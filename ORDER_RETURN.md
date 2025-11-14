If user chooses to pay with e-wallet then it's automatically paid and cannot be canceled though the user can opt out to return the package with a valid reason, which admin will approve, and then a refund will ensue. That's exactly how modern e-commerce works with digital wallets. Let me refine the status system to match this flow:

## Streamlined Order Status System

### 1. **Immediate Payment Flow** (E-wallet)

```php
// When user completes checkout with e-wallet
'paid' // Instant - payment deducted from wallet
'processing' // Admin sees new order to fulfill
```

### 2. **Core Fulfillment Statuses**

-   **`paid`** - Payment successful, awaiting admin action
-   **`processing`** - Admin acknowledged and preparing order
-   **`packed`** - Order packed and ready
-   **`shipped`** - Handed to courier with tracking number
-   **`out_for_delivery`** - Package on the way to customer
-   **`delivered`** - Successfully delivered

### 3. **Return/Refund Flow**

-   **`return_requested`** - Customer initiated return request
-   **`return_approved`** - Admin approved the return
-   **`return_rejected`** - Admin rejected (invalid reason)
-   **`return_in_transit`** - Customer shipped item back
-   **`return_received`** - Admin received returned item
-   **`refunded`** - Money credited back to e-wallet

### 4. **Edge Cases**

-   **`delivery_failed`** - Delivery attempt unsuccessful
-   **`on_hold`** - Issue that needs resolution

## Database Schema

```php
// Orders table migration
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->decimal('total_amount', 10, 2);
    $table->enum('payment_method', ['e_wallet', 'cod', 'bank_transfer']);
    $table->enum('status', [
        'paid',
        'processing',
        'packed',
        'shipped',
        'out_for_delivery',
        'delivered',
        'delivery_failed',
        'on_hold',
        'return_requested',
        'return_approved',
        'return_rejected',
        'return_in_transit',
        'return_received',
        'refunded'
    ])->default('paid'); // Default for e-wallet payments

    $table->string('tracking_number')->nullable();
    $table->string('courier_name')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->timestamps();
});

// Return requests table
Schema::create('return_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->enum('reason', [
        'damaged_product',
        'wrong_item',
        'not_as_described',
        'quality_issue',
        'no_longer_needed',
        'other'
    ]);
    $table->text('description');
    $table->json('images')->nullable(); // Return proof images
    $table->enum('status', ['pending', 'approved', 'rejected', 'completed']);
    $table->text('admin_response')->nullable();
    $table->string('return_tracking_number')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('rejected_at')->nullable();
    $table->timestamp('refunded_at')->nullable();
    $table->timestamps();
});
```

## Order Model with Return Logic

```php
class Order extends Model
{
    protected $dates = ['delivered_at'];

    const MAX_RETURN_DAYS = 7; // Return window

    public function canRequestReturn()
    {
        // Can only return if delivered and within return window
        return $this->status === 'delivered'
            && $this->delivered_at
            && $this->delivered_at->addDays(self::MAX_RETURN_DAYS) >= now();
    }

    public function returnRequest()
    {
        return $this->hasOne(ReturnRequest::class);
    }

    public function processPayment()
    {
        if ($this->payment_method === 'e_wallet') {
            // Deduct from wallet
            $user = $this->user;
            $user->wallet->debit($this->total_amount, 'Order #' . $this->id);

            // Immediately mark as paid
            $this->update(['status' => 'paid']);

            // Notify admin of new order
            Admin::notify(new NewPaidOrder($this));
        }
    }

    public function processRefund()
    {
        if ($this->payment_method === 'e_wallet') {
            // Credit back to wallet
            $this->user->wallet->credit(
                $this->total_amount,
                'Refund for Order #' . $this->id
            );

            $this->update(['status' => 'refunded']);

            // Update return request
            if ($this->returnRequest) {
                $this->returnRequest->update([
                    'status' => 'completed',
                    'refunded_at' => now()
                ]);
            }
        }
    }
}
```

## Return Request Controller (User Side)

```php
class ReturnRequestController extends Controller
{
    public function store(Request $request, Order $order)
    {
        // Verify ownership
        abort_if($order->user_id !== auth()->id(), 403);

        // Check if return is allowed
        if (!$order->canRequestReturn()) {
            return back()->with('error', 'Return window has expired (7 days from delivery)');
        }

        $validated = $request->validate([
            'reason' => 'required|in:damaged_product,wrong_item,not_as_described,quality_issue,no_longer_needed,other',
            'description' => 'required|string|min:20|max:500',
            'images.*' => 'nullable|image|max:5120' // 5MB per image
        ]);

        // Upload return proof images
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('return-proofs', 'public');
            }
        }

        $returnRequest = $order->returnRequest()->create([
            'user_id' => auth()->id(),
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'images' => $images,
            'status' => 'pending'
        ]);

        $order->update(['status' => 'return_requested']);

        // Notify admin
        Admin::notify(new ReturnRequestSubmitted($returnRequest));

        return redirect()->route('orders.show', $order)
            ->with('success', 'Return request submitted. We\'ll review it within 24 hours.');
    }
}
```

## Admin Return Management

```php
class AdminReturnController extends Controller
{
    public function index()
    {
        $returnRequests = ReturnRequest::with('order', 'user')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.returns.index', compact('returnRequests'));
    }

    public function approve(Request $request, ReturnRequest $returnRequest)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string',
            'return_shipping_instructions' => 'required|string'
        ]);

        $returnRequest->update([
            'status' => 'approved',
            'admin_response' => $validated['admin_response'],
            'approved_at' => now()
        ]);

        $returnRequest->order->update(['status' => 'return_approved']);

        // Send return shipping instructions to customer
        $returnRequest->user->notify(
            new ReturnApproved($returnRequest, $validated['return_shipping_instructions'])
        );

        return back()->with('success', 'Return request approved');
    }

    public function reject(Request $request, ReturnRequest $returnRequest)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string|min:20' // Explain why rejected
        ]);

        $returnRequest->update([
            'status' => 'rejected',
            'admin_response' => $validated['admin_response'],
            'rejected_at' => now()
        ]);

        // Order goes back to delivered status
        $returnRequest->order->update(['status' => 'delivered']);

        $returnRequest->user->notify(new ReturnRejected($returnRequest));

        return back()->with('success', 'Return request rejected');
    }

    public function confirmReceived(ReturnRequest $returnRequest)
    {
        $returnRequest->order->update(['status' => 'return_received']);

        // Process refund
        $returnRequest->order->processRefund();

        return back()->with('success', 'Return received and refund processed');
    }
}
```

## User Order Tracking View

```blade
{{-- resources/views/orders/show.blade.php --}}
<div class="order-detail">
    <h3>Order #{{ $order->order_number }}</h3>

    {{-- Status Timeline --}}
    <div class="status-progress">
        @php
            $timeline = ['paid', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered'];
            $currentIndex = array_search($order->status, $timeline);
        @endphp

        @foreach($timeline as $index => $status)
            <div class="status-node {{ $index <= $currentIndex ? 'completed' : '' }}">
                <div class="status-dot"></div>
                <div class="status-label">{{ ucwords(str_replace('_', ' ', $status)) }}</div>
            </div>
        @endforeach
    </div>

    {{-- Tracking Info --}}
    @if($order->tracking_number)
        <div class="tracking-section">
            <h4>Tracking Information</h4>
            <p>Courier: {{ $order->courier_name }}</p>
            <p>Tracking Number: {{ $order->tracking_number }}</p>
            <a href="#" class="btn btn-sm">Track Package</a>
        </div>
    @endif

    {{-- Return Section --}}
    @if($order->status === 'delivered')
        @if($order->canRequestReturn())
            <div class="return-section">
                <p>Not satisfied? You can return this order within {{ $order->delivered_at->addDays(7)->diffForHumans() }}</p>
                <button class="btn btn-warning" onclick="showReturnModal()">Request Return</button>
            </div>
        @else
            <p class="text-muted">Return window expired</p>
        @endif
    @endif

    {{-- Return Status --}}
    @if($order->returnRequest)
        <div class="return-status">
            <h4>Return Status</h4>
            <p>Status: {{ ucwords($order->returnRequest->status) }}</p>
            <p>Reason: {{ ucwords(str_replace('_', ' ', $order->returnRequest->reason)) }}</p>
            @if($order->returnRequest->admin_response)
                <p>Admin Response: {{ $order->returnRequest->admin_response }}</p>
            @endif
            @if($order->returnRequest->status === 'approved')
                <p class="text-info">Please ship the item back using the instructions sent to your email.</p>
            @endif
        </div>
    @endif
</div>
```

## Key Business Logic Points

1. **No Cancellation After Payment**: Once e-wallet payment is made, the order cannot be cancelled, only returned after delivery.

2. **Return Window**: Typically 7 days from delivery date for return eligibility.

3. **Return Approval Required**: Admin must validate the return reason before approving.

4. **Automatic Refund**: Once admin confirms receipt of returned item, refund is automatically credited to e-wallet.

5. **Return Proof**: Users should provide images/description to support their return claim.

This flow ensures that:

-   Payment is instant and final (no payment pending states)
-   Users have a clear return process with accountability
-   Admin has control over return approvals
-   Refunds are traceable back to the e-wallet
-   The entire process is transparent to all parties
