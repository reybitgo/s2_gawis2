@extends('layouts.admin')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-2">Order Details</h1>
                    <p class="text-muted">Order {{ $order->order_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Back to Orders
                    </a>
                    @if($order->isPaid())
                        <a href="{{ route('orders.invoice', $order) }}" class="btn btn-outline-info">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cloud-download') }}"></use>
                            </svg>
                            Download Invoice
                        </a>
                    @endif
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
                            Order Information
                        </h5>
                        <span class="{{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Order Details</h6>
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
                                <tr>
                                    <td class="fw-semibold">Delivery Method:</td>
                                    <td>
                                        <span class="badge bg-{{ $order->isOfficePickup() ? 'info' : 'primary' }}">
                                            {{ $order->delivery_method_label }}
                                        </span>
                                    </td>
                                </tr>
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

            <!-- Delivery Information (for Home Delivery) -->
            @if($order->isHomeDelivery() && $order->delivery_address)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-location-pin') }}"></use>
                        </svg>
                        Delivery Address
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Delivery Information</h6>
                            <div class="delivery-address">
                                <div class="mb-2">
                                    <strong>{{ $order->delivery_address['full_name'] ?? 'N/A' }}</strong>
                                </div>
                                <div class="mb-1">{{ $order->delivery_address['address'] ?? '' }}</div>
                                @if(!empty($order->delivery_address['address_2']))
                                    <div class="mb-1">{{ $order->delivery_address['address_2'] }}</div>
                                @endif
                                <div class="mb-1">
                                    {{ $order->delivery_address['city'] ?? '' }}{{ !empty($order->delivery_address['state']) ? ', ' . $order->delivery_address['state'] : '' }} {{ $order->delivery_address['zip'] ?? '' }}
                                </div>
                                @if(!empty($order->delivery_address['phone']))
                                    <div class="mb-2">
                                        <svg class="icon me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-phone') }}"></use>
                                        </svg>
                                        {{ $order->delivery_address['phone'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Delivery Preferences</h6>
                            <table class="table table-sm table-borderless">
                                @if(!empty($order->delivery_address['time_preference']))
                                <tr>
                                    <td class="fw-semibold">Preferred Time:</td>
                                    <td>
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
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-semibold">Delivery Window:</td>
                                    <td>3-5 business days</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Tracking:</td>
                                    <td>
                                        @if(!empty($order->tracking_number))
                                            <code>{{ $order->tracking_number }}</code>
                                        @else
                                            <span class="text-muted">Will be provided when shipped</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            @if(!empty($order->delivery_address['instructions']))
                            <div class="mt-3">
                                <h6 class="text-muted">Special Instructions</h6>
                                <div class="alert alert-light">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-comment-square') }}"></use>
                                    </svg>
                                    {{ $order->delivery_address['instructions'] }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Office Pickup Information (for Office Pickup) -->
            @if($order->isOfficePickup())
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-building') }}"></use>
                        </svg>
                        Office Pickup Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Pickup Location</h6>
                            <div class="mb-2">
                                <strong>Main Office</strong>
                            </div>
                            <div class="mb-1">123 Business Street</div>
                            <div class="mb-1">Business District, City 12345</div>
                            <div class="mb-2">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-phone') }}"></use>
                                </svg>
                                +1 (555) 123-4567
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Pickup Hours & Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-semibold">Monday - Friday:</td>
                                    <td>9:00 AM - 5:00 PM</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Saturday:</td>
                                    <td>9:00 AM - 2:00 PM</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Sunday:</td>
                                    <td>Closed</td>
                                </tr>
                            </table>

                            <div class="alert alert-warning mt-3">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                </svg>
                                <strong>Important:</strong> Please bring a valid ID when collecting your order.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

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
                                    @if($item->package && $item->package->isAvailable())
                                        <small class="text-success">
                                            <svg class="icon me-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                            </svg>
                                            Still available
                                        </small>
                                    @else
                                        <small class="text-muted">
                                            <svg class="icon me-1">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                            </svg>
                                            No longer available
                                        </small>
                                    @endif
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
                            @if(isset($order->metadata['cancellation_reason']))
                                <p class="mb-0"><strong>Reason:</strong> {{ ucfirst(str_replace('_', ' ', $order->metadata['cancellation_reason'])) }}</p>
                            @endif
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 flex-wrap">
                        @if($order->isPaid() || $order->isCompleted())
                            <form action="{{ route('orders.reorder', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary"
                                        onclick="return confirm('Add all items from this order to your cart?')">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                                    </svg>
                                    Reorder Items
                                </button>
                            </form>
                        @endif

                        @if($order->isPaid())
                            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-outline-info">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cloud-download') }}"></use>
                                </svg>
                                Download Invoice
                            </a>
                        @endif

                        @if($order->canBeCancelled())
                            <button type="button" class="btn btn-outline-danger"
                                    data-coreui-toggle="modal" data-coreui-target="#cancelOrderModal">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                </svg>
                                Cancel Order
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $hasReturnRequest = $order->returnRequest()->exists();
                $returnRequest = $order->returnRequest;
            @endphp

            <!-- Return Request Section (for delivered orders) -->
            @if($order->canRequestReturn() || $hasReturnRequest)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-action-undo') }}"></use>
                        </svg>
                        Return Request
                    </h5>
                </div>
                <div class="card-body">
                    @if(!$hasReturnRequest && $order->canRequestReturn())
                        <!-- Show return request form -->
                        <div class="alert alert-info">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            <strong>Return Window:</strong> You have {{ $order->getReturnWindowDaysRemaining() }} days remaining to request a return.
                        </div>
                        <button type="button" class="btn btn-warning" data-coreui-toggle="modal" data-coreui-target="#returnRequestModal">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-action-undo') }}"></use>
                            </svg>
                            Request Return
                        </button>
                    @elseif($hasReturnRequest)
                        <!-- Show return request status -->
                        <div class="mb-3">
                            <label class="fw-semibold">Return Status:</label>
                            <span class="{{ $returnRequest->status_badge_class }}">{{ $returnRequest->status_label }}</span>
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold">Reason:</label>
                            <div>{{ $returnRequest->reason_label }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-semibold">Description:</label>
                            <div class="text-muted">{{ $returnRequest->description }}</div>
                        </div>
                        @if($returnRequest->images && count($returnRequest->images) > 0)
                        <div class="mb-3">
                            <label class="fw-semibold">Proof Images:</label>
                            <div class="d-flex gap-2 flex-wrap mt-2">
                                @foreach($returnRequest->images as $image)
                                    <img src="{{ asset('storage/' . $image) }}" alt="Return proof" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if($returnRequest->admin_response)
                        <div class="mb-3">
                            <label class="fw-semibold">Admin Response:</label>
                            <div class="alert alert-light">{{ $returnRequest->admin_response }}</div>
                        </div>
                        @endif

                        @if($returnRequest->isApproved() && !$returnRequest->return_tracking_number)
                        <!-- Show tracking number form -->
                        <div class="alert alert-success">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                            </svg>
                            <strong>Return Approved!</strong> Please ship the item back and provide the tracking number below.
                        </div>
                        <form action="{{ route('returns.update-tracking', $returnRequest) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="return_tracking_number" class="form-label fw-semibold">Return Tracking Number</label>
                                <input type="text" class="form-control" id="return_tracking_number" name="return_tracking_number" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Tracking Number</button>
                        </form>
                        @elseif($returnRequest->return_tracking_number)
                        <div class="mb-3">
                            <label class="fw-semibold">Return Tracking Number:</label>
                            <div><code>{{ $returnRequest->return_tracking_number }}</code></div>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                        </svg>
                        All Orders
                    </a>
                </div>
                <div>
                    <a href="{{ route('packages.index') }}" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                        </svg>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

<!-- Return Request Modal -->
@if($order->canRequestReturn())
<div class="modal fade" id="returnRequestModal" tabindex="-1" aria-labelledby="returnRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnRequestModalLabel">Request Return</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('returns.store', $order) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                        </svg>
                        <strong>Return Policy:</strong> You have {{ $order->getReturnWindowDaysRemaining() }} days remaining to request a return. Please provide detailed information for faster processing.
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">Reason for Return <span class="text-danger">*</span></label>
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
                        <label for="description" class="form-label fw-semibold">Detailed Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="4" required minlength="20" maxlength="1000"
                                  placeholder="Please provide a detailed description of the issue (minimum 20 characters)..."></textarea>
                        <div class="form-text">Minimum 20 characters. Be as specific as possible to help us process your return quickly.</div>
                    </div>

                    <div class="mb-3">
                        <label for="images" class="form-label fw-semibold">
                            Proof Images <span class="badge bg-success ms-1">Recommended</span>
                        </label>
                        <input class="form-control" type="file" id="images" name="images[]" multiple accept="image/*">
                        <div class="form-text">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lightbulb') }}"></use>
                            </svg>
                            Upload photos showing the issue for faster approval. Maximum 2MB per image.
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Before You Submit</h6>
                        <ul class="mb-0 small">
                            <li>Ensure your description is detailed and accurate</li>
                            <li>Upload clear photos of any damage or issues</li>
                            <li>We'll review your request within 24 hours</li>
                            <li>Once approved, you'll receive shipping instructions</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-action-undo') }}"></use>
                        </svg>
                        Submit Return Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($order->canBeCancelled())
<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('orders.cancel', $order) }}" method="POST">
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