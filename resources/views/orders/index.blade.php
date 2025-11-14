@extends('layouts.admin')

@section('title', 'Order History')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h2 mb-2">Order History</h1>
            <p class="text-muted mb-0">Track and manage all your orders</p>
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

    <!-- Statistics Cards -->
    <div class="row mb-4 g-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-semibold">{{ $stats['total_orders'] }}</div>
                            <div>Total Orders</div>
                        </div>
                        <svg class="icon icon-3xl">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list-numbered') }}"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-semibold">{{ $stats['paid_orders'] }}</div>
                            <div>Paid Orders</div>
                        </div>
                        <svg class="icon icon-3xl">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-semibold">{{ currency($stats['total_spent']) }}</div>
                            <div>Total Spent</div>
                        </div>
                        <svg class="icon icon-3xl">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-semibold">{{ number_format($stats['total_points_earned']) }}</div>
                            <div>Points Earned</div>
                        </div>
                        <svg class="icon icon-3xl">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-filter') }}"></use>
                </svg>
                Filter Orders
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('orders.index') }}" id="filter-form">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Order number or notes...">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div class="col-md-2">
                        <label for="payment_status" class="form-label">Payment</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">All Payments</option>
                            <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from"
                               value="{{ request('date_from') }}">
                    </div>

                    <!-- Date To -->
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to"
                               value="{{ request('date_to') }}">
                    </div>

                    <!-- Filter Buttons -->
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <svg class="icon">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                </svg>
                            </button>
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm">
                                <svg class="icon">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                    </svg>
                    Your Orders ({{ $orders->total() }} total)
                </h5>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-2">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-coreui-toggle="dropdown">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-sort-ascending') }}"></use>
                            </svg>
                            Sort
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => 'desc']) }}">Newest First</a></li>
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_direction' => 'asc']) }}">Oldest First</a></li>
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'total_amount', 'sort_direction' => 'desc']) }}">Highest Amount</a></li>
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'total_amount', 'sort_direction' => 'asc']) }}">Lowest Amount</a></li>
                            <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_direction' => 'asc']) }}">Status A-Z</a></li>
                        </ul>
                    </div>
                    <x-per-page-selector :perPage="$perPage" />
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($orders->count() > 0)
                @include('orders.partials.order-list', ['orders' => $orders])
            @else
                <div class="text-center py-5">
                    <svg class="icon icon-4xl text-muted mb-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                    </svg>
                    <h4 class="text-muted">No Orders Found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to']))
                            No orders match your current filters.
                        @else
                            You haven't placed any orders yet.
                        @endif
                    </p>
                    <a href="{{ route('packages.index') }}" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                        </svg>
                        Start Shopping
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
                </div>
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on filter change (optional)
    const filterForm = document.getElementById('filter-form');
    const autoSubmitElements = ['status', 'payment_status'];

    autoSubmitElements.forEach(function(id) {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                filterForm.submit();
            });
        }
    });

    // Date validation
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            dateTo.min = this.value;
        });

        dateTo.addEventListener('change', function() {
            dateFrom.max = this.value;
        });
    }
});
</script>
@endpush
@endsection