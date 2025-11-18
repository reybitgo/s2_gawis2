@extends('layouts.admin')

@section('title', 'Monthly Quota System')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Monthly Quota System</h1>
        <div>
            <a href="{{ route('admin.products.index') }}" class="btn btn-primary">
                <i class="fas fa-box"></i> Manage Product PV
            </a>
            <a href="{{ route('admin.monthly-quota.packages') }}" class="btn btn-info">
                <i class="fas fa-gift"></i> Manage Package Quotas
            </a>
            <a href="{{ route('admin.monthly-quota.reports') }}" class="btn btn-success">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Active Users</h5>
                    <h2>{{ number_format($stats['total_active_users']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Quota Met This Month</h5>
                    <h2>{{ number_format($stats['users_met_quota']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Quota Not Met</h5>
                    <h2>{{ number_format($stats['users_not_met_quota']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Compliance Rate</h5>
                    <h2>{{ $stats['quota_compliance_rate'] }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Summary -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Configuration Status</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Products with PV
                            <span class="badge bg-primary rounded-pill">{{ $stats['total_products_with_pv'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Packages with Quota
                            <span class="badge bg-info rounded-pill">{{ $stats['total_packages_with_quota'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Top Performers ({{ now()->format('F Y') }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Total PV</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTrackers as $tracker)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.monthly-quota.reports.user', $tracker->user) }}">
                                            {{ $tracker->user->username ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>{{ number_format($tracker->total_pv_points, 2) }}</td>
                                    <td>
                                        @if($tracker->quota_met)
                                            <span class="badge bg-success">Met</span>
                                        @else
                                            <span class="badge bg-warning">Not Met</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="card">
        <div class="card-header">
            <h5>How Monthly Quota System Works</h5>
        </div>
        <div class="card-body">
            <ol>
                <li><strong>Assign PV to Products:</strong> Each product is assigned a Point Value (PV) at <a href="{{ route('admin.products.index') }}">Products Management</a>.</li>
                <li><strong>Set Package Quotas:</strong> Each starter package defines a monthly PV quota requirement at <a href="{{ route('admin.monthly-quota.packages') }}">Package Quotas</a>.</li>
                <li><strong>Track User Purchases:</strong> System tracks PV accumulated by users each month automatically.</li>
                <li><strong>Validate Qualification:</strong> Users earn Unilevel bonuses only if they're active AND have met their monthly quota.</li>
                <li><strong>Monthly Reset:</strong> PV tracking resets at the beginning of each month (automated via CRON job).</li>
            </ol>
            <div class="alert alert-info mt-3">
                <strong>Note:</strong> Product PV values can be managed through the existing Products page. 
                Use the "Points Awarded" field when editing products to set their PV values (supports decimals: 0.01 to 9999.99).
            </div>
        </div>
    </div>
</div>
@endsection
