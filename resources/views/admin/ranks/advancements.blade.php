@extends('layouts.admin')

@section('title', 'Rank Advancements')
@section('page-title', 'Rank Advancement History')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-history') }}"></use>
                    </svg>
                    Rank Advancement History
                </h4>
                <p class="text-body-secondary mb-0">Track all rank changes and system rewards</p>
            </div>
            <div>
                <a href="{{ route('admin.ranks.index') }}" class="btn btn-secondary">
                    <svg class="icon me-1">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.ranks.advancements') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="type" class="form-label">Advancement Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="sponsorship_reward" {{ request('type') === 'sponsorship_reward' ? 'selected' : '' }}>
                            Sponsorship Reward
                        </option>
                        <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>
                            Direct Purchase
                        </option>
                        <option value="admin_adjustment" {{ request('type') === 'admin_adjustment' ? 'selected' : '' }}>
                            Admin Adjustment
                        </option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="rank" class="form-label">To Rank</label>
                    <select name="rank" id="rank" class="form-select">
                        <option value="">All Ranks</option>
                        @foreach($ranks as $rank)
                            <option value="{{ $rank }}" {{ request('rank') === $rank ? 'selected' : '' }}>
                                {{ $rank }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="search" class="form-label">Search User</label>
                    <input type="text" 
                           name="search" 
                           id="search" 
                           class="form-control" 
                           placeholder="Username or email..."
                           value="{{ request('search') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Show</label>
                    <select name="per_page" id="per_page" class="form-select" onchange="this.form.submit()">
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 per page</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 per page</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 per page</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20 per page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-filter') }}"></use>
                        </svg>
                        Filter
                    </button>
                </div>
            </div>
            
            @if(request()->hasAny(['type', 'rank', 'search']))
                <div class="mt-3">
                    <a href="{{ route('admin.ranks.advancements') }}" class="btn btn-secondary btn-sm">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                        </svg>
                        Clear Filters
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<!-- Advancements Table -->
<div class="card mb-5">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Advancement Records</h5>
            <span class="text-muted">
                Showing {{ $advancements->firstItem() ?? 0 }} to {{ $advancements->lastItem() ?? 0 }} of {{ $advancements->total() }} records
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>From Rank</th>
                        <th>To Rank</th>
                        <th>Type</th>
                        <th>Sponsors Count</th>
                        <th>System Paid</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advancements as $advancement)
                        <tr>
                            <td>
                                <small>{{ $advancement->created_at->format('M d, Y') }}</small>
                                <br>
                                <small class="text-muted">{{ $advancement->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <strong>{{ $advancement->user->username }}</strong>
                                <br>
                                <small class="text-muted">{{ $advancement->user->email }}</small>
                            </td>
                            <td>
                                @if($advancement->from_rank)
                                    <span class="badge bg-secondary">{{ $advancement->from_rank }}</span>
                                @else
                                    <span class="badge bg-light text-dark">None</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-success">{{ $advancement->to_rank }}</span>
                            </td>
                            <td>
                                @if($advancement->advancement_type === 'sponsorship_reward')
                                    <span class="badge bg-primary">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-gift') }}"></use>
                                        </svg>
                                        Reward
                                    </span>
                                @elseif($advancement->advancement_type === 'purchase')
                                    <span class="badge bg-info">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                                        </svg>
                                        Purchase
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                        </svg>
                                        Admin
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($advancement->sponsors_count)
                                    <span class="badge bg-info">
                                        {{ $advancement->sponsors_count }}/{{ $advancement->required_sponsors }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($advancement->system_paid_amount > 0)
                                    <strong class="text-success">₱{{ number_format($advancement->system_paid_amount, 2) }}</strong>
                                @else
                                    <span class="text-muted">₱0.00</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @if($advancement->order_id)
                                        <a href="{{ route('admin.orders.show', $advancement->order_id) }}" 
                                           class="btn btn-outline-primary"
                                           title="View Order">
                                            <svg class="icon">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-description') }}"></use>
                                            </svg>
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('admin.users.edit', $advancement->user_id) }}" 
                                       class="btn btn-outline-secondary"
                                       title="View User">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                        </svg>
                                    </a>
                                    
                                    @if($advancement->notes)
                                        <button type="button" 
                                                class="btn btn-outline-info"
                                                data-coreui-toggle="tooltip"
                                                title="{{ $advancement->notes }}">
                                            <svg class="icon">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-notes') }}"></use>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <svg class="icon icon-xl mb-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                </svg>
                                <p class="mb-0">No rank advancements found</p>
                                @if(request()->hasAny(['type', 'rank', 'search']))
                                    <small>Try adjusting your filters</small>
                                @else
                                    <small>Advancements will appear here when users rank up</small>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($advancements->hasPages())
        <div class="card-footer">
            {{ $advancements->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-coreui-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new coreui.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
