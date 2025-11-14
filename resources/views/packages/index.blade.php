@extends('layouts.admin')

@section('title', 'Packages')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">Available Packages</h1>
                    <p class="text-muted">Choose the perfect package for your needs</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <form method="GET" action="{{ route('packages.index') }}" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search packages..."
                       value="{{ request('search') }}">
                <button type="submit" class="btn btn-outline-primary">
                    <svg class="icon">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-search') }}"></use>
                    </svg>
                </button>
            </form>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-md-end">
                <select class="form-select" onchange="location = this.value;" style="max-width: 250px;">
                    <option value="{{ route('packages.index') }}"
                            {{ !request('sort') ? 'selected' : '' }}>Sort by Default</option>
                    <option value="{{ route('packages.index', ['sort' => 'price_low'] + request()->query()) }}"
                            {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="{{ route('packages.index', ['sort' => 'price_high'] + request()->query()) }}"
                            {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="{{ route('packages.index', ['sort' => 'points_high'] + request()->query()) }}"
                            {{ request('sort') == 'points_high' ? 'selected' : '' }}>Most Points</option>
                    <option value="{{ route('packages.index', ['sort' => 'name'] + request()->query()) }}"
                            {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                </select>
            </div>
        </div>
    </div>

    @if($packages->count() > 0)
        <div class="row">
            @foreach($packages as $package)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 package-card">
                        <div class="card-img-top-wrapper" style="height: 150px; overflow: hidden;">
                            <img src="{{ $package->image_url }}" class="card-img-top" alt="{{ $package->name }}"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0 fw-bold">{{ $package->name }}</h6>
                                @if($package->quantity_available !== null && $package->quantity_available <= 10)
                                    <span class="badge bg-warning small">Limited</span>
                                @endif
                                @if(isset($package->meta_data['badge']))
                                    <span class="badge bg-info small">{{ $package->meta_data['badge'] }}</span>
                                @endif
                            </div>

                            <p class="card-text text-muted small mb-2" style="line-height: 1.3;">{{ Str::limit($package->short_description, 80) }}</p>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <div class="text-center p-1 bg-light rounded">
                                        <div class="fw-bold text-primary mb-0">{{ $package->formatted_price }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Price</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-1 bg-light rounded">
                                        <div class="fw-bold text-success mb-0">{{ number_format($package->points_awarded) }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Points</small>
                                    </div>
                                </div>
                            </div>

                            @if(isset($package->meta_data['features']))
                                <ul class="list-unstyled mb-2" style="font-size: 0.8rem;">
                                    @foreach(array_slice($package->meta_data['features'], 0, 2) as $feature)
                                        <li class="d-flex align-items-center mb-1">
                                            <svg class="icon text-success me-1 flex-shrink-0" style="width: 10px; height: 10px;">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                            </svg>
                                            <span style="line-height: 1.2;">{{ Str::limit($feature, 35) }}</span>
                                        </li>
                                    @endforeach
                                    @if(count($package->meta_data['features']) > 2)
                                        <li class="text-muted small">
                                            +{{ count($package->meta_data['features']) - 2 }} more
                                        </li>
                                    @endif
                                </ul>
                            @endif

                            <div class="mt-auto">
                                @if($package->quantity_available !== null)
                                    <div class="mb-2">
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            @if($package->quantity_available > 0)
                                                {{ $package->quantity_available }} left
                                            @else
                                                Out of stock
                                            @endif
                                        </small>
                                    </div>
                                @endif

                                <div class="d-grid gap-1">
                                    <a href="{{ route('packages.show', $package) }}" class="btn btn-outline-primary btn-sm">
                                        View Details
                                    </a>
                                    @if($package->isAvailable())
                                        @if(in_array($package->id, $cartPackageIds))
                                            <button class="btn btn-success btn-sm" disabled>
                                                <svg class="icon me-1" style="width: 14px; height: 14px;">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                                </svg>
                                                In Cart
                                            </button>
                                        @else
                                            <button class="btn btn-primary btn-sm add-to-cart-btn" data-package-id="{{ $package->id }}">
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

        <div class="row">
            <div class="col-12">
                {{ $packages->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <svg class="icon icon-xxl text-muted mb-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                    </svg>
                    <h5 class="text-muted">No packages found</h5>
                    <p class="text-muted">
                        @if(request('search'))
                            No packages match your search "{{ request('search') }}".
                            <a href="{{ route('packages.index') }}">View all packages</a>
                        @else
                            There are no packages available at the moment.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

<style>
.package-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.package-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.card-img-top-wrapper {
    position: relative;
}

.add-to-cart-btn {
    transition: all 0.2s ease-in-out;
}

.add-to-cart-btn:hover {
    transform: translateY(-1px);
}
</style>

@endsection