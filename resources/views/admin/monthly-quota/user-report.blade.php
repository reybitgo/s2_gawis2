@extends('layouts.admin')

@section('title', 'User Quota Report: ' . $user->username)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>User Quota Report: {{ $user->username }}</h1>
        <a href="{{ route('admin.monthly-quota.reports') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>

    <!-- User Info Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>User Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>User ID:</strong> {{ $user->id }}</p>
                    <p><strong>Username:</strong> {{ $user->username }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Network Status:</strong> 
                        <span class="badge bg-{{ $user->network_status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($user->network_status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Package:</strong> {{ $package ? $package->name : 'N/A' }}</p>
                    <p><strong>Monthly Quota Required:</strong> {{ $package ? number_format($package->monthly_quota_points, 2) . ' PV' : 'N/A' }}</p>
                    <p><strong>Quota Enforced:</strong> 
                        @if($package && $package->enforce_monthly_quota)
                            <span class="badge bg-warning">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </p>
                    <p><strong>Qualifies for Bonus:</strong> 
                        @if($user->qualifiesForUnilevelBonus())
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-danger">No</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Month Status -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Current Month Status - {{ $currentStatus['month_name'] }} {{ $currentStatus['year'] }}</h5>
        </div>
        <div class="card-body">
            <div class="row text-center mb-3">
                <div class="col-md-3">
                    <h3 class="text-primary">{{ number_format($currentStatus['total_pv'], 2) }}</h3>
                    <p class="text-muted">PV Earned</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-info">{{ number_format($currentStatus['required_quota'], 2) }}</h3>
                    <p class="text-muted">Required Quota</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-{{ $currentStatus['quota_met'] ? 'success' : 'warning' }}">
                        {{ number_format($currentStatus['remaining_pv'], 2) }}
                    </h3>
                    <p class="text-muted">PV Remaining</p>
                </div>
                <div class="col-md-3">
                    <h3 class="text-{{ $currentStatus['quota_met'] ? 'success' : 'warning' }}">
                        {{ number_format($currentStatus['progress_percentage'], 1) }}%
                    </h3>
                    <p class="text-muted">Progress</p>
                </div>
            </div>

            <div class="mb-3">
                <div class="progress" style="height: 30px;">
                    <div class="progress-bar {{ $currentStatus['quota_met'] ? 'bg-success' : 'bg-warning' }}" 
                         role="progressbar" 
                         style="width: {{ min(100, $currentStatus['progress_percentage']) }}%"
                         aria-valuenow="{{ $currentStatus['progress_percentage'] }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        {{ number_format($currentStatus['progress_percentage'], 1) }}%
                    </div>
                </div>
            </div>

            @if($currentStatus['quota_met'])
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <strong>Quota Met!</strong> 
                This user has met their monthly quota and qualifies for Unilevel bonuses.
            </div>
            @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <strong>Quota Not Met</strong> 
                User needs {{ number_format($currentStatus['remaining_pv'], 2) }} more PV to qualify for Unilevel bonuses this month.
            </div>
            @endif

            @if($currentStatus['last_purchase_at'])
            <p class="text-muted">Last purchase: {{ \Carbon\Carbon::parse($currentStatus['last_purchase_at'])->format('F d, Y g:i A') }}</p>
            @endif
        </div>
    </div>

    <!-- Quota History -->
    <div class="card">
        <div class="card-header">
            <h5>Quota History (Last 12 Months)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Total PV</th>
                            <th>Required Quota</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $record)
                        <tr>
                            <td>{{ $record['month_name'] }}</td>
                            <td>{{ $record['year'] }}</td>
                            <td>{{ number_format($record['total_pv'], 2) }}</td>
                            <td>{{ number_format($record['required_quota'], 2) }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $record['quota_met'] ? 'bg-success' : 'bg-warning' }}" 
                                         role="progressbar" 
                                         style="width: {{ $record['progress_percentage'] }}%"
                                         aria-valuenow="{{ $record['progress_percentage'] }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($record['progress_percentage'], 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($record['quota_met'])
                                    <span class="badge bg-success">Met</span>
                                @else
                                    <span class="badge bg-warning">Not Met</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No history available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
