@extends('layouts.admin')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">Available Products</h1>
                    <p class="text-muted">Browse our consumable health and wellness products</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <form method="GET" action="{{ route('products.index') }}" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search products..."
                       value="{{ request('search') }}">
                @if(request()->except('search'))
                    @foreach(request()->except('search') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                @endif
                <button type="submit" class="btn btn-outline-primary">
                    <svg class="icon">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-search') }}"></use>
                    </svg>
                </button>
            </form>
        </div>
        <div class="col-md-3">
            <select class="form-select" onchange="location = this.value;">
                <option value="{{ route('products.index', request()->except('category')) }}"
                        {{ !request('category') ? 'selected' : '' }}>All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ route('products.index', ['category' => $cat] + request()->except('category')) }}"
                            {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" onchange="location = this.value;">
                <option value="{{ route('products.index', request()->except(['min_price', 'max_price'])) }}">All Prices</option>
                <option value="{{ route('products.index', ['min_price' => 0, 'max_price' => 500] + request()->except(['min_price', 'max_price'])) }}"
                        {{ request('min_price') == 0 && request('max_price') == 500 ? 'selected' : '' }}>₱0 - ₱500</option>
                <option value="{{ route('products.index', ['min_price' => 500, 'max_price' => 1000] + request()->except(['min_price', 'max_price'])) }}"
                        {{ request('min_price') == 500 && request('max_price') == 1000 ? 'selected' : '' }}>₱500 - ₱1,000</option>
                <option value="{{ route('products.index', ['min_price' => 1000] + request()->except(['min_price', 'max_price'])) }}"
                        {{ request('min_price') == 1000 && !request('max_price') ? 'selected' : '' }}>₱1,000+</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" onchange="location = this.value;">
                <option value="{{ route('products.index', request()->except('sort')) }}"
                        {{ !request('sort') ? 'selected' : '' }}>Sort by Default</option>
                <option value="{{ route('products.index', ['sort' => 'price_asc'] + request()->except('sort')) }}"
                        {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="{{ route('products.index', ['sort' => 'price_desc'] + request()->except('sort')) }}"
                        {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="{{ route('products.index', ['sort' => 'name'] + request()->except('sort')) }}"
                        {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                <option value="{{ route('products.index', ['sort' => 'newest'] + request()->except('sort')) }}"
                        {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
            </select>
        </div>
        <div class="col-md-2">
            <x-per-page-selector :perPage="$perPage" />
        </div>
    </div>

    @if($products->count() > 0)
        <div class="row">
            @foreach($products as $product)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 product-card">
                        <div class="card-img-top-wrapper" style="height: 150px; overflow: hidden; position: relative;">
                            <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                            @if($product->category)
                                <span class="badge bg-info position-absolute top-0 start-0 m-2">{{ $product->category }}</span>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0 fw-bold">{{ $product->name }}</h6>
                                @if($product->quantity_available !== null && $product->quantity_available <= 10)
                                    <span class="badge bg-warning small">Limited</span>
                                @endif
                            </div>

                            @if($product->sku)
                                <small class="text-muted mb-2" style="font-size: 0.75rem;">SKU: {{ $product->sku }}</small>
                            @endif

                            <p class="card-text text-muted small mb-2" style="line-height: 1.4;">
                                {{ Str::limit($product->short_description, 80) }}
                            </p>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-primary">{{ currency($product->price) }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Price</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-success">{{ number_format($product->points_awarded) }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Points</small>
                                    </div>
                                </div>
                            </div>

                            @if($product->total_unilevel_bonus > 0)
                                <div class="alert alert-success py-1 px-2 mb-2" style="font-size: 0.8rem;">
                                    <svg class="icon me-1" style="width: 14px; height: 14px;">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                                    </svg>
                                    <strong>Bonus:</strong> {{ currency($product->total_unilevel_bonus) }}
                                </div>
                            @else
                                <div style="height: 34px;" class="mb-2"></div>
                            @endif

                            <div class="mt-auto">
                                @if($product->quantity_available !== null)
                                    <div class="mb-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            @if($product->quantity_available > 0)
                                                {{ $product->quantity_available }} units available
                                            @else
                                                Out of stock
                                            @endif
                                        </small>
                                    </div>
                                @endif

                                <div class="d-grid gap-1">
                                    <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-primary btn-sm">
                                        View Details
                                    </a>
                                    @if($product->isAvailable())
                                        @if($inCart[$product->id] ?? false)
                                            <button class="btn btn-success btn-sm product-in-cart-btn" disabled>
                                                <svg class="icon me-1" style="width: 14px; height: 14px;">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                                </svg>
                                                In Cart
                                            </button>
                                        @else
                                            <button class="btn btn-primary btn-sm add-to-cart-btn" data-product-id="{{ $product->id }}">
                                                <svg class="icon me-1" style="width: 14px; height: 14px;">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                                                </svg>
                                                Add to Cart
                                            </button>
                                        @endif
                                    @else
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            Unavailable
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                    </div>
                    {{ $products->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <svg class="icon icon-xxl text-muted mb-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                    </svg>
                    <h5 class="text-muted">No products found</h5>
                    <p class="text-muted">
                        @if(request('search'))
                            No products match your search "{{ request('search') }}".
                            <a href="{{ route('products.index') }}">View all products</a>
                        @else
                            There are no products available at the moment.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Bottom spacing -->
<div class="pb-5"></div>

<style>
.product-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.add-to-cart-btn {
    transition: all 0.2s ease-in-out;
}

.add-to-cart-btn:hover {
    transform: translateY(-1px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // AJAX Add to Cart for Products
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            const btn = this;
            const originalHTML = btn.innerHTML;

            // Disable button and show loading
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
                    body: JSON.stringify({
                        quantity: 1
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update button to "In Cart" state
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success', 'product-in-cart-btn');
                    btn.innerHTML = `<svg class="icon me-1" style="width: 14px; height: 14px;">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                    </svg>In Cart`;

                    // Update cart count in header if it exists
                    const cartBadge = document.getElementById('cart-count');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                        cartBadge.style.display = data.cart_count > 0 ? 'inline' : 'none';
                    }

                    // Refresh the cart dropdown in the header
                    if (window.cartManager) {
                        window.cartManager.refreshCartDropdown();
                        window.cartManager.showMessage(data.message, 'success');
                    }
                } else {
                    // Restore button state
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
    });
});
</script>
@endsection
