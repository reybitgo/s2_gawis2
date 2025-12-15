@extends('layouts.admin')

@section('title', 'Rank System Management')
@section('page-title', 'Rank System Management')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                    </svg>
                    Rank System Management
                </h4>
                <p class="text-body-secondary mb-0">Monitor and configure the rank advancement system</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.ranks.configure') }}" class="btn btn-primary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                    </svg>
                    <span class="d-none d-sm-inline">Configure Ranks</span>
                    <span class="d-inline d-sm-none">Configure</span>
                </a>
                <a href="{{ route('admin.ranks.advancements') }}" class="btn btn-info">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-history') }}"></use>
                    </svg>
                    <span class="d-none d-sm-inline">View Advancements</span>
                    <span class="d-inline d-sm-none">Advancements</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-primary-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ number_format($stats['total_ranked_users']) }}</div>
                    <div>Ranked Users</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-people') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ number_format($stats['total_advancements']) }}</div>
                    <div>Total Advancements</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-top') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-info-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ number_format($stats['system_rewards_count']) }}</div>
                    <div>System Rewards</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-gift') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-warning-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">₱{{ number_format($stats['total_system_paid'], 2) }}</div>
                    <div>Total System Paid</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Rank Packages Overview -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Rank Packages Overview</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Rank Order</th>
                        <th>Rank Name</th>
                        <th>Package</th>
                        <th>Price</th>
                        <th>Required Sponsors</th>
                        <th>Next Rank</th>
                        <th>User Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $package->rank_order }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $package->rank_name }}</span>
                            </td>
                            <td>{{ $package->name }}</td>
                            <td>₱{{ number_format($package->price, 2) }}</td>
                            <td>
                                <span class="badge bg-primary">{{ $package->required_direct_sponsors }}</span>
                            </td>
                            <td>
                                @if($package->nextRankPackage)
                                    <span class="badge bg-success">{{ $package->nextRankPackage->rank_name }}</span>
                                @else
                                    <span class="badge bg-dark">Top Rank</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $rankDistribution[$package->rank_name]->count ?? 0 }}</strong> users
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No rank packages configured</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Rank Distribution Chart -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Rank Distribution</h5>
    </div>
    <div class="card-body">
        @if($rankDistribution->count() > 0)
            <canvas id="rankDistributionChart" style="height: 300px;"></canvas>
        @else
            <div class="alert alert-info mb-0">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                </svg>
                No rank distribution data available yet. Users will appear here once they have been assigned ranks.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($rankDistribution->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('rankDistributionChart');
    
    const labels = @json($rankDistribution->keys());
    const data = @json($rankDistribution->pluck('count'));
    
    // Generate colors based on number of ranks
    const colors = [
        'rgba(54, 162, 235, 0.6)',
        'rgba(75, 192, 192, 0.6)',
        'rgba(255, 206, 86, 0.6)',
        'rgba(153, 102, 255, 0.6)',
        'rgba(255, 99, 132, 0.6)',
        'rgba(255, 159, 64, 0.6)',
    ];
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'User Count',
                data: data,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: colors.slice(0, labels.length).map(c => c.replace('0.6', '1')),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' users';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endpush

@push('styles')
<style>
/* Mobile responsiveness improvements */
@media (max-width: 767.98px) {
    .card-header {
        padding: 1rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .card-header h4, .card-header h5 {
        font-size: 1.1rem;
    }
    
    /* Button improvements */
    .btn {
        font-size: 0.875rem;
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
}

@media (max-width: 575.98px) {
    /* Extra small screens - show shorter button text */
    .btn svg.icon {
        margin-right: 0.25rem !important;
    }
}

/* Prevent card header from overflowing */
.card-header {
    overflow: hidden;
}

.card-header > div {
    min-width: 0;
}

/* Gradient backgrounds for stats cards */
.bg-primary-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.bg-success-gradient {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #0dcaf0 0%, #0baccc 100%);
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
}
</style>
@endpush
