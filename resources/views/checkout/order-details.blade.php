@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-2">Order Details</h1>
                    <p class="text-muted">View and manage your order information</p>
                </div>
                <div>
                    <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Back to Packages
                    </a>
                </div>
            </div>

            <!-- Order Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list-numbered') }}"></use>
                            </svg>
                            Order Details
                        </h5>
                        <span class="{{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Order Information</h6>
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
                                    <td><span class="{{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Payment Status:</td>
                                    <td><span class="{{ $order->payment_status_badge_class }}">{{ ucfirst($order->payment_status) }}</span></td>
                                </tr>
                                @if($order->isPaid() && isset($order->metadata['payment']))
                                <tr>
                                    <td class="fw-semibold">Payment Method:</td>
                                    <td>{{ ucfirst($order->metadata['payment']['method'] ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Transaction ID:</td>
                                    <td><code>{{ $order->metadata['payment']['transaction_reference'] ?? 'N/A' }}</code></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Order Summary</h6>
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
                                    <td class="text-success">{{ number_format($order->points_awarded) }} points</td>
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
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                        </svg>
                        Order Items ({{ $order->getTotalItemsCount() }} items)
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($order->orderItems as $item)
                        <div class="border-bottom p-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ $item->package_image_url }}" alt="{{ $item->package_name }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $item->package_name }}</h6>
                                    @if($item->package_description)
                                        <p class="text-muted small mb-1">{{ Str::limit($item->package_description, 80) }}</p>
                                    @endif
                                    <div class="d-flex align-items-center text-sm">
                                        <span class="me-3">Quantity: <strong>{{ $item->quantity }}</strong></span>
                                        <span class="me-3">Unit Price: <strong>{{ $item->formatted_unit_price }}</strong></span>
                                        <span class="text-primary">Points: <strong>{{ number_format($item->total_points_awarded) }}</strong></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold h6 mb-0">{{ $item->formatted_total_price }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                        </svg>
                        Order Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($order->isPaid())
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                                </svg>
                                Payment Confirmed
                            </h6>
                            <p class="mb-2">Your payment has been processed successfully and your order is confirmed.</p>
                            <ul class="mb-0">
                                <li>Payment has been deducted from your wallet</li>
                                <li>Your order is being processed</li>
                                <li>Points have been credited to your account</li>
                                <li>You will receive email updates on your order status</li>
                            </ul>
                        </div>
                    @elseif($order->isPending())
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                </svg>
                                Payment Pending
                            </h6>
                            <p class="mb-2">Your order has been created but payment is still pending.</p>
                            <ul class="mb-0">
                                <li>Your order is awaiting payment confirmation</li>
                                <li>Please ensure you have sufficient wallet balance</li>
                                <li>Contact support if you need assistance with payment</li>
                                <li>Points will be credited once payment is completed</li>
                            </ul>
                        </div>
                    @elseif($order->isCancelled())
                        <div class="alert alert-secondary">
                            <h6 class="alert-heading">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x-circle') }}"></use>
                                </svg>
                                Order Cancelled
                            </h6>
                            <p class="mb-2">This order has been cancelled.</p>
                            @if($order->payment_status === 'refunded')
                                <p class="mb-0">The payment has been refunded to your wallet.</p>
                            @endif
                        </div>
                    @endif

                    @if($order->canBeCancelled())
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Order Cancellation</h6>
                        <p class="mb-2">You can cancel this order while it's in {{ $order->status }} status.</p>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-coreui-toggle="modal" data-coreui-target="#cancelOrderModal">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                            </svg>
                            Cancel Order
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('packages.index') }}" class="btn btn-outline-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                        </svg>
                        Continue Shopping
                    </a>
                </div>
                <div>
                    <!-- Additional action buttons can be added here in the future -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@if($order->canBeCancelled())
<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('checkout.cancel-order', $order) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to cancel order <strong>{{ $order->order_number }}</strong>?</p>
                    @if($order->isPaid())
                        <div class="alert alert-info">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            <strong>Refund Notice:</strong> Since this order has been paid, the full amount will be refunded to your wallet upon cancellation.
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for cancellation <span class="text-danger">*</span></label>
                        <select class="form-select" id="cancellation_reason" name="cancellation_reason" required>
                            <option value="">Select a reason...</option>
                            <option value="changed_mind">Changed my mind</option>
                            <option value="found_better_price">Found better price elsewhere</option>
                            <option value="payment_issues">Payment issues</option>
                            <option value="delivery_concerns">Delivery concerns</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Keep Order</button>
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection