@extends('layouts.admin')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Order {{ $order->order_number }}</h2>
            <div class="text-muted">Created {{ $order->created_at->format('M d, Y \a\t H:i') }}</div>
        </div>
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                </svg>
                Back to Orders
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-lg-8">
            <!-- Status Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-timeline') }}"></use>
                        </svg>
                        Order Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($statusSummary as $step)
                            <div class="timeline-item {{ $step['completed'] ? 'completed' : ($step['is_current'] ? 'current' : 'pending') }}">
                                <div class="timeline-marker">
                                    @if($step['completed'])
                                        <svg class="icon text-success">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                        </svg>
                                    @elseif($step['is_current'])
                                        <svg class="icon text-primary">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-media-play') }}"></use>
                                        </svg>
                                    @else
                                        <svg class="icon text-muted">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-circle') }}"></use>
                                        </svg>
                                    @endif
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1 {{ $step['is_current'] ? 'text-primary fw-bold' : '' }}">{{ $step['label'] }}</h6>
                                        @if($step['completed'] && $step['history_id'])
                                            <button type="button"
                                                    class="btn btn-sm btn-link text-muted p-0 ms-2"
                                                    onclick="editTimelineNotes({{ $step['history_id'] }}, '{{ $step['status'] }}', {{ json_encode($step['notes']) }})"
                                                    title="Edit notes">
                                                <svg class="icon icon-sm">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-pencil') }}"></use>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                    @if($step['timestamp'])
                                        <div class="text-muted small">{{ \Carbon\Carbon::parse($step['timestamp'])->format('M d, Y H:i') }}</div>
                                    @endif
                                    @if($step['completed'] && $step['history_id'])
                                        <div class="text-muted small mt-1" id="timeline-notes-{{ $step['history_id'] }}" style="{{ !$step['notes'] ? 'display: none;' : '' }}">{!! $step['notes'] ? nl2br(e($step['notes'])) : '' !!}</div>
                                    @endif
                                    @if($step['changed_by'])
                                        <div class="text-muted small">by {{ $step['changed_by'] }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list-numbered') }}"></use>
                        </svg>
                        Order Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Order Details</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-semibold">Order Number:</td>
                                    <td>{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Order Date:</td>
                                    <td>{{ $order->created_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Status:</td>
                                    <td><span class="{{ $order->status_badge_class }}">{{ $order->status_label }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Payment Status:</td>
                                    <td><span class="{{ $order->payment_status_badge_class }}">{{ ucfirst($order->payment_status) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Delivery Method:</td>
                                    <td>
                                        <span class="badge bg-{{ $order->isOfficePickup() ? 'info' : 'primary' }}">
                                            {{ $order->delivery_method_label }}
                                        </span>
                                    </td>
                                </tr>
                                @if($order->paid_at)
                                <tr>
                                    <td class="fw-semibold">Paid At:</td>
                                    <td>{{ $order->paid_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                @endif
                                @if($order->cancelled_at)
                                <tr>
                                    <td class="fw-semibold">Cancelled At:</td>
                                    <td>{{ $order->cancelled_at->format('M d, Y \a\t g:i A') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Order Summary</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-semibold">Subtotal:</td>
                                    <td>{{ $order->formatted_subtotal }}</td>
                                </tr>
                                @if($order->tax_amount > 0)
                                <tr>
                                    <td class="fw-semibold">Tax ({{ $order->tax_percentage }}):</td>
                                    <td>{{ $order->formatted_tax_amount }}</td>
                                </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="fw-bold">Total:</td>
                                    <td class="fw-bold text-primary">{{ $order->formatted_total }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Points Earned:</td>
                                    <td class="text-success">
                                        {{ number_format($order->points_awarded) }} points
                                        @if($order->points_credited)
                                            <span class="badge bg-success ms-1">Credited</span>
                                        @else
                                            <span class="badge bg-warning ms-1">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($order->customer_notes)
                    <div class="mt-3">
                        <h6 class="text-muted">Customer Notes</h6>
                        <div class="alert alert-light">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-comment-square') }}"></use>
                            </svg>
                            {{ $order->customer_notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                        </svg>
                        Order Items
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Package</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Points</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $item->package_snapshot['image_url'] ?? '/images/package-placeholder.svg' }}"
                                                     alt="{{ $item->package_snapshot['name'] }}"
                                                     class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <strong>{{ $item->package_snapshot['name'] }}</strong>
                                                    @if($item->package_snapshot['short_description'])
                                                        <div class="text-muted small">{{ $item->package_snapshot['short_description'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>${{ number_format($item->unit_price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ $item->total_points_awarded }} pts</td>
                                        <td class="text-end"><strong>${{ number_format($item->total_price, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong>{{ $order->formatted_subtotal }}</strong></td>
                                </tr>
                                @if($order->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end">Tax ({{ number_format($order->tax_rate * 100, 1) }}%):</td>
                                    <td class="text-end">${{ number_format($order->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong class="text-primary">{{ $order->formatted_total }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Status History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-history') }}"></use>
                        </svg>
                        Status History
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($order->statusHistory->sortByDesc('created_at') as $history)
                        <div class="d-flex mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar bg-light text-primary">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $history->status_label }}</h6>
                                        <div class="text-muted small">{{ $history->changed_by_description }}</div>
                                    </div>
                                    <div class="text-muted small">{{ $history->created_at->format('M d, Y H:i') }}</div>
                                </div>
                                @if($history->notes)
                                    <div class="mt-2 text-muted">{{ $history->notes }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">No status history available.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Order Status Management -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Management</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Current Status</label>
                        <div>
                            <span class="badge bg-{{ \App\Models\Order::getStatusBadgeColor($order->status) }} fs-6">{{ $order->status_label }}</span>
                        </div>
                    </div>

                    @if(count($recommendedStatuses) > 0)
                        <div class="mb-3">
                            <label class="form-label">Next Actions</label>
                            <div class="mt-2">
                                @foreach($recommendedStatuses as $statusOption)
                                    <button type="button"
                                            class="btn btn-sm {{ $statusOption['is_recommended'] ? 'btn-primary' : 'btn-outline-secondary' }} me-1 mb-1 status-update-btn"
                                            data-status="{{ $statusOption['status'] }}"
                                            title="{{ $statusOption['description'] }}">
                                        {{ $statusOption['label'] }}
                                        @if($statusOption['is_recommended'])
                                            <svg class="icon ms-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                                            </svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="button" class="btn btn-outline-primary btn-sm" data-coreui-toggle="modal" data-coreui-target="#statusModal">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-pencil') }}"></use>
                        </svg>
                        Update Status
                    </button>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="d-flex align-items-center justify-content-center bg-dark text-white rounded-circle me-3" style="width: 50px; height: 50px;">
                            <svg class="icon" style="width: 24px; height: 24px;">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                            </svg>
                        </div>
                        <div>
                            <strong>{{ $order->user->fullname ?? $order->user->name }}</strong>
                            <div class="text-muted small">{{ $order->user->email }}</div>
                            @if($order->user->phone)
                                <div class="text-muted small">
                                    <svg class="icon me-1" style="width: 12px; height: 12px;">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-phone') }}"></use>
                                    </svg>
                                    {{ $order->user->phone }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($order->customer_notes)
                        <div class="mb-3">
                            <label class="form-label small text-muted">Customer Notes:</label>
                            <div class="p-2 bg-light rounded">{{ $order->customer_notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Method:</label>
                        <div>
                            <span class="badge bg-{{ $order->isOfficePickup() ? 'info' : 'primary' }}">
                                {{ $order->delivery_method_label }}
                            </span>
                        </div>
                    </div>

                    @if($order->isHomeDelivery())
                        <!-- Home Delivery Fields -->
                        @if($order->delivery_address)
                            <div class="mb-3">
                                <label class="form-label small text-muted">Delivery Address:</label>
                                <div class="delivery-address small">
                                    <div><strong>{{ $order->delivery_address['full_name'] ?? 'N/A' }}</strong></div>
                                    @if(!empty($order->delivery_address['phone']))
                                        <div class="text-muted">{{ $order->delivery_address['phone'] }}</div>
                                    @endif
                                    <div>{{ $order->delivery_address['address'] ?? '' }}</div>
                                    @if(!empty($order->delivery_address['address_2']))
                                        <div>{{ $order->delivery_address['address_2'] }}</div>
                                    @endif
                                    <div>
                                        {{ $order->delivery_address['city'] ?? '' }}{{ !empty($order->delivery_address['state']) ? ', ' . $order->delivery_address['state'] : '' }} {{ $order->delivery_address['zip'] ?? '' }}
                                    </div>
                                    @if(!empty($order->delivery_address['time_preference']))
                                        <div class="text-muted mt-1">
                                            <small>
                                                Preferred time:
                                                @switch($order->delivery_address['time_preference'])
                                                    @case('anytime')
                                                        Anytime (9 AM - 6 PM)
                                                        @break
                                                    @case('morning')
                                                        Morning (9 AM - 12 PM)
                                                        @break
                                                    @case('afternoon')
                                                        Afternoon (12 PM - 6 PM)
                                                        @break
                                                    @case('weekend')
                                                        Weekend preferred
                                                        @break
                                                    @default
                                                        {{ ucfirst($order->delivery_address['time_preference']) }}
                                                @endswitch
                                            </small>
                                        </div>
                                    @endif
                                    @if(!empty($order->delivery_address['instructions']))
                                        <div class="p-2 bg-light rounded mt-2">
                                            <small><strong>Instructions:</strong> {{ $order->delivery_address['instructions'] }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label small text-muted">Tracking Number:</label>
                            <div>{{ $order->tracking_number ?? 'Not set' }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Courier:</label>
                            <div>{{ $order->courier_name ?? 'Not set' }}</div>
                        </div>
                        @if($order->estimated_delivery)
                            <div class="mb-3">
                                <label class="form-label small text-muted">Estimated Delivery:</label>
                                <div>{{ \Carbon\Carbon::parse($order->estimated_delivery)->format('M d, Y') }}</div>
                            </div>
                        @endif
                        <button type="button" class="btn btn-outline-primary btn-sm" data-coreui-toggle="modal" data-coreui-target="#trackingModal">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-truck') }}"></use>
                            </svg>
                            Update Tracking
                        </button>
                    @else
                        <!-- Office Pickup Fields -->
                        <div class="mb-3">
                            <label class="form-label small text-muted">Pickup Location:</label>
                            <div>{{ $order->pickup_location ?? 'Not set' }}</div>
                        </div>
                        @if($order->pickup_date)
                            <div class="mb-3">
                                <label class="form-label small text-muted">Pickup Date:</label>
                                <div>{{ \Carbon\Carbon::parse($order->pickup_date)->format('M d, Y H:i') }}</div>
                            </div>
                        @endif
                        @if($order->pickup_instructions)
                            <div class="mb-3">
                                <label class="form-label small text-muted">Pickup Instructions:</label>
                                <div class="p-2 bg-light rounded">{{ $order->pickup_instructions }}</div>
                            </div>
                        @endif
                        <button type="button" class="btn btn-outline-primary btn-sm" data-coreui-toggle="modal" data-coreui-target="#pickupModal">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-location-pin') }}"></use>
                            </svg>
                            Update Pickup Info
                        </button>
                    @endif
                </div>
            </div>

            <!-- Payment Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Payment Status:</label>
                        <div>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'warning') }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Total Amount:</label>
                        <div><strong>{{ $order->formatted_total }}</strong></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Points Awarded:</label>
                        <div>{{ $order->points_awarded }} pts</div>
                    </div>
                    @if($order->paid_at)
                        <div class="mb-3">
                            <label class="form-label small text-muted">Paid At:</label>
                            <div>{{ $order->paid_at->format('M d, Y H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Admin Notes -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Admin Notes</h5>
                </div>
                <div class="card-body">
                    @if($order->admin_notes)
                        <div class="p-3 bg-light rounded mb-3">{{ $order->admin_notes }}</div>
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-coreui-toggle="modal" data-coreui-target="#notesModal">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-note-add') }}"></use>
                        </svg>
                        {{ $order->admin_notes ? 'Update Notes' : 'Add Notes' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="">Select Status</option>
                            @foreach($recommendedStatuses as $statusOption)
                                <option value="{{ $statusOption['status'] }}"
                                        {{ $statusOption['is_recommended'] ? 'data-recommended="true"' : '' }}>
                                    {{ $statusOption['label'] }}
                                    {{ $statusOption['is_recommended'] ? '⭐' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Recommended statuses are marked with ⭐</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Add notes about this status change..."></textarea>
                    </div>

                    <!-- Delivery Timestamp Field (shown when status is 'delivered') -->
                    <div class="mb-3" id="deliveryTimestampField" style="display: none;">
                        <label class="form-label fw-semibold">
                            Delivery Date & Time
                            <span class="badge bg-warning text-dark ms-1">Required for Returns</span>
                        </label>
                        <input type="datetime-local" name="delivered_at" class="form-control"
                               value="{{ now()->format('Y-m-d\TH:i') }}">
                        <div class="form-text">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            This timestamp determines when the 7-day return window starts. Customers can request returns within 7 days of delivery.
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="notify_customer" id="notifyCustomer" checked>
                        <label class="form-check-label" for="notifyCustomer">
                            Notify customer about this status change
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Confirmation Modal -->
<div class="modal fade" id="statusConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <svg class="icon icon-2xl text-warning">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-2">Are you sure you want to change the order status?</h6>
                        <p class="mb-2">
                            <strong>Order:</strong> <span id="confirmOrderNumber">{{ $order->order_number }}</span><br>
                            <strong>Current Status:</strong> <span id="confirmCurrentStatus" class="badge {{ $order->getStatusBadgeColor($order->status) ? 'bg-' . $order->getStatusBadgeColor($order->status) : 'bg-secondary' }}">{{ $order->status_label }}</span><br>
                            <strong>New Status:</strong> <span id="confirmNewStatus" class="badge bg-primary"></span>
                        </p>
                        <div id="confirmNotes" class="alert alert-light" style="display: none;">
                            <strong>Notes:</strong> <span id="confirmNotesText"></span>
                        </div>
                        <div class="text-muted small">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            This action will update the order status and notify relevant parties.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusUpdate">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                    </svg>
                    Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tracking Modal (Home Delivery) -->
@if($order->isHomeDelivery())
<div class="modal fade" id="trackingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Tracking Information</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <form id="trackingForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tracking Number</label>
                        <input type="text" name="tracking_number" class="form-control"
                               value="{{ $order->tracking_number }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Courier Name</label>
                        <input type="text" name="courier_name" class="form-control"
                               value="{{ $order->courier_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estimated Delivery Date (Optional)</label>
                        <input type="date" name="estimated_delivery" class="form-control"
                               value="{{ $order->estimated_delivery ? $order->estimated_delivery->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Tracking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Pickup Modal (Office Pickup) -->
@if($order->isOfficePickup())
<div class="modal fade" id="pickupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Pickup Information</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <form id="pickupForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pickup Location</label>
                        <input type="text" name="pickup_location" class="form-control"
                               value="{{ $order->pickup_location }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pickup Date (Optional)</label>
                        <input type="datetime-local" name="pickup_date" class="form-control"
                               value="{{ $order->pickup_date ? $order->pickup_date->format('Y-m-d\TH:i') : '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pickup Instructions (Optional)</label>
                        <textarea name="pickup_instructions" class="form-control" rows="3">{{ $order->pickup_instructions }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Pickup Info</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $order->admin_notes ? 'Update' : 'Add' }} Admin Notes</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
            </div>
            <form id="notesForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="notes" class="form-control" rows="4" required>{{ $order->admin_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Notes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Timeline Notes Modal -->
<div class="modal fade" id="editTimelineNotesModal" tabindex="-1" aria-labelledby="editTimelineNotesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTimelineNotesModalLabel">Edit Timeline Notes</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTimelineNotesForm">
                @csrf
                <input type="hidden" id="edit_history_id" name="history_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" id="edit_status_label">Status</label>
                        <p class="text-muted small">Update the notes for this status change in the order timeline.</p>
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="4" placeholder="Enter notes for this status..."></textarea>
                        <div class="form-text">These notes will appear in the order timeline.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Notes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                </svg>
                <span id="successToastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x-circle') }}"></use>
                </svg>
                <span id="errorToastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e5e5;
}

.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    width: 30px;
    height: 30px;
    background: white;
    border: 2px solid #e5e5e5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-item.completed .timeline-marker {
    border-color: #28a745;
    background: #28a745;
}

.timeline-item.current .timeline-marker {
    border-color: #007bff;
    background: #007bff;
}

.timeline-content {
    margin-left: 1rem;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status update buttons
    document.querySelectorAll('.status-update-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const status = this.dataset.status;
            updateOrderStatus(status);
        });
    });

    // Show/hide delivery timestamp field based on selected status
    document.querySelector('select[name="status"]').addEventListener('change', function() {
        const deliveryTimestampField = document.getElementById('deliveryTimestampField');
        const selectedStatus = this.value;

        if (selectedStatus === 'delivered') {
            deliveryTimestampField.style.display = 'block';
        } else {
            deliveryTimestampField.style.display = 'none';
        }
    });

    // Status form - show confirmation modal instead of direct update
    document.getElementById('statusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const newStatus = formData.get('status');
        const notes = formData.get('notes');
        const notifyCustomer = formData.get('notify_customer') === 'on';
        const deliveredAt = formData.get('delivered_at');

        // Get status label from the selected option
        const statusSelect = this.querySelector('select[name="status"]');
        const selectedOption = statusSelect.options[statusSelect.selectedIndex];
        const statusLabel = selectedOption.text.replace('⭐', '').trim();

        // Update confirmation modal with details
        document.getElementById('confirmNewStatus').textContent = statusLabel;

        // Show/hide notes section
        const confirmNotes = document.getElementById('confirmNotes');
        const confirmNotesText = document.getElementById('confirmNotesText');
        if (notes && notes.trim()) {
            confirmNotesText.textContent = notes;
            confirmNotes.style.display = 'block';
        } else {
            confirmNotes.style.display = 'none';
        }

        // Store form data for confirmation
        window.pendingStatusUpdate = {
            status: newStatus,
            notes: notes,
            notifyCustomer: notifyCustomer,
            delivered_at: deliveredAt
        };

        // Hide status modal and show confirmation modal
        const statusModal = new coreui.Modal(document.getElementById('statusModal'));
        const confirmModal = new coreui.Modal(document.getElementById('statusConfirmModal'));

        statusModal.hide();
        setTimeout(() => {
            confirmModal.show();
        }, 300);
    });

    // Status confirmation handler
    document.getElementById('confirmStatusUpdate').addEventListener('click', function() {
        if (window.pendingStatusUpdate) {
            const { status, notes, notifyCustomer, delivered_at } = window.pendingStatusUpdate;

            // Hide confirmation modal
            const confirmModal = new coreui.Modal(document.getElementById('statusConfirmModal'));
            confirmModal.hide();

            // Perform the actual status update
            updateOrderStatus(status, notes, notifyCustomer, delivered_at);

            // Clear pending update
            window.pendingStatusUpdate = null;
        }
    });

    // Tracking form
    @if($order->isHomeDelivery())
    document.getElementById('trackingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('{{ route("admin.orders.update-tracking", $order) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                tracking_number: formData.get('tracking_number'),
                courier_name: formData.get('courier_name'),
                estimated_delivery: formData.get('estimated_delivery')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
    @endif

    // Pickup form
    @if($order->isOfficePickup())
    document.getElementById('pickupForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('{{ route("admin.orders.update-pickup", $order) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                pickup_location: formData.get('pickup_location'),
                pickup_date: formData.get('pickup_date'),
                pickup_instructions: formData.get('pickup_instructions')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
    @endif

    // Notes form
    document.getElementById('notesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('{{ route("admin.orders.add-notes", $order) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notes: formData.get('notes')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });

    function updateOrderStatus(status, notes = null, notifyCustomer = false, deliveredAt = null) {
        const requestBody = {
            status: status,
            notes: notes,
            notify_customer: notifyCustomer
        };

        // Include delivered_at timestamp if provided
        if (deliveredAt) {
            requestBody.delivered_at = deliveredAt;
        }

        fetch('{{ route("admin.orders.update-status", $order) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the order status.');
        });
    }
});

function getStatusBadgeColor(status) {
    const colors = {
        'pending': 'warning',
        'paid': 'info',
        'processing': 'primary',
        'confirmed': 'primary',
        'packing': 'primary',
        'ready_for_pickup': 'info',
        'pickup_notified': 'info',
        'ready_to_ship': 'info',
        'shipped': 'info',
        'out_for_delivery': 'info',
        'delivered': 'success',
        'received_in_office': 'success',
        'completed': 'success',
        'on_hold': 'warning',
        'cancelled': 'secondary',
        'refunded': 'secondary',
        'returned': 'secondary',
        'delivery_failed': 'danger',
        'payment_failed': 'danger',
        'failed': 'danger'
    };
    return colors[status] || 'secondary';
}

// Toast helper functions
function showSuccessToast(message) {
    const toastElement = document.getElementById('successToast');
    document.getElementById('successToastMessage').textContent = message;
    const toast = new coreui.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });
    toast.show();
}

function showErrorToast(message) {
    const toastElement = document.getElementById('errorToast');
    document.getElementById('errorToastMessage').textContent = message;
    const toast = new coreui.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    toast.show();
}

// Edit Timeline Notes
function editTimelineNotes(historyId, status, currentNotes) {
    const modal = new coreui.Modal(document.getElementById('editTimelineNotesModal'));
    const statusLabels = {!! json_encode(\App\Models\Order::getStatusLabels()) !!};

    // Set form values
    document.getElementById('edit_history_id').value = historyId;
    document.getElementById('edit_notes').value = currentNotes || '';
    document.getElementById('edit_status_label').textContent = statusLabels[status] || status;

    modal.show();
}

// Handle timeline notes update form submission
document.getElementById('editTimelineNotesForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const historyId = formData.get('history_id');
    const notes = formData.get('notes');

    fetch(`/admin/orders/status-history/${historyId}/update-notes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the timeline notes display
            const notesElement = document.getElementById('timeline-notes-' + historyId);
            if (notesElement) {
                if (notes) {
                    // Escape HTML and convert newlines to <br> tags
                    const escapedNotes = notes
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;')
                        .replace(/\n/g, '<br>');
                    notesElement.innerHTML = escapedNotes;
                    notesElement.style.display = 'block';
                } else {
                    notesElement.innerHTML = '';
                    notesElement.style.display = 'none';
                }
            }

            // Close modal
            const modal = coreui.Modal.getInstance(document.getElementById('editTimelineNotesModal'));
            modal.hide();

            // Show success toast
            showSuccessToast('Timeline notes updated successfully!');
        } else {
            showErrorToast(data.message || 'Failed to update notes');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorToast('An error occurred while updating the notes.');
    });
});
</script>
@endpush

