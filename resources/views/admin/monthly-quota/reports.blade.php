@extends('layouts.admin')

@section('title', 'Monthly Quota Reports')

@section('content')
<div class="container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Monthly Quota Reports</h1>
        <a href="{{ route('admin.monthly-quota.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Month/Year Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.monthly-quota.reports') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="month" class="form-label">Month</label>
                    <select name="month" id="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="year" class="form-label">Year</label>
                    <select name="year" id="year" class="form-select">
                        @for($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Users</h6>
                    <h3>{{ number_format($summary['total_users']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Quota Met</h6>
                    <h3>{{ number_format($summary['quota_met']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Quota Not Met</h6>
                    <h3>{{ number_format($summary['quota_not_met']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Average PV</h6>
                    <h3>{{ number_format($summary['avg_pv'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card">
        <div class="card-header">
            <h5>Quota Status for {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Total PV</th>
                            <th>Required Quota</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Last Purchase</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trackers as $tracker)
                        <tr>
                            <td>{{ $tracker->user_id }}</td>
                            <td>
                                <a href="{{ route('admin.monthly-quota.reports.user', $tracker->user) }}">
                                    {{ $tracker->user->username ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ number_format($tracker->total_pv_points, 2) }}</td>
                            <td>{{ number_format($tracker->required_quota, 2) }}</td>
                            <td>
                                @php
                                    $progress = $tracker->required_quota > 0 
                                        ? min(100, ($tracker->total_pv_points / $tracker->required_quota) * 100)
                                        : 100;
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $tracker->quota_met ? 'bg-success' : 'bg-warning' }}" 
                                         role="progressbar" 
                                         style="width: {{ $progress }}%"
                                         aria-valuenow="{{ $progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($progress, 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($tracker->quota_met)
                                    <span class="badge bg-success">Met</span>
                                @else
                                    <span class="badge bg-warning">Not Met</span>
                                @endif
                            </td>
                            <td>
                                {{ $tracker->last_purchase_at ? $tracker->last_purchase_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>
                                <a href="{{ route('admin.monthly-quota.reports.user', $tracker->user) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No data available for this period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $trackers->appends(['year' => $year, 'month' => $month])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
