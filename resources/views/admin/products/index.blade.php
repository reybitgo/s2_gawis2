@extends('layouts.admin')

@section('title', 'Product Management')
@section('page-title', 'Product Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div>
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                            </svg>
                            <strong>Products (Unilevel System)</strong>
                            <small class="text-body-secondary ms-2 d-none d-md-inline">
                                Manage product catalog and unilevel commission structure
                            </small>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                </svg>
                                Add New Product
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="per_page" class="form-select">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 per page</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 per page</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 per page</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 per page</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </form>

                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Points</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr class="{{ $product->trashed() ? 'table-secondary' : '' }}">
                                            <td>
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                     class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $product->name }}</strong>
                                                    @if($product->trashed())
                                                        <span class="badge bg-secondary ms-2">Deleted</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td><code>{{ $product->sku }}</code></td>
                                            <td><span class="badge bg-info">{{ $product->category }}</span></td>
                                            <td>{{ $product->formatted_price }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ number_format($product->points_awarded) }} pts</span>
                                            </td>
                                            <td>
                                                @if($product->quantity_available === null)
                                                    <span class="badge bg-success">Unlimited</span>
                                                @else
                                                    <span class="badge bg-{{ $product->quantity_available > 0 ? 'success' : 'danger' }}">
                                                        {{ $product->quantity_available }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$product->trashed())
                                                    <span class="badge bg-{{ $product->is_active ? 'success' : 'warning' }}">
                                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Deleted</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="d-flex gap-1 justify-content-center align-items-center">
                                                    @if(!$product->trashed())
                                                        <a href="{{ route('admin.products.show', $product) }}"
                                                           class="btn btn-sm btn-outline-info" title="View">
                                                            <svg class="icon">
                                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                                            </svg>
                                                        </a>
                                                        <a href="{{ route('admin.products.edit', $product) }}"
                                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <svg class="icon">
                                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-pencil') }}"></use>
                                                            </svg>
                                                        </a>
                                                        <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-{{ $product->is_active ? 'warning' : 'success' }}"
                                                                    title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                                                <svg class="icon">
                                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-' . ($product->is_active ? 'ban' : 'check') . '') }}"></use>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                        @if($product->canBeDeleted())
                                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                                    data-coreui-toggle="modal" data-coreui-target="#deleteModal"
                                                                    onclick="setDeleteProduct('{{ $product->id }}', '{{ $product->name }}', '{{ route('admin.products.destroy', $product) }}')">
                                                                <svg class="icon">
                                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                                                                </svg>
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete - has been purchased">
                                                                <svg class="icon">
                                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $products->appends(request()->query())->links() }}
                    @else
                        <div class="text-center py-5">
                            <svg class="icon icon-xxl text-muted mb-3">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                            </svg>
                            <h5 class="text-muted">No products found</h5>
                            <p class="text-muted">Get started by creating your first product for the Unilevel system.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                </svg>
                                Create Product
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <svg class="icon text-danger me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    Confirm Product Deletion
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <svg class="icon icon-xl text-danger">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                        </svg>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">Are you sure you want to delete this product?</h6>
                        <p class="text-muted mb-0">You are about to delete <strong id="productName"></strong>. This action cannot be undone.</p>
                    </div>
                </div>
                <div class="alert alert-danger mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    <strong>Warning:</strong> This will permanently remove the product and all its associated data.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                    </svg>
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                        </svg>
                        Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function setDeleteProduct(productId, productName, actionUrl) {
    document.getElementById('productName').textContent = productName;
    document.getElementById('deleteForm').action = actionUrl;
}
</script>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@endsection

@push('styles')
<style>
/* Action buttons spacing and styling */
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    white-space: nowrap;
}

.btn-sm .icon {
    width: 1rem;
    height: 1rem;
}

/* Ensure action buttons stay in one line on all screen sizes */
td .d-flex {
    flex-wrap: nowrap !important;
    white-space: nowrap;
}

/* Keep actions column from wrapping */
.text-nowrap {
    white-space: nowrap !important;
}

/* On smaller screens, make buttons more compact */
@media (max-width: 991.98px) {
    table td .d-flex.gap-1 {
        gap: 0.25rem !important;
    }
    
    table .btn-sm {
        padding: 0.3rem 0.45rem;
        font-size: 0;
        min-width: auto;
        border-radius: 0.25rem;
    }
    
    table .btn-sm .icon {
        width: 0.875rem;
        height: 0.875rem;
        margin: 0 !important;
    }
}

/* For very small screens, make buttons even more compact */
@media (max-width: 575.98px) {
    table td .d-flex.gap-1 {
        gap: 0.2rem !important;
    }
    
    table .btn-sm {
        padding: 0.25rem 0.4rem;
        border-radius: 0.2rem;
    }
    
    table .btn-sm .icon {
        width: 0.8rem;
        height: 0.8rem;
    }
}

/* Ensure table is horizontally scrollable */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Prevent table cells from collapsing */
table td {
    vertical-align: middle;
}

table td.text-nowrap {
    min-width: fit-content;
}
</style>
@endpush
