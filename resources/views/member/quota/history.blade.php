@extends('layouts.admin')

@section('title', 'Quota History')

@section('content')
<div class="pb-4">
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-history') }}"></use>
                    </svg>
                    Quota History
                </h4>
                <p class="text-body-secondary mb-0">View your monthly quota performance over time</p>
            </div>
            <div>
                <a href="{{ route('member.quota.index') }}" class="btn btn-primary me-2">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-line') }}"></use>
                    </svg>
                    Current Month
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

<!-- History Table -->
<div class="card">
    <div class="card-header">
        <strong>Last 12 Months Performance</strong>
    </div>
    <div class="card-body p-0">
        @if($history->isEmpty())
            <div class="p-5 text-center text-body-secondary">
                <svg class="icon icon-xxl mb-3">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                </svg>
                <p class="mb-0">No quota history available yet.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Year</th>
                            <th>PV Earned</th>
                            <th>Required Quota</th>
                            <th>Progress</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $record)
                        <tr>
                            <td>
                                <strong>{{ $record['month_name'] }}</strong>
                            </td>
                            <td>{{ $record['year'] }}</td>
                            <td>
                                <span class="text-primary">
                                    {{ number_format($record['total_pv'], 2) }} PV
                                </span>
                            </td>
                            <td>
                                <span class="text-info">
                                    {{ number_format($record['required_quota'], 2) }} PV
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 20px; min-width: 150px;">
                                        <div class="progress-bar {{ $record['quota_met'] ? 'bg-success' : 'bg-warning' }}" 
                                             role="progressbar" 
                                             style="width: {{ min(100, $record['progress_percentage']) }}%"
                                             aria-valuenow="{{ $record['progress_percentage'] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="badge {{ $record['quota_met'] ? 'bg-success' : 'bg-warning' }} text-white">
                                        {{ number_format($record['progress_percentage'], 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($record['quota_met'])
                                    <span class="badge bg-success">
                                        <svg class="icon me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                                        </svg>
                                        MET
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <svg class="icon me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x-circle') }}"></use>
                                        </svg>
                                        NOT MET
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Statistics -->
            <div class="card-footer">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border-end">
                            <div class="text-body-secondary small">Total Months</div>
                            <h5 class="mb-0">{{ $history->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-end">
                            <div class="text-body-secondary small">Months Qualified</div>
                            <h5 class="text-success mb-0">{{ $history->where('quota_met', true)->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div>
                            <div class="text-body-secondary small">Success Rate</div>
                            <h5 class="text-primary mb-0">
                                {{ $history->count() > 0 ? number_format(($history->where('quota_met', true)->count() / $history->count()) * 100, 1) : 0 }}%
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Info Card -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lightbulb') }}"></use>
                    </svg>
                    Understanding Your History
                </h5>
                <ul class="mb-0">
                    <li>Each month's performance is tracked separately</li>
                    <li>Green "MET" badge means you qualified for Unilevel bonuses that month</li>
                    <li>Red "NOT MET" badge means you did not qualify that month</li>
                    <li>Your success rate shows your consistency over time</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                    </svg>
                    Tips for Success
                </h5>
                <ul class="mb-0">
                    <li>Plan your purchases early in the month</li>
                    <li>Set reminders for the 25th to check your progress</li>
                    <li>Subscribe to products you use regularly</li>
                    <li>Consistent monthly qualification maximizes your Unilevel earnings</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
