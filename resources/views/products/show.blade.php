@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Back to Products
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 500px;">
                </div>
            </div>
        </div>

        <!-- Product Details -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column p-4">
                    <div> <!-- Content wrapper -->
                        <h1 class="h3 fw-bold mb-2">{{ $product->name }}</h1>
                        @if($product->category)
                            <span class="badge bg-info mb-3">{{ $product->category }}</span>
                        @endif

                        <p class="text-muted">{{ $product->short_description }}</p>

                        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-3">
                            <div class="h4 fw-bold text-primary mb-0">{{ currency($product->price) }}</div>
                            <div class="text-end">
                                <div class="fw-bold">{{ number_format($product->points_awarded) }}</div>
                                <small class="text-muted">Points Awarded</small>
                            </div>
                        </div>

                        @if($product->long_description)
                            <div class="mt-4">
                                <h5 class="fw-bold">Product Description</h5>
                                <div class="text-break">
                                    {!! $product->long_description !!}
                                </div>
                            </div>
                        @endif

                        @if($product->total_unilevel_bonus > 0)
                            <div class="alert alert-success mt-4">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                                </svg>
                                <strong>Unilevel Bonus:</strong> A total of <strong>{{ currency($product->total_unilevel_bonus) }}</strong> is distributed to the upline network for each unit purchased.
                            </div>
                        @endif
                    </div>

                    <div class="mt-auto"> <!-- CTA wrapper -->
                        <div class="mt-4">
                            @if($product->isAvailable())
                                @if($inCart)
                                    <button class="btn btn-success btn-lg w-100" disabled>
                                        <svg class="icon me-2">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                        </svg>
                                        Already in Cart
                                    </button>
                                @else
                                    <button class="btn btn-primary btn-lg w-100 add-to-cart-btn" data-product-id="{{ $product->id }}">
                                        <svg class="icon me-2">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                                        </svg>
                                        Add to Cart
                                    </button>
                                @endif
                            @else
                                <button class="btn btn-secondary btn-lg w-100" disabled>
                                    Unavailable
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="fw-bold mb-3">Related Products</h4>
            </div>
            @foreach($relatedProducts as $related)
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card h-100 product-card">
                        <a href="{{ route('products.show', $related->slug) }}">
                            <img src="{{ $related->image_url }}" class="card-img-top" alt="{{ $related->name }}" style="height: 180px; object-fit: cover;">
                        </a>
                        <div class="card-body">
                            <h6 class="card-title">
                                <a href="{{ route('products.show', $related->slug) }}" class="text-decoration-none">{{ $related->name }}</a>
                            </h6>
                            <div class="fw-bold text-primary">{{ currency($related->price) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            const btn = this;
            const originalHTML = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Adding...';

            try {
                const response = await fetch(`/products/${productId}/add-to-cart`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: 1 })
                });

                const data = await response.json();

                if (data.success) {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success');
                    btn.innerHTML = `<svg class="icon me-2"><use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use></svg>In Cart`;

                    const cartBadge = document.getElementById('cart-count');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                        cartBadge.style.display = data.cart_count > 0 ? 'inline' : 'none';
                    }

                    if (window.cartManager) {
                        window.cartManager.refreshCartDropdown();
                        window.cartManager.showMessage(data.message, 'success');
                    }
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                    if (window.cartManager) {
                        window.cartManager.showMessage(data.message, 'error');
                    }
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                if (window.cartManager) {
                    window.cartManager.showMessage('Failed to add product to cart. Please try again.', 'error');
                }
            }
        });
    }
});
</script>
@endpush
