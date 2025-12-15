@extends('layouts.admin')

@section('title', 'Package Management')
@section('page-title', 'Package Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div>
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                            </svg>
                            <strong>Packages</strong>
                            <small class="text-body-secondary ms-2 d-none d-md-inline">
                                @if($packages->count() > 0)
                                    Showing {{ $packages->firstItem() }} to {{ $packages->lastItem() }} of {{ $packages->total() }} packages
                                @else
                                    No packages found
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                            </svg>
                            Add New Package
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($packages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Points</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Sort</th>
                                        <th class="text-center">Plan</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($packages as $package)
                                        <tr class="{{ $package->trashed() ? 'table-secondary' : '' }}">
                                            <td>
                                                <img src="{{ $package->image_url }}" alt="{{ $package->name }}"
                                                     class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $package->name }}</strong>
                                                    @if($package->trashed())
                                                        <span class="badge bg-secondary ms-2">Deleted</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $package->formatted_price }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ number_format($package->points_awarded) }} pts</span>
                                            </td>
                                            <td>
                                                @if($package->quantity_available === null)
                                                    <span class="badge bg-success">Unlimited</span>
                                                @else
                                                    <span class="badge bg-{{ $package->quantity_available > 0 ? 'success' : 'danger' }}">
                                                        {{ $package->quantity_available }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!$package->trashed())
                                                    <span class="badge bg-{{ $package->is_active ? 'success' : 'warning' }}">
                                                        {{ $package->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Deleted</span>
                                                @endif
                                            </td>
                                            <td>{{ $package->sort_order }}</td>
                                            <td class="text-center">
                                                @if($package->is_mlm_package)
                                                    <svg class="icon text-success">
                                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                                    </svg>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="d-flex gap-1 justify-content-center align-items-center">
                                                    @if(!$package->trashed())
                                                        <a href="{{ route('admin.packages.show', $package) }}"
                                                           class="btn btn-sm btn-outline-info" title="View">
                                                            <svg class="icon">
                                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                                            </svg>
                                                        </a>
                                                        <a href="{{ route('admin.packages.edit', $package) }}"
                                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <svg class="icon">
                                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-pencil') }}"></use>
                                                            </svg>
                                                        </a>
                                                        <form method="POST" action="{{ route('admin.packages.toggle-status', $package) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-{{ $package->is_active ? 'warning' : 'success' }}"
                                                                    title="{{ $package->is_active ? 'Deactivate' : 'Activate' }}">
                                                                <svg class="icon">
                                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-' . ($package->is_active ? 'ban' : 'check') . '') }}"></use>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                        @if($package->canBeDeleted())
                                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Delete"
                                                                    data-coreui-toggle="modal" data-coreui-target="#deleteModal"
                                                                    onclick="setDeletePackage('{{ $package->id }}', '{{ $package->name }}', '{{ route('admin.packages.destroy', $package) }}')">
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
                    </div>
                    @if($packages->hasPages())
                        <div class="card-footer">
                            {{ $packages->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    <div class="card-body">
                        <div class="text-center py-5">
                            <svg class="icon icon-xxl text-muted mb-3">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                            </svg>
                            <h5 class="text-muted">No packages found</h5>
                            <p class="text-muted">Get started by creating your first package.</p>
                            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                </svg>
                                Create Package
                            </a>
                        </div>
                    </div>
                @endif
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
                    Confirm Package Deletion
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
                        <h6 class="mb-1">Are you sure you want to delete this package?</h6>
                        <p class="text-muted mb-0">You are about to delete <strong id="packageName"></strong>. This action cannot be undone.</p>
                    </div>
                </div>
                <div class="alert alert-danger mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    <strong>Warning:</strong> This will permanently remove the package and all its associated data.
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
                        Delete Package
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function setDeletePackage(packageId, packageName, actionUrl) {
    document.getElementById('packageName').textContent = packageName;
    document.getElementById('deleteForm').action = actionUrl;
}
</script>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@endsection

@push('styles')
<style>
/* Pagination improvements - prevent overflow */
.card-footer {
    overflow-x: auto;
    overflow-y: hidden;
}

.card-footer nav {
    min-width: fit-content;
}

.pagination {
    flex-wrap: wrap;
    margin-bottom: 0;
}

.pagination .page-item {
    margin: 2px;
}

.pagination .page-link {
    min-width: 32px;
    text-align: center;
}

/* Mobile responsiveness improvements */
@media (max-width: 767.98px) {
    .card-header {
        padding: 1rem;
    }
    
    .card-header h4, .card-header h5 {
        font-size: 1.1rem;
    }
    
    /* Make list items more mobile-friendly */
    .card-body {
        padding: 0.75rem;
    }
    
    /* Adjust statistics cards on mobile */
    .row.g-3 {
        gap: 0.5rem !important;
    }
    
    .card-body.pb-0 {
        padding: 0.75rem !important;
    }
    
    .fs-4 {
        font-size: 1.25rem !important;
    }
    
    /* Button improvements */
    .btn-sm {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Pagination on mobile */
    .card-footer {
        padding: 0.75rem;
    }
    
    .pagination .page-item .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        min-width: 28px;
    }
    
    /* Hide some pagination numbers on mobile */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
}

@media (max-width: 575.98px) {
    /* Extra small screens */
    /* Hide verbose text on mobile */
    .d-none-xs {
        display: none !important;
    }
    
    /* More aggressive pagination hiding on very small screens */
    .pagination .page-item:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
    
    /* Make pagination info smaller */
    .card-footer small {
        font-size: 0.75rem;
    }
}

/* Prevent card header from overflowing */
.card-header {
    overflow: hidden;
}

.card-header > div {
    min-width: 0;
}

/* Prevent card footer from overflowing */
.card-footer {
    overflow-x: auto;
}

/* Improve badge visibility */
.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

/* Gradient backgrounds for stats cards */
.bg-primary-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.bg-success-gradient {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
}

.bg-danger-gradient {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #0dcaf0 0%, #0baccc 100%);
}

/* Scrollbar styling for pagination overflow */
.card-footer::-webkit-scrollbar {
    height: 6px;
}

.card-footer::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.card-footer::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.card-footer::-webkit-scrollbar-thumb:hover {
    background: #555;
}

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