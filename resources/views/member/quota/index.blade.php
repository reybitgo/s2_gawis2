@extends('layouts.admin')

@section('title', 'My Monthly Quota')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-line') }}"></use>
                    </svg>
                    My Monthly Quota
                </h4>
                <p class="text-body-secondary mb-0">Track your monthly PV progress and qualification status</p>
            </div>
            <div>
                <a href="{{ route('member.quota.history') }}" class="btn btn-outline-primary me-2">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-history') }}"></use>
                    </svg>
                    View History
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Current Month Status -->
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <strong>{{ $status['month_name'] }} {{ $status['year'] }}</strong> - Current Month Progress
            </div>
            <div class="card-body">
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">PV Progress</h5>
                        <span class="badge {{ $status['quota_met'] ? 'bg-success' : 'bg-warning' }} text-white fs-6">
                            {{ number_format($status['progress_percentage'], 1) }}%
                        </span>
                    </div>
                    
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar {{ $status['quota_met'] ? 'bg-success' : 'bg-primary' }}" 
                             role="progressbar" 
                             style="width: {{ min(100, $status['progress_percentage']) }}%"
                             aria-valuenow="{{ $status['total_pv'] }}" 
                             aria-valuemin="0" 
                             aria-valuemax="{{ $status['required_quota'] }}">
                            <strong>{{ number_format($status['total_pv'], 2) }} / {{ number_format($status['required_quota'], 2) }} PV</strong>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-body-secondary small">PV Earned</div>
                            <h4 class="text-primary mb-0">{{ number_format($status['total_pv'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-body-secondary small">Required</div>
                            <h4 class="text-info mb-0">{{ number_format($status['required_quota'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-body-secondary small">Remaining</div>
                            <h4 class="text-{{ $status['remaining_pv'] > 0 ? 'warning' : 'success' }} mb-0">
                                {{ number_format($status['remaining_pv'], 2) }}
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <div class="text-body-secondary small">Days Left</div>
                            <h4 class="mb-0">{{ $daysRemaining }}</h4>
                        </div>
                    </div>
                </div>

                <!-- Qualification Status -->
                <div class="alert {{ $status['quota_met'] ? 'alert-success' : 'alert-warning' }} d-flex align-items-center">
                    @if($status['quota_met'])
                        <svg class="icon icon-xl me-3">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                        </svg>
                        <div>
                            <h5 class="alert-heading mb-1">✅ QUALIFIED</h5>
                            <p class="mb-0">Congratulations! You have met your monthly quota and are qualified to receive Unilevel bonuses.</p>
                        </div>
                    @else
                        <svg class="icon icon-xl me-3">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        <div>
                            <h5 class="alert-heading mb-1">⚠️ NOT QUALIFIED</h5>
                            <p class="mb-0">You need <strong>{{ number_format($status['remaining_pv'], 2) }} more PV</strong> to qualify for Unilevel bonuses this month.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-warning mt-2">
                                Shop Products to Earn PV
                            </a>
                        </div>
                    @endif
                </div>

                @if($status['last_purchase_at'])
                    <div class="text-body-secondary small">
                        <strong>Last Purchase:</strong> {{ $status['last_purchase_at']->format('M d, Y h:i A') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent PV-Earning Orders -->
        @if($recentOrders->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <strong>Recent PV-Earning Orders</strong> ({{ $status['month_name'] }} {{ $status['year'] }})
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Products</th>
                                <th class="text-end">PV Earned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('orders.show', $order['order_number']) }}" class="text-decoration-none">
                                        {{ $order['order_number'] }}
                                    </a>
                                </td>
                                <td>{{ $order['date']->format('M d, Y') }}</td>
                                <td>
                                    <small class="text-body-secondary">
                                        @foreach($order['products'] as $product)
                                            {{ $product['name'] }} ({{ $product['quantity'] }}x)
                                            @if(!$loop->last), @endif
                                        @endforeach
                                    </small>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success">+{{ number_format($order['pv_earned'], 2) }} PV</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- How it Works -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <strong>How Monthly Quota Works</strong>
            </div>
            <div class="card-body">
                <ol class="ps-3 mb-0">
                    <li class="mb-2">Purchase products each month to earn PV (Personal Volume) points</li>
                    <li class="mb-2">Each product has a specific PV value</li>
                    <li class="mb-2">Accumulate enough PV to meet your monthly quota</li>
                    <li class="mb-2">Once met, you qualify to receive Unilevel bonuses from your downline's purchases</li>
                    <li class="mb-0">Quota resets on the 1st of each month</li>
                </ol>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <strong>Quick Actions</strong>
            </div>
            <div class="card-body">
                <a href="{{ route('products.index') }}" class="btn btn-primary w-100 mb-2">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                    </svg>
                    Shop Products
                </a>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-primary w-100 mb-2">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                    </svg>
                    View All Orders
                </a>
                <a href="{{ route('member.quota.history') }}" class="btn btn-outline-secondary w-100">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-history') }}"></use>
                    </svg>
                    View History
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
