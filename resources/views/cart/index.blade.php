@extends('layouts.admin')

@section('title', 'Shopping Cart')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">Shopping Cart</h1>
                    <p class="text-muted">Review your selected packages</p>
                </div>
                <div>
                    <a href="{{ route('packages.index') }}" class="btn btn-outline-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(count($validationIssues) > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <svg class="icon me-2">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
            </svg>
            <strong>Cart Updated:</strong>
            <ul class="mb-0 mt-2">
                @foreach($validationIssues as $issue)
                    <li>{{ $issue }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($cartSummary['is_empty'])
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <svg class="icon icon-5xl text-muted mb-4">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                        </svg>
                        <h3 class="mb-3">Your cart is empty</h3>
                        <p class="text-muted mb-4">Add some packages to your cart to get started.</p>
                        <a href="{{ route('packages.index') }}" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                            </svg>
                            Browse Packages
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom spacing for empty cart -->
        <div class="pb-5"></div>
    @else
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4 mb-lg-0">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Cart Items ({{ $cartSummary['item_count'] }} items)</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                            </svg>
                            Clear Cart
                        </button>
                    </div>
                    <div class="card-body p-0">
                        @foreach($cartSummary['items_by_type'] as $type => $items)
                            @if(count($items) > 0)
                                <div class="p-3 bg-light border-bottom">
                                    <h6 class="mb-0 text-uppercase">{{ $type === 'packages' ? 'MLM Packages' : 'Unilevel Products' }}</h6>
                                </div>
                                @foreach($items as $itemId => $item)
                                    @php
                                        $itemSlug = $item['slug'];
                                        $itemRoute = $type === 'packages' ? route('packages.show', $itemSlug) : route('products.show', $itemSlug);
                                        $itemImage = $item['image_url'] ?? $item['image'];
                                        $itemPoints = $item['points_awarded'] ?? $item['points'] ?? 0;
                                    @endphp
                                    <div class="cart-item border-bottom p-4" data-item-id="{{ $itemId }}">
                                        <!-- Desktop Layout -->
                                        <div class="d-none d-lg-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <img src="{{ $itemImage }}" alt="{{ $item['name'] }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="flex-grow-1 me-4">
                                                <h6 class="mb-1"><a href="{{ $itemRoute }}" class="text-decoration-none">{{ $item['name'] }}</a></h6>
                                                <small class="text-muted">{{ $itemPoints }} points each</small>
                                            </div>
                                            <div class="text-center me-4" style="min-width: 80px;">
                                                <div class="fw-semibold">{{ currency($item['price']) }}</div>
                                            </div>
                                            <div class="me-4" style="min-width: 140px;">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" onclick="updateQuantityWithLoader(this, '{{ $itemId }}', {{ $item['quantity'] - 1 }})">-</button>
                                                    <span class="mx-3 fw-semibold">{{ $item['quantity'] }}</span>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" onclick="updateQuantityWithLoader(this, '{{ $itemId }}', {{ $item['quantity'] + 1 }})">+</button>
                                                </div>
                                            </div>
                                            <div class="text-center me-auto">
                                                <div class="fw-bold">{{ currency($item['price'] * $item['quantity']) }}</div>
                                            </div>
                                            <div class="ms-3">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem('{{ $itemId }}')" title="Remove item">
                                                    <svg class="icon"><use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use></svg>
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Mobile Layout -->
                                        <div class="d-lg-none">
                                            <div class="row">
                                                <div class="col-8">
                                                    <div class="d-flex">
                                                        <img src="{{ $itemImage }}" alt="{{ $item['name'] }}" class="rounded me-3 flex-shrink-0" style="width: 60px; height: 60px; object-fit: cover;">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1"><a href="{{ $itemRoute }}" class="text-decoration-none">{{ $item['name'] }}</a></h6>
                                                            <div class="text-muted small">{{ $itemPoints }} points each</div>
                                                            <div class="fw-semibold">{{ currency($item['price']) }} each</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-4 text-end">
                                                    <div class="fw-bold mb-2">{{ currency($item['price'] * $item['quantity']) }}</div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem('{{ $itemId }}')" title="Remove item">
                                                        <svg class="icon"><use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use></svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-muted">Quantity:</span>
                                                        <div class="d-flex align-items-center">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" onclick="updateQuantityWithLoader(this, '{{ $itemId }}', {{ $item['quantity'] - 1 }})">-</button>
                                                            <span class="mx-3 fw-semibold">{{ $item['quantity'] }}</span>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" onclick="updateQuantityWithLoader(this, '{{ $itemId }}', {{ $item['quantity'] + 1 }})">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card sticky-top sticky-order-summary">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>{{ currency($cartSummary['subtotal']) }}</span>
                        </div>

                        @if($cartSummary['has_packages'] && $cartSummary['has_products'])
                            <div class="d-flex justify-content-between mb-1 ps-3">
                                <small class="text-muted">Packages Subtotal</small>
                                <small class="text-muted">{{ currency($cartSummary['subtotals_by_type']['packages']) }}</small>
                            </div>
                            <div class="d-flex justify-content-between mb-2 ps-3">
                                <small class="text-muted">Products Subtotal</small>
                                <small class="text-muted">{{ currency($cartSummary['subtotals_by_type']['products']) }}</small>
                            </div>
                        @endif

                        @if($cartSummary['show_tax'])
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax ({{ number_format($cartSummary['tax_rate'] * 100, 1) }}%)</span>
                            <span>{{ currency($cartSummary['tax_amount']) }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total</strong>
                            <strong class="text-primary">{{ currency($cartSummary['total']) }}</strong>
                        </div>
                        <div class="alert alert-info">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                            </svg>
                            You will earn <strong>{{ number_format($cartSummary['total_points']) }} points</strong> from this order!
                        </div>
                        <div class="d-grid gap-2">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                                </svg>
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

<!-- Clear Cart Confirmation Modal -->
<div class="modal fade" id="clearCartModal" tabindex="-1" aria-labelledby="clearCartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearCartModalLabel">
                    <svg class="icon me-2 text-warning">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    Clear Cart
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <svg class="icon icon-2xl text-warning me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                    </svg>
                    <div>
                        <h6 class="mb-1">Are you sure you want to clear your entire cart?</h6>
                        <p class="text-muted mb-0 small">This action cannot be undone. All items will be removed from your cart.</p>
                    </div>
                </div>
                <div class="alert alert-warning mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                    </svg>
                    <strong>Note:</strong> You will need to add items back to your cart manually if you proceed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                    </svg>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmClearCart">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                    </svg>
                    Clear Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Item Confirmation Modal -->
<div class="modal fade" id="removeItemModal" tabindex="-1" aria-labelledby="removeItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeItemModalLabel">
                    <svg class="icon me-2 text-warning">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    Remove Item
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <svg class="icon icon-2xl text-danger me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                    </svg>
                    <div>
                        <h6 class="mb-1">Are you sure you want to remove this item from your cart?</h6>
                        <p class="text-muted mb-0 small" id="removeItemDetails">This item will be completely removed from your cart.</p>
                    </div>
                </div>
                <div class="card border-warning">
                    <div class="card-body p-3" id="removeItemPreview">
                        <!-- Item details will be populated here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                    </svg>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmRemoveItem">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                    </svg>
                    Remove Item
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .quantity-btn:disabled {
        opacity: 0.7;
    }

    .quantity-btn .spinner-border-sm {
        width: 0.75rem;
        height: 0.75rem;
    }

    .sticky-order-summary {
        z-index: 100 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function showMessage(message, type = 'info') {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }
    async function updateQuantityWithLoader(buttonElement, itemId, newQuantity) {
        if (newQuantity < 0) return;

        const originalContent = buttonElement.innerHTML;
        const originalDisabled = buttonElement.disabled;

        buttonElement.disabled = true;
        buttonElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            await updateQuantity(itemId, newQuantity);
            // If updateQuantity reloads, this part won't be reached.
            // If it succeeds without reloading, we should restore the button.
            buttonElement.disabled = originalDisabled;
            buttonElement.innerHTML = originalContent;
        } catch (error) {
            // Restore button state on any error
            buttonElement.disabled = originalDisabled;
            buttonElement.innerHTML = originalContent;
            // The error is already shown to the user by updateQuantity, so we just log it.
            console.error('Update failed:', error.message);
        }
    }

    async function updateQuantity(itemId, newQuantity) {
        if (newQuantity <= 0) {
            await removeItem(itemId);
            return;
        }

        console.log('Updating quantity:', { itemId, newQuantity });

        try {
            const url = window.cartRoutes.update.replace('{itemId}', itemId);
            console.log('Update URL:', url);

            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: newQuantity })
            });

            console.log('Response status:', response.status);

            if (!response.ok) {
                const data = await response.json();
                const errorMessage = data.message || `Server error: ${response.status}`;
                console.error('HTTP error response:', data);
                showMessage(errorMessage, 'error');
                throw new Error(errorMessage);
            }

            const data = await response.json();
            console.log('Response data:', data);

            if (data.success) {
                // On success, reload the page to show the updated cart state
                location.reload();
            } else {
                const errorMessage = data.message || 'An unknown error occurred.';
                showMessage(errorMessage, 'error');
                throw new Error(errorMessage);
            }
        } catch (error) {
            console.error('Error in updateQuantity:', error);
            // Re-throw the error to be caught by the caller (updateQuantityWithLoader)
            throw error;
        }
    }

    let currentRemoveItemId = null;

    function removeItem(itemId) {
        console.log('removeItem called with itemId:', itemId);

        // Store the item ID for later use
        currentRemoveItemId = itemId;

        // Get item details from the cart row
        const cartItem = document.querySelector(`[data-item-id="${itemId}"]`);
        console.log('Found cart item:', cartItem);

        if (!cartItem) {
            console.error('Cart item not found for item ID:', itemId);
            return;
        }

        const itemName = cartItem.querySelector('h6 a').textContent.trim();
        const itemImage = cartItem.querySelector('img').src;
        const itemPrice = cartItem.querySelector('.fw-semibold').textContent.trim();
        const quantitySpan = cartItem.querySelector('.mx-3.fw-semibold');
        const itemQuantity = quantitySpan ? quantitySpan.textContent.trim() : '1';

        console.log('Item details:', { itemName, itemImage, itemPrice, itemQuantity });

        // Populate the modal with item details
        document.getElementById('removeItemPreview').innerHTML = `
            <div class="d-flex align-items-center">
                <img src="${itemImage}" alt="${itemName}" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex-grow-1">
                    <div class="fw-semibold">${itemName}</div>
                    <div class="text-muted small">${itemQuantity} × ${itemPrice}</div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-danger">Remove</div>
                </div>
            </div>
        `;

        // Show the modal
        const modalElement = document.getElementById('removeItemModal');
        console.log('Modal element found:', modalElement);

        if (modalElement) {
            const modal = new coreui.Modal(modalElement);
            console.log('Modal instance created:', modal);
            modal.show();
            console.log('Modal.show() called');
        } else {
            console.error('Remove item modal not found in DOM');
        }
    }

    async function performRemoveItem() {
        if (!currentRemoveItemId) return;

        try {
            // Hide the modal first
            const modal = coreui.Modal.getInstance(document.getElementById('removeItemModal'));
            modal.hide();

            // Show loading state on the confirm button
            const confirmBtn = document.getElementById('confirmRemoveItem');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Removing...';

            const url = window.cartRoutes.remove.replace('{itemId}', currentRemoveItemId);
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Item removed from cart', 'success');
                location.reload(); // Reload to update the cart display
            } else {
                showMessage(data.message, 'error');
                // Reset button state
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error removing item:', error);
            showMessage('Error removing item', 'error');
            // Reset button state
            const confirmBtn = document.getElementById('confirmRemoveItem');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }

        // Clear the stored item ID
        currentRemoveItemId = null;
    }

    function clearCart() {
        // Show the modal instead of using confirm
        const modal = new coreui.Modal(document.getElementById('clearCartModal'));
        modal.show();
    }

    async function performClearCart() {
        try {
            // Hide the modal first
            const modal = coreui.Modal.getInstance(document.getElementById('clearCartModal'));
            modal.hide();

            // Show loading state on the confirm button
            const confirmBtn = document.getElementById('confirmClearCart');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Clearing...';

            const response = await fetch(window.cartRoutes.clear, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Cart cleared successfully', 'success');
                location.reload(); // Reload to show empty cart
            } else {
                showMessage('Error clearing cart', 'error');
                // Reset button state
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error clearing cart:', error);
            showMessage('Error clearing cart', 'error');
            // Reset button state
            const confirmBtn = document.getElementById('confirmClearCart');
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    }

    // Bind the confirm button to actually clear the cart
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded event fired');

        const clearBtn = document.getElementById('confirmClearCart');
        const removeBtn = document.getElementById('confirmRemoveItem');

        console.log('Clear button found:', clearBtn);
        console.log('Remove button found:', removeBtn);

        if (clearBtn) {
            clearBtn.addEventListener('click', performClearCart);
            console.log('Clear cart event listener attached');
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', performRemoveItem);
            console.log('Remove item event listener attached');
        }

        // Test if CoreUI is available
        console.log('CoreUI available:', typeof coreui !== 'undefined');
        console.log('CoreUI Modal available:', typeof coreui.Modal !== 'undefined');
    });
</script>
@endpush
@endsection