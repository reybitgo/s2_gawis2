For an e-commerce system with e-wallet payments where the admin manages order fulfillment, here's a comprehensive order status system that will keep users informed:

## Core Order Statuses

### 1. **Payment & Initial Processing**

-   **`pending`** - Order placed, awaiting payment confirmation
-   **`paid`** - Payment successful via e-wallet
-   **`payment_failed`** - Payment issue (insufficient balance, etc.)

### 2. **Order Processing**

-   **`processing`** - Admin has acknowledged and started processing
-   **`confirmed`** - Order confirmed and being prepared
-   **`packing`** - Items being packed for shipment
-   **`ready_to_ship`** - Packed and ready for courier pickup

### 3. **Shipping & Delivery**

-   **`shipped`** - Handed to courier/logistics partner
-   **`in_transit`** - On the way to customer
-   **`out_for_delivery`** - With delivery agent, arriving today
-   **`delivered`** - Successfully delivered to customer
-   **`delivery_failed`** - Delivery attempt unsuccessful

### 4. **Special Cases**

-   **`on_hold`** - Temporarily paused (stock issue, verification needed)
-   **`cancelled`** - Order cancelled (by admin/user)
-   **`refunded`** - Payment returned to e-wallet
-   **`returned`** - Customer returned the item
-   **`completed`** - Order fully completed and closed

## Database Implementation

```php
// Migration for orders table
Schema::table('orders', function (Blueprint $table) {
    $table->enum('status', [
        'pending',
        'paid',
        'payment_failed',
        'processing',
        'confirmed',
        'packing',
        'ready_to_ship',
        'shipped',
        'in_transit',
        'out_for_delivery',
        'delivered',
        'delivery_failed',
        'on_hold',
        'cancelled',
        'refunded',
        'returned',
        'completed'
    ])->default('pending');

    $table->string('tracking_number')->nullable();
    $table->string('courier_name')->nullable();
    $table->text('admin_notes')->nullable();
    $table->text('status_message')->nullable(); // Custom message for user
    $table->timestamp('estimated_delivery')->nullable();
});
```

## Status History Tracking

Create a separate table to track status changes:

```php
// Order status history migration
Schema::create('order_status_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained();
    $table->string('status');
    $table->string('changed_by')->nullable(); // admin/system/user
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

## Laravel Model Implementation

```php
// Order.php Model
class Order extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_PROCESSING = 'processing';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PACKING = 'packing';
    const STATUS_READY_TO_SHIP = 'ready_to_ship';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    // ... other statuses

    public static function getStatusLabels()
    {
        return [
            self::STATUS_PENDING => 'Pending Payment',
            self::STATUS_PAID => 'Payment Received',
            self::STATUS_PROCESSING => 'Processing Order',
            self::STATUS_CONFIRMED => 'Order Confirmed',
            self::STATUS_PACKING => 'Packing Items',
            self::STATUS_READY_TO_SHIP => 'Ready for Shipment',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            // ... other labels
        ];
    }

    public function updateStatus($newStatus, $notes = null, $changedBy = 'admin')
    {
        $this->status = $newStatus;
        $this->save();

        // Log the status change
        $this->statusHistory()->create([
            'status' => $newStatus,
            'notes' => $notes,
            'changed_by' => $changedBy
        ]);

        // Send notification to user
        $this->user->notify(new OrderStatusUpdated($this));
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }
}
```

## Admin Interface Features

For the admin panel, consider implementing:

```php
// AdminOrderController.php
class AdminOrderController extends Controller
{
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Order::getAllStatuses()),
            'tracking_number' => 'nullable|string',
            'courier_name' => 'nullable|string',
            'estimated_delivery' => 'nullable|date',
            'notes' => 'nullable|string',
            'notify_customer' => 'boolean'
        ]);

        $order->updateStatus(
            $validated['status'],
            $validated['notes'] ?? null,
            auth()->user()->name
        );

        if ($validated['tracking_number']) {
            $order->tracking_number = $validated['tracking_number'];
        }

        if ($validated['courier_name']) {
            $order->courier_name = $validated['courier_name'];
        }

        $order->save();

        if ($validated['notify_customer'] ?? true) {
            // Send email/SMS notification
        }

        return back()->with('success', 'Order status updated successfully');
    }

    public function bulkUpdateStatus(Request $request)
    {
        // Allow admin to update multiple orders at once
        $orderIds = $request->input('order_ids', []);
        $newStatus = $request->input('status');

        Order::whereIn('id', $orderIds)->each(function ($order) use ($newStatus) {
            $order->updateStatus($newStatus);
        });

        return back()->with('success', 'Bulk status update completed');
    }
}
```

## User-Facing Display

For the user's order tracking page:

```php
// Blade view example
<div class="order-status-tracker">
    <h3>Order #{{ $order->order_number }}</h3>

    <div class="status-timeline">
        @foreach($order->getStatusProgression() as $status)
            <div class="status-step {{ $order->hasReachedStatus($status) ? 'completed' : 'pending' }}">
                <span class="status-icon"></span>
                <span class="status-label">{{ Order::getStatusLabels()[$status] }}</span>
                @if($statusHistory = $order->getStatusHistoryFor($status))
                    <span class="status-time">{{ $statusHistory->created_at->format('M d, H:i') }}</span>
                @endif
            </div>
        @endforeach
    </div>

    @if($order->tracking_number)
        <div class="tracking-info">
            <p>Tracking: {{ $order->tracking_number }}</p>
            <p>Courier: {{ $order->courier_name }}</p>
            @if($order->estimated_delivery)
                <p>Expected Delivery: {{ $order->estimated_delivery->format('M d, Y') }}</p>
            @endif
        </div>
    @endif
</div>
```

## Additional Considerations

1. **Status Groups**: Group statuses logically for easier filtering:

    - Pre-fulfillment: `pending`, `paid`, `processing`
    - Fulfillment: `confirmed`, `packing`, `ready_to_ship`
    - Delivery: `shipped`, `in_transit`, `out_for_delivery`, `delivered`
    - Issues: `on_hold`, `cancelled`, `returned`, `refunded`

2. **Automated Status Updates**: Consider integrating with courier APIs to automatically update shipping statuses.

3. **Status Permissions**: Define which user roles can set which statuses to prevent errors.

4. **Status Workflow Rules**: Implement business logic to prevent invalid status transitions (e.g., can't go from `delivered` back to `processing`).

This system gives you flexibility while keeping users informed throughout the entire order lifecycle. The admin can easily update statuses, and users get clear visibility into their order progress.
