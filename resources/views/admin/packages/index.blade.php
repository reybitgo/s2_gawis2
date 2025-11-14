@extends('layouts.admin')

@section('title', 'Package Management')
@section('page-title', 'Package Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Packages</h5>
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
                                                <small class="text-muted">{{ Str::limit($package->short_description, 50) }}</small>
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
                                            <td>
                                                <div class="btn-group" role="group">
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
                                                        <form method="POST" action="{{ route('admin.packages.toggle-status', $package) }}" class="d-inline">
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

                        {{ $packages->appends(request()->query())->links() }}
                    @else
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