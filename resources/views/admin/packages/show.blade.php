@extends('layouts.admin')

@section('title', 'Package Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Package Details: {{ $package->name }}</h5>
                        <div class="btn-group">
                            <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-pencil') }}"></use>
                                </svg>
                                Edit
                            </a>
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                                </svg>
                                Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Package Name</h6>
                                    <p class="h5">{{ $package->name }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Status</h6>
                                    <p>
                                        <span class="badge bg-{{ $package->is_active ? 'success' : 'warning' }} fs-6">
                                            {{ $package->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <h6 class="text-muted mb-2">Price</h6>
                                    <p class="h4 text-primary">{{ $package->formatted_price }}</p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted mb-2">Points Awarded</h6>
                                    <p class="h5">
                                        <span class="badge bg-info fs-6">{{ number_format($package->points_awarded) }} pts</span>
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted mb-2">Quantity Available</h6>
                                    <p>
                                        @if($package->quantity_available === null)
                                            <span class="badge bg-success fs-6">Unlimited</span>
                                        @else
                                            <span class="badge bg-{{ $package->quantity_available > 0 ? 'success' : 'danger' }} fs-6">
                                                {{ $package->quantity_available }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-muted mb-2">Sort Order</h6>
                                    <p>{{ $package->sort_order }}</p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">URL Slug</h6>
                                <p class="font-monospace">{{ $package->slug }}</p>
                                <small class="text-muted">Public URL: <code>{{ route('packages.show', $package) }}</code></small>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Short Description</h6>
                                <p>{{ $package->short_description }}</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Long Description</h6>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($package->long_description)) !!}
                                </div>
                            </div>

                            @if($package->meta_data)
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Package Information</h6>
                                    <div class="row">
                                        @if(isset($package->meta_data['duration']) && $package->meta_data['duration'])
                                            <div class="col-md-6 mb-3">
                                                <div class="p-3 bg-light rounded">
                                                    <h6 class="text-muted mb-2">
                                                        <svg class="icon me-2">
                                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                                                        </svg>
                                                        Duration
                                                    </h6>
                                                    <p class="mb-0 fw-semibold">{{ $package->meta_data['duration'] }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if(isset($package->meta_data['category']) && $package->meta_data['category'])
                                            <div class="col-md-6 mb-3">
                                                <div class="p-3 bg-light rounded">
                                                    <h6 class="text-muted mb-2">
                                                        <svg class="icon me-2">
                                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-tag') }}"></use>
                                                        </svg>
                                                        Category
                                                    </h6>
                                                    <p class="mb-0">
                                                        <span class="badge bg-secondary fs-6">{{ ucfirst($package->meta_data['category']) }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if(isset($package->meta_data['features']) && is_array($package->meta_data['features']) && count($package->meta_data['features']) > 0)
                                        <div class="p-3 bg-light rounded">
                                            <h6 class="text-muted mb-3">
                                                <svg class="icon me-2">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                                                </svg>
                                                Package Features
                                            </h6>
                                            <div class="row">
                                                @foreach($package->meta_data['features'] as $index => $feature)
                                                    <div class="col-md-6 mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <svg class="icon text-success me-2 flex-shrink-0">
                                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                                                            </svg>
                                                            <span>{{ $feature }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Created</h6>
                                    <p>{{ $package->created_at->format('M j, Y \a\t g:i A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Last Updated</h6>
                                    <p>{{ $package->updated_at->format('M j, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Package Image</h6>
                                </div>
                                <div class="card-body text-center">
                                    <img src="{{ $package->image_url }}" alt="{{ $package->name }}"
                                         class="img-fluid rounded shadow-sm mb-3">
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('packages.show', $package) }}" class="btn btn-outline-info" target="_blank">
                                            <svg class="icon me-2">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-external-link') }}"></use>
                                            </svg>
                                            View Public Page
                                        </a>

                                        <form method="POST" action="{{ route('admin.packages.toggle-status', $package) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-{{ $package->is_active ? 'warning' : 'success' }} w-100">
                                                <svg class="icon me-2">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-' . ($package->is_active ? 'ban' : 'check') . '') }}"></use>
                                                </svg>
                                                {{ $package->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>

                                        @if($package->canBeDeleted())
                                            <form method="POST" action="{{ route('admin.packages.destroy', $package) }}"
                                                  onsubmit="return confirm('Are you sure you want to delete this package? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger w-100">
                                                    <svg class="icon me-2">
                                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                                                    </svg>
                                                    Delete Package
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-outline-secondary w-100" disabled>
                                                <svg class="icon me-2">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                                </svg>
                                                Cannot Delete
                                            </button>
                                            <small class="text-muted">Package has been purchased by users</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($package->meta_data)
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Package Details</h6>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($package->meta_data['duration']) && $package->meta_data['duration'])
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Duration</small>
                                                <strong>{{ $package->meta_data['duration'] }}</strong>
                                            </div>
                                        @endif

                                        @if(isset($package->meta_data['category']) && $package->meta_data['category'])
                                            <div class="mb-3">
                                                <small class="text-muted d-block">Category</small>
                                                <span class="badge bg-secondary">{{ ucfirst($package->meta_data['category']) }}</span>
                                            </div>
                                        @endif

                                        @if(isset($package->meta_data['features']) && is_array($package->meta_data['features']) && count($package->meta_data['features']) > 0)
                                            <div class="mb-0">
                                                <small class="text-muted d-block mb-2">Features</small>
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($package->meta_data['features'] as $feature)
                                                        <li class="mb-1">
                                                            <svg class="icon text-success me-2">
                                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                                            </svg>
                                                            {{ $feature }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection