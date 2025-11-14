# Return Process Implementation Guide

## Status: Database Ready ✅
- ✅ `return_requests` table created
- ✅ `delivered_at` field added to orders table

---

## Remaining Implementation Tasks

### Phase 1: Models & Business Logic

#### 1.1 Create ReturnRequest Model
```bash
php artisan make:model ReturnRequest
```

**File:** `app/Models/ReturnRequest.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'reason',
        'description',
        'images',
        'status',
        'admin_response',
        'return_tracking_number',
        'approved_at',
        'rejected_at',
        'refunded_at',
    ];

    protected $casts = [
        'images' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    const REASON_DAMAGED_PRODUCT = 'damaged_product';
    const REASON_WRONG_ITEM = 'wrong_item';
    const REASON_NOT_AS_DESCRIBED = 'not_as_described';
    const REASON_QUALITY_ISSUE = 'quality_issue';
    const REASON_NO_LONGER_NEEDED = 'no_longer_needed';
    const REASON_OTHER = 'other';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getReasonLabels(): array
    {
        return [
            self::REASON_DAMAGED_PRODUCT => 'Damaged Product',
            self::REASON_WRONG_ITEM => 'Wrong Item Received',
            self::REASON_NOT_AS_DESCRIBED => 'Not as Described',
            self::REASON_QUALITY_ISSUE => 'Quality Issue',
            self::REASON_NO_LONGER_NEEDED => 'No Longer Needed',
            self::REASON_OTHER => 'Other',
        ];
    }

    public function getReasonLabelAttribute(): string
    {
        return self::getReasonLabels()[$this->reason] ?? $this->reason;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
```

---

#### 1.2 Update Order Model

**File:** `app/Models/Order.php`

Add these methods and constants:

```php
// Add to constants section
const STATUS_RETURN_REQUESTED = 'return_requested';
const STATUS_RETURN_APPROVED = 'return_approved';
const STATUS_RETURN_REJECTED = 'return_rejected';
const STATUS_RETURN_IN_TRANSIT = 'return_in_transit';
const STATUS_RETURN_RECEIVED = 'return_received';
const STATUS_REFUNDED = 'refunded';

const MAX_RETURN_DAYS = 7;

// Add to casts
protected $casts = [
    // ... existing casts
    'delivered_at' => 'datetime',
];

// Add relationship
public function returnRequest(): HasOne
{
    return $this->hasOne(ReturnRequest::class);
}

// Add return-related methods
public function canRequestReturn(): bool
{
    return $this->status === self::STATUS_DELIVERED
        && $this->delivered_at
        && $this->delivered_at->addDays(self::MAX_RETURN_DAYS) >= now()
        && !$this->returnRequest;
}

public function getDaysUntilReturnExpiry(): ?int
{
    if (!$this->delivered_at) {
        return null;
    }

    $expiryDate = $this->delivered_at->copy()->addDays(self::MAX_RETURN_DAYS);
    $daysRemaining = now()->diffInDays($expiryDate, false);

    return $daysRemaining > 0 ? (int) $daysRemaining : 0;
}

public function processRefund(): void
{
    if ($this->payment_status !== self::PAYMENT_STATUS_PAID) {
        throw new \Exception('Cannot refund unpaid order');
    }

    // Credit wallet
    $this->user->wallet->credit(
        $this->total_amount,
        'Refund for Order #' . $this->order_number
    );

    // Update order
    $this->update([
        'status' => self::STATUS_REFUNDED,
        'payment_status' => self::PAYMENT_STATUS_REFUNDED
    ]);

    // Update return request if exists
    if ($this->returnRequest) {
        $this->returnRequest->update([
            'status' => ReturnRequest::STATUS_COMPLETED,
            'refunded_at' => now()
        ]);
    }
}

public function markAsDelivered(): void
{
    $this->update([
        'status' => self::STATUS_DELIVERED,
        'delivered_at' => now(),
    ]);
}
```

Update the status labels method to include return statuses:

```php
public static function getStatusLabels(): array
{
    return [
        // ... existing statuses
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_RETURN_REQUESTED => 'Return Requested',
        self::STATUS_RETURN_APPROVED => 'Return Approved',
        self::STATUS_RETURN_REJECTED => 'Return Rejected',
        self::STATUS_RETURN_IN_TRANSIT => 'Return In Transit',
        self::STATUS_RETURN_RECEIVED => 'Return Received',
        self::STATUS_REFUNDED => 'Refunded',
        // ... other statuses
    ];
}
```

---

### Phase 2: Routes

**File:** `routes/web.php`

Add these routes:

```php
// Customer return request routes
Route::middleware(['auth', 'enforce.2fa'])->group(function () {
    Route::post('/orders/{order}/return-request', [ReturnRequestController::class, 'store'])
        ->name('orders.return-request.store');
    Route::post('/return-requests/{returnRequest}/add-tracking', [ReturnRequestController::class, 'addTracking'])
        ->name('return-requests.add-tracking');
});

// Admin return management routes
Route::middleware(['auth', 'role:admin', 'enforce.2fa'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/returns', [AdminReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/{returnRequest}', [AdminReturnController::class, 'show'])->name('returns.show');
    Route::post('/returns/{returnRequest}/approve', [AdminReturnController::class, 'approve'])->name('returns.approve');
    Route::post('/returns/{returnRequest}/reject', [AdminReturnController::class, 'reject'])->name('returns.reject');
    Route::post('/returns/{returnRequest}/confirm-received', [AdminReturnController::class, 'confirmReceived'])->name('returns.confirm-received');
});
```

---

### Phase 3: Controllers

#### 3.1 Create ReturnRequestController

```bash
php artisan make:controller Member/ReturnRequestController
```

**File:** `app/Http/Controllers/Member/ReturnRequestController.php`

*See attached implementation in next section*

#### 3.2 Create AdminReturnController

```bash
php artisan make:controller Admin/AdminReturnController
```

**File:** `app/Http/Controllers/Admin/AdminReturnController.php`

*See attached implementation in next section*

---

### Phase 4: UI Components

#### 4.1 Admin Order Details - Add Delivery Timestamp UI

**File:** `resources/views/admin/orders/show.blade.php`

**Location:** In the "Order Actions" section, after other status update buttons

```html
<!-- Set Delivery Timestamp -->
@if($order->status === 'out_for_delivery')
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Mark as Delivered</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.orders.mark-delivered', $order) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="delivered_at" class="form-label">Delivery Date & Time</label>
                <input type="datetime-local"
                       class="form-control"
                       id="delivered_at"
                       name="delivered_at"
                       value="{{ old('delivered_at', now()->format('Y-m-d\TH:i')) }}"
                       required>
                <div class="form-text">Set the date and time when the package was delivered</div>
            </div>
            <div class="mb-3">
                <label for="delivery_notes" class="form-label">Delivery Notes (Optional)</label>
                <textarea class="form-control"
                          id="delivery_notes"
                          name="delivery_notes"
                          rows="2"
                          placeholder="Any notes about the delivery...">{{ old('delivery_notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-success">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                </svg>
                Mark as Delivered
            </button>
        </form>
    </div>
</div>
@endif

@if($order->delivered_at)
<div class="alert alert-success">
    <h6 class="alert-heading">Delivery Confirmed</h6>
    <p class="mb-0">
        <strong>Delivered on:</strong> {{ $order->delivered_at->format('M d, Y \a\t g:i A') }}<br>
        <strong>Return Window:</strong>
        @if($order->canRequestReturn())
            {{ $order->getDaysUntilReturnExpiry() }} days remaining (until {{ $order->delivered_at->addDays(7)->format('M d, Y') }})
        @else
            Expired
        @endif
    </p>
</div>
@endif
```

---

#### 4.2 Customer Order Details - Add Return Request UI

**File:** `resources/views/orders/show.blade.php`

Add this section after the order details, before the action buttons:

```html
<!-- Return Request Section -->
@if($order->status === 'delivered')
    @if($order->canRequestReturn())
        <div class="card mb-4">
            <div class="card-header bg-warning-subtle">
                <h6 class="mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-action-undo') }}"></use>
                    </svg>
                    Request Return
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-3">Not satisfied with your order? You can request a return within {{ $order->getDaysUntilReturnExpiry() }} days.</p>

                <button type="button" class="btn btn-warning" data-coreui-toggle="modal" data-coreui-target="#returnRequestModal">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-action-undo') }}"></use>
                    </svg>
                    Request Return
                </button>
            </div>
        </div>
    @elseif($order->delivered_at && !$order->returnRequest)
        <div class="alert alert-secondary">
            <strong>Return Window Expired:</strong> The 7-day return period ended on {{ $order->delivered_at->addDays(7)->format('M d, Y') }}.
        </div>
    @endif
@endif

<!-- Return Status Display -->
@if($order->returnRequest)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <svg class="icon me-2">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
            </svg>
            Return Request Status
        </h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <strong>Status:</strong>
            <span class="badge
                @if($order->returnRequest->isPending()) bg-warning
                @elseif($order->returnRequest->isApproved()) bg-info
                @elseif($order->returnRequest->isCompleted()) bg-success
                @else bg-danger
                @endif">
                {{ ucfirst($order->returnRequest->status) }}
            </span>
        </div>
        <div class="mb-3">
            <strong>Reason:</strong> {{ $order->returnRequest->reason_label }}
        </div>
        <div class="mb-3">
            <strong>Description:</strong><br>
            <p class="text-muted">{{ $order->returnRequest->description }}</p>
        </div>

        @if($order->returnRequest->admin_response)
        <div class="alert alert-info">
            <strong>Admin Response:</strong><br>
            {{ $order->returnRequest->admin_response }}
        </div>
        @endif

        @if($order->returnRequest->isApproved() && !$order->returnRequest->return_tracking_number)
        <div class="alert alert-warning">
            <p><strong>Next Step:</strong> Please ship the item back and provide the tracking number below.</p>
            <form action="{{ route('return-requests.add-tracking', $order->returnRequest) }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" class="form-control" name="tracking_number" placeholder="Return tracking number" required>
                    <button type="submit" class="btn btn-primary">Submit Tracking</button>
                </div>
            </form>
        </div>
        @elseif($order->returnRequest->return_tracking_number)
        <div class="mb-3">
            <strong>Return Tracking Number:</strong> {{ $order->returnRequest->return_tracking_number }}
        </div>
        @endif

        @if($order->returnRequest->isCompleted())
        <div class="alert alert-success">
            <strong>✓ Return Completed</strong><br>
            Refund processed on {{ $order->returnRequest->refunded_at->format('M d, Y \a\t g:i A') }}
        </div>
        @endif
    </div>
</div>
@endif

<!-- Return Request Modal -->
<div class="modal fade" id="returnRequestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Order Return</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <form action="{{ route('orders.return-request.store', $order) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Important:</strong> Please provide detailed information about why you're returning this order. Include photos if applicable.
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Return <span class="text-danger">*</span></label>
                        <select class="form-select" id="reason" name="reason" required>
                            <option value="">Select a reason...</option>
                            <option value="damaged_product">Damaged Product</option>
                            <option value="wrong_item">Wrong Item Received</option>
                            <option value="not_as_described">Not as Described</option>
                            <option value="quality_issue">Quality Issue</option>
                            <option value="no_longer_needed">No Longer Needed</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Detailed Description <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  minlength="20"
                                  required
                                  placeholder="Please provide a detailed explanation (minimum 20 characters)..."></textarea>
                        <div class="form-text">Minimum 20 characters required</div>
                    </div>

                    <div class="mb-3">
                        <label for="images" class="form-label">Upload Images (Optional)</label>
                        <input type="file"
                               class="form-control"
                               id="images"
                               name="images[]"
                               multiple
                               accept="image/*">
                        <div class="form-text">Upload photos showing the issue (max 3 images, 5MB each)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Return Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

#### 4.3 Admin Returns List Page

**File:** `resources/views/admin/returns/index.blade.php`

*Full implementation available - create this view to list all return requests*

---

#### 4.4 Add "Returns" to Admin Sidebar

**File:** `resources/views/partials/sidebar.blade.php`

Add after the Orders menu item:

```html
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.returns.index') }}">
        <svg class="nav-icon">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-action-undo') }}"></use>
        </svg>
        Returns
        @php
            $pendingReturns = \App\Models\ReturnRequest::where('status', 'pending')->count();
        @endphp
        @if($pendingReturns > 0)
            <span class="badge badge-sm bg-warning ms-auto">{{ $pendingReturns }}</span>
        @endif
    </a>
</li>
```

---

## Summary

**Completed:**
✅ Database migrations
✅ Implementation guide created

**Next Steps:**
1. Create ReturnRequest model
2. Update Order model with return methods
3. Create controllers (ReturnRequestController, AdminReturnController)
4. Add routes
5. Build UI components
6. Test complete workflow

Would you like me to implement any specific part of this guide first?
