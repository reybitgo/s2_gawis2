<div class="list-group list-group-flush">
@foreach($orders as $order)
    <div class="list-group-item">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex flex-grow-1">
                <!-- Order Icon/Avatar -->
                <div class="me-3">
                    @if($order->isPaid())
                        <div class="avatar avatar-md bg-success text-white">
                            <svg class="icon">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                            </svg>
                        </div>
                    @elseif($order->isCancelled())
                        <div class="avatar avatar-md bg-secondary text-white">
                            <svg class="icon">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                            </svg>
                        </div>
                    @else
                        <div class="avatar avatar-md bg-warning text-white">
                            <svg class="icon">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Order Content -->
                <div class="flex-grow-1">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center mb-1 gap-2">
                        <h6 class="mb-0 me-2">
                            <a href="{{ route('orders.show', $order) }}" class="text-decoration-none">
                                {{ $order->order_number }}
                            </a>
                        </h6>
                        <!-- Status Badges -->
                        <div class="d-flex flex-wrap gap-1">
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success">Paid</span>
                                @if($order->status !== 'completed' && $order->status !== 'paid')
                                    <span class="{{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                                @endif
                            @elseif($order->payment_status === 'failed')
                                <span class="badge bg-danger">Payment Failed</span>
                            @elseif($order->payment_status === 'refunded')
                                <span class="badge bg-warning">Refunded</span>
                            @else
                                <span class="{{ $order->status_badge_class }}">{{ ucfirst($order->status) }}</span>
                                @if($order->payment_status === 'pending' && $order->status !== 'pending')
                                    <span class="badge bg-warning">Payment Pending</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- Order Details -->
                    <div class="d-flex flex-wrap text-body-secondary small gap-3 mb-2">
                        <div class="d-flex align-items-center">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                            </svg>
                            {{ $order->created_at->format('M d, Y g:i A') }}
                        </div>
                        <div class="d-flex align-items-center">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                            </svg>
                            <span class="fw-semibold">{{ currency($order->total_amount) }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                            </svg>
                            {{ $order->getTotalItemsCount() }} items
                        </div>
                        @if($order->points_awarded > 0)
                            <div class="d-flex align-items-center text-warning">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                                </svg>
                                {{ number_format($order->points_awarded) }} points
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2 flex-wrap ms-2">
                <!-- View Details -->
                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                    <svg class="icon me-1">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                    </svg>
                    <span class="d-none d-md-inline">Details</span>
                </a>

                <!-- Dropdown Menu -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            data-coreui-toggle="dropdown" title="More Actions">
                        <svg class="icon">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-options') }}"></use>
                        </svg>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('orders.show', $order) }}">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                </svg>
                                View Details
                            </a>
                        </li>
                        @if($order->isPaid())
                            <li>
                                <a class="dropdown-item" href="{{ route('orders.invoice', $order) }}">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cloud-download') }}"></use>
                                    </svg>
                                    Download Invoice
                                </a>
                            </li>
                        @endif
                        @if($order->isPaid() || $order->isCompleted())
                            <li>
                                <button type="button" class="dropdown-item"
                                        data-coreui-toggle="modal" data-coreui-target="#reorderModal{{ $order->id }}">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                                    </svg>
                                    Reorder Items
                                </button>
                            </li>
                        @endif
                        @if($order->canBeCancelled())
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item text-danger"
                                        data-coreui-toggle="modal" data-coreui-target="#cancelOrderModal{{ $order->id }}">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                    </svg>
                                    Cancel Order
                                </button>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Order Items Preview (Collapsible) -->
        <div class="mt-3">
            <button class="btn btn-link btn-sm p-0 text-muted" type="button"
                    data-coreui-toggle="collapse" data-coreui-target="#orderItems{{ $order->id }}">
                <svg class="icon me-1">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chevron-right') }}"></use>
                </svg>
                View Order Items ({{ $order->orderItems->count() }})
            </button>
            <div class="collapse" id="orderItems{{ $order->id }}">
                <div class="mt-2">
                    @foreach($order->orderItems as $item)
                        <div class="d-flex align-items-center py-2 border-top">
                            <img src="{{ $item->item_image_url }}" alt="{{ $item->item_name }}"
                                 class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $item->item_name }}</div>
                                <small class="text-muted">
                                    Qty: {{ $item->quantity }} × {{ $item->formatted_unit_price }} = {{ $item->formatted_total_price }}
                                </small>
                            </div>
                            @if($item->total_points_awarded > 0)
                                <div class="text-warning small">
                                    <svg class="icon me-1">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                                    </svg>
                                    {{ number_format($item->total_points_awarded) }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal for each order -->
    @if($order->canBeCancelled())
        <div class="modal fade" id="cancelOrderModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Order</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
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
                                <label for="cancellation_reason{{ $order->id }}" class="form-label">Reason for cancellation <span class="text-danger">*</span></label>
                                <select class="form-select" id="cancellation_reason{{ $order->id }}" name="cancellation_reason" required>
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

    <!-- Reorder Confirmation Modal for each order -->
    @if($order->isPaid() || $order->isCompleted())
        <div class="modal fade" id="reorderModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reorder Items</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md bg-primary text-white me-3">
                                <svg class="icon">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                                </svg>
                            </div>
                            <div>
                                <h6 class="mb-1">Add items to cart</h6>
                                <small class="text-muted">Order {{ $order->order_number }}</small>
                            </div>
                        </div>

                        <p>Do you want to add all items from this order to your shopping cart?</p>

                        <div class="alert alert-info">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            <strong>Note:</strong> This will add {{ $order->orderItems->count() }} item(s) to your cart with a total value of {{ $order->formatted_total_amount }}.
                        </div>

                        <!-- Order Items Preview -->
                        <div class="border rounded p-3 bg-light">
                            <h6 class="mb-2">Items to be added:</h6>
                            @foreach($order->orderItems->take(3) as $item)
                                <div class="d-flex align-items-center py-1">
                                    <img src="{{ $item->item_image_url }}" alt="{{ $item->item_name }}"
                                         class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <small class="fw-semibold">{{ $item->item_name }}</small>
                                        <small class="text-muted d-block">Qty: {{ $item->quantity }}</small>
                                    </div>
                                    <small class="text-muted">{{ $item->formatted_total_price }}</small>
                                </div>
                            @endforeach
                            @if($order->orderItems->count() > 3)
                                <small class="text-muted">... and {{ $order->orderItems->count() - 3 }} more item(s)</small>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                        <form action="{{ route('orders.reorder', $order) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                                </svg>
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
</div>