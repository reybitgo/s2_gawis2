@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- Welcome Header -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">Welcome back, {{ $user->username }}! 👋</h4>
                <p class="text-body-secondary mb-0">Here's what's happening with your wallet today.</p>
            </div>
            <div class="d-none d-md-block">
                @if($user->hasRole('admin'))
                    <span class="badge bg-purple-gradient text-white">Administrator</span>
                @elseif($user->hasRole('member'))
                    <span class="badge bg-info-gradient text-white">Member</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Current Rank Display -->
@if(!$user->hasRole('admin'))
<div class="card mb-4 border-primary shadow-sm">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <svg class="icon icon-3xl text-warning">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                        </svg>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Your Current Rank</h6>
                        @if($user->current_rank)
                            <h3 class="mb-1">
                                <span class="badge bg-gradient-success fs-5 px-3 py-2">
                                    <svg class="icon me-1">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                                    </svg>
                                    {{ $user->current_rank }}
                                </span>
                            </h3>
                            @if($user->rankPackage)
                                <p class="text-muted mb-0">
                                    <strong>{{ $user->rankPackage->name }}</strong> 
                                    <span class="text-body-secondary">(₱{{ number_format($user->rankPackage->price, 2) }})</span>
                                </p>
                                @if($user->rank_updated_at)
                                    <small class="text-body-secondary">
                                        <svg class="icon icon-sm">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                                        </svg>
                                        Achieved {{ $user->rank_updated_at->diffForHumans() }}
                                    </small>
                                @endif
                            @endif
                        @else
                            <h4 class="mb-1">
                                <span class="badge bg-secondary fs-6 px-3 py-2">No Rank Yet</span>
                            </h4>
                            <p class="text-muted mb-0">Purchase a rank package to get started and earn commissions</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                @if($user->rankPackage && $user->rankPackage->nextRankPackage)
                    <div class="mb-2">
                        <small class="text-muted d-block mb-1">Next Rank</small>
                        <span class="badge bg-info-gradient text-white">
                            {{ $user->rankPackage->nextRankPackage->rank_name }}
                        </span>
                    </div>
                    @if($user->rankPackage->required_direct_sponsors)
                        @php
                            $qualifiedSponsors = $user->referrals()
                                ->where('rank_package_id', $user->rank_package_id)
                                ->where('network_status', 'active')
                                ->count();
                            $required = $user->rankPackage->required_direct_sponsors;
                            $percentage = $required > 0 ? min(100, ($qualifiedSponsors / $required) * 100) : 0;
                        @endphp
                        <div class="mb-2">
                            <small class="text-muted d-block mb-1">
                                Qualified Sponsors: {{ $qualifiedSponsors }}/{{ $required }}
                            </small>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $qualifiedSponsors >= $required ? 'bg-success' : 'bg-primary' }}" 
                                     role="progressbar" 
                                     style="width: {{ $percentage }}%"
                                     aria-valuenow="{{ $qualifiedSponsors }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="{{ $required }}">
                                </div>
                            </div>
                        </div>
                        @if($qualifiedSponsors >= $required)
                            <span class="badge bg-success">
                                <svg class="icon icon-sm">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                </svg>
                                Ready to Advance!
                            </span>
                        @endif
                    @endif
                @elseif($user->current_rank)
                    <div class="text-center p-3 bg-light rounded">
                        <svg class="icon icon-xl text-warning mb-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trophy') }}"></use>
                        </svg>
                        <p class="mb-0 small text-muted"><strong>Top Rank!</strong><br>You've reached the highest rank</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Wallet Overview Cards -->
<div class="row g-3 mb-4">
    <!-- Current Balance -->
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ currency($wallet->total_balance) }}</div>
                    <div>Current Balance</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Deposits -->
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-info-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ currency($totalDeposits) }}</div>
                    <div>Total Deposits</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-bottom') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <!-- Total Withdrawals -->
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-warning-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ currency($totalWithdrawals) }}</div>
                    <div>Total Withdrawals</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-top') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <!-- Points Earned -->
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-purple-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ number_format($totalPointsEarned) }}</div>
                    <div>Points Earned</div>
                    @if($pendingPoints > 0)
                        <small class="opacity-75">{{ number_format($pendingPoints) }} pending</small>
                    @endif
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-star') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- MLM Balance Widget -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        @include('components.mlm-balance-widget')
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="cil-people"></i> Network Stats
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="text-muted small">Direct Referrals</label>
                        <h4 class="mb-0">{{ auth()->user()->referrals()->count() }}</h4>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="text-muted small">Total Network Earnings</label>
                        <h4 class="mb-0 text-success">{{ currency(auth()->user()->wallet->lifetime_earnings ?? 0) }}</h4>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('referral.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="cil-share"></i> My Referral Link
                    </a>
                    <a href="{{ route('member.register.show') }}" class="btn btn-sm btn-primary">
                        <i class="cil-user-plus"></i> Register Member
                    </a>
                </div>
                <hr>
                <div class="d-flex justify-content-around">
                    <a href="{{ route('member.unilevel.genealogy') }}" class="btn btn-sm btn-outline-info">
                        <i class="cil-sitemap"></i> Unilevel Genealogy
                    </a>
                    <a href="{{ route('member.mlm.genealogy') }}" class="btn btn-sm btn-outline-success">
                        <i class="cil-sitemap"></i> MLM Genealogy
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Quick Actions</h5>
        <div class="card-header-actions">
            <small class="text-body-secondary">Manage your wallet and transactions</small>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @if($user->hasRole('admin'))
                <!-- Admin Actions -->
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-purple w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-pie') }}"></use>
                        </svg>
                        Admin Dashboard
                    </a>
                </div>
                @can('wallet_management')
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('admin.wallet.management') }}" class="btn btn-info w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                        </svg>
                        Wallet Management
                    </a>
                </div>
                @endcan
                @can('transaction_approval')
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('admin.transaction.approval') }}" class="btn btn-warning w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-task') }}"></use>
                        </svg>
                        Transaction Approval
                    </a>
                </div>
                @endcan
                @can('system_settings')
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('admin.system.settings') }}" class="btn btn-secondary w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                        </svg>
                        System Settings
                    </a>
                </div>
                @endcan
            @else
                <!-- Member Actions -->
                @can('deposit_funds')
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('wallet.deposit') }}" class="btn btn-success w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-bottom') }}"></use>
                        </svg>
                        Deposit Funds
                    </a>
                </div>
                @endcan
                @can('transfer_funds')
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('wallet.transfer') }}" class="btn btn-info w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-right') }}"></use>
                        </svg>
                        Transfer Funds
                    </a>
                </div>
                @endcan
                @can('transfer_funds')
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('wallet.convert') }}" class="btn btn-purple w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-vertical') }}"></use>
                        </svg>
                        Convert Balance
                    </a>
                </div>
                @endcan
                @can('withdraw_funds')
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('wallet.withdraw') }}" class="btn btn-danger w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-top') }}"></use>
                        </svg>
                        Withdraw Funds
                    </a>
                </div>
                @endcan
                @can('view_transactions')
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-primary w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list-numbered') }}"></use>
                        </svg>
                        View Transactions
                    </a>
                </div>
                @endcan
                <div class="col-md-3 col-sm-6">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                        </svg>
                        Order History
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card mb-4">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list-numbered') }}"></use>
        </svg>
        <strong>Recent Transactions</strong>
        <small class="text-body-secondary ms-auto">Your latest wallet activity</small>
    </div>
    <div class="card-body p-0">
        @forelse($recentTransactions as $transaction)
            <div class="list-group-item d-flex justify-content-between align-items-center border-0 border-bottom">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3
                        @if($transaction->type === 'deposit' || $transaction->type === 'transfer_in') bg-success-gradient
                        @elseif($transaction->type === 'withdrawal' || $transaction->type === 'transfer_out') bg-danger-gradient
                        @elseif($transaction->type === 'transfer_charge') bg-warning-gradient
                        @else bg-info-gradient @endif">
                        <svg class="icon text-white">
                            @if($transaction->type === 'deposit' || $transaction->type === 'transfer_in')
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-bottom') }}"></use>
                            @elseif($transaction->type === 'withdrawal' || $transaction->type === 'transfer_out')
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-circle-top') }}"></use>
                            @elseif($transaction->type === 'transfer_charge')
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                            @else
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <div class="fw-semibold">
                            @if($transaction->type === 'transfer_out')
                                Transfer Sent
                            @elseif($transaction->type === 'transfer_in')
                                Transfer Received
                            @elseif($transaction->type === 'transfer_charge')
                                Transfer Fee
                            @else
                                {{ ucfirst($transaction->type) }}
                            @endif
                        </div>
                        <div class="small text-body-secondary">
                            <svg class="icon icon-xs me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                            </svg>
                            {{ $transaction->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-semibold
                        @if($transaction->type === 'deposit' || $transaction->type === 'transfer_in') text-success
                        @elseif($transaction->type === 'transfer_charge') text-warning
                        @else text-danger @endif">
                        @if($transaction->type === 'deposit' || $transaction->type === 'transfer_in')
                            +{{ currency($transaction->amount) }}
                        @else
                            -{{ currency($transaction->amount) }}
                        @endif
                    </div>
                    <span class="badge
                        @if($transaction->status == 'approved') bg-success-gradient
                        @elseif($transaction->status == 'rejected') bg-danger-gradient
                        @elseif($transaction->status == 'pending') bg-warning-gradient
                        @else bg-secondary-gradient @endif">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-4 text-center">
                <svg class="icon icon-3xl text-body-secondary mb-3">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                </svg>
                <h4 class="text-body-secondary">No transactions</h4>
                <p class="text-body-secondary">Get started by making your first deposit.</p>
                @can('deposit_funds')
                    <a href="{{ route('wallet.deposit') }}" class="btn btn-primary mt-2">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                        </svg>
                        Make Deposit
                    </a>
                @endcan
            </div>
        @endforelse
    </div>
    @if($recentTransactions->count() > 0)
        <div class="card-footer">
            <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-primary btn-sm">
                <svg class="icon me-1">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                View all transactions
            </a>
        </div>
    @endif
</div>

<!-- Account Information -->
<div class="card mb-4">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
        </svg>
        <strong>Account Information</strong>
        <small class="text-body-secondary ms-auto">Your account details and security status</small>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <div class="list-group-item d-flex align-items-center">
                <div class="avatar avatar-sm bg-primary-gradient me-3">
                    <svg class="icon text-white icon-xs">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-envelope-closed') }}"></use>
                    </svg>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Email Address</div>
                    <div class="small text-body-secondary">{{ $user->email }}</div>
                </div>
                @if($user->email_verified_at)
                    <span class="badge bg-success text-white rounded-pill">
                        <svg class="icon icon-xs me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                        </svg>
                        Verified
                    </span>
                @else
                    <span class="badge bg-danger text-white rounded-pill">
                        <svg class="icon icon-xs me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x-circle') }}"></use>
                        </svg>
                        Unverified
                    </span>
                @endif
            </div>

            <div class="list-group-item d-flex align-items-center">
                <div class="avatar avatar-sm bg-info-gradient me-3">
                    <svg class="icon text-white icon-xs">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                    </svg>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Account Security</div>
                    <div class="small text-body-secondary">Email verification status</div>
                </div>
                @if($user->email_verified_at)
                    <span class="badge bg-success text-white rounded-pill">
                        <svg class="icon icon-xs me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                        </svg>
                        Secured
                    </span>
                @else
                    <span class="badge bg-warning text-dark rounded-pill">
                        <svg class="icon icon-xs me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        Needs Attention
                    </span>
                @endif
            </div>

            <div class="list-group-item d-flex align-items-center">
                <div class="avatar avatar-sm bg-secondary-gradient me-3">
                    <svg class="icon text-white icon-xs">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-mobile') }}"></use>
                    </svg>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Two-Factor Authentication</div>
                    <div class="small text-body-secondary">Additional security protection</div>
                </div>
                @if($user->two_factor_secret)
                    <span class="badge bg-success text-white rounded-pill">
                        <svg class="icon icon-xs me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                        </svg>
                        Enabled
                    </span>
                @else
                    <span class="badge bg-warning text-dark rounded-pill">
                        <svg class="icon icon-xs me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-unlocked') }}"></use>
                        </svg>
                        Disabled
                    </span>
                @endif
            </div>

            <div class="list-group-item d-flex align-items-center">
                <div class="avatar avatar-sm bg-success-gradient me-3">
                    <svg class="icon text-white icon-xs">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                    </svg>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Wallet Status</div>
                    <div class="small text-body-secondary">Current wallet access level</div>
                </div>
                @if($wallet->is_active)
                    <span class="badge bg-success text-white rounded-pill">
                        <svg class="icon icon-xs me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                        </svg>
                        Active
                    </span>
                @else
                    <span class="badge bg-danger text-white rounded-pill">
                        <svg class="icon icon-xs me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-ban') }}"></use>
                        </svg>
                        Frozen
                    </span>
                @endif
            </div>

            <div class="list-group-item d-flex align-items-center">
                <div class="avatar avatar-sm bg-warning-gradient me-3">
                    <svg class="icon text-white icon-xs">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-line') }}"></use>
                    </svg>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Transaction Activity</div>
                    <div class="small text-body-secondary">Total completed transactions</div>
                </div>
                <span class="badge bg-info text-white rounded-pill">
                    {{ $totalTransactions }} Transactions
                </span>
            </div>

            <div class="list-group-item d-flex align-items-center">
                <div class="avatar avatar-sm bg-purple-gradient me-3">
                    <svg class="icon text-white icon-xs">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-calendar') }}"></use>
                    </svg>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">Member Since</div>
                    <div class="small text-body-secondary">Account creation date</div>
                </div>
                <span class="text-body-secondary fw-semibold">
                    {{ $user->created_at->format('M d, Y') }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Transaction Summary -->
@if($monthlyTransactions->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-line') }}"></use>
        </svg>
        <strong>Monthly Transaction Summary</strong>
        <small class="text-body-secondary ms-auto">Your transaction activity over the past 6 months</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Month</th>
                        <th scope="col">Deposits</th>
                        <th scope="col">Withdrawals</th>
                        <th scope="col">Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyTransactions as $monthly)
                        <tr>
                            <td class="fw-semibold">
                                {{ date('M Y', mktime(0, 0, 0, $monthly->month, 1, $monthly->year)) }}
                            </td>
                            <td class="text-success fw-semibold">
                                {{ currency($monthly->deposits) }}
                            </td>
                            <td class="text-danger fw-semibold">
                                {{ currency($monthly->withdrawals) }}
                            </td>
                            <td class="fw-semibold">
                                {{ $monthly->count }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

<style>
/* Gradient backgrounds */
.bg-success-gradient {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}

.bg-danger-gradient {
    background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
}

.bg-purple-gradient {
    background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
}

.bg-secondary-gradient {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

/* Fix mobile overflow */
@media (max-width: 575px) {
    .row {
        --cui-gutter-x: 0.75rem;
    }
}

/* Welcome header improvements */
.card.border-0.shadow-sm {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.card.border-0.shadow-sm .card-title {
    color: white !important;
}

.card.border-0.shadow-sm .text-body-secondary {
    color: rgba(255, 255, 255, 0.8) !important;
}

/* Icon sizing */
.icon-2xl {
    width: 2rem;
    height: 2rem;
}

.icon-xl {
    width: 1.5rem;
    height: 1.5rem;
}

.icon-sm {
    width: 0.875rem;
    height: 0.875rem;
}

/* Avatar sizing */
.avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 8px;
}

.avatar-md {
    width: 40px;
    height: 40px;
    border-radius: 10px;
}

.avatar-xl {
    width: 64px;
    height: 64px;
    border-radius: 16px;
}

/* List group item padding fix for avatar spacing */
.list-group-item {
    padding: 1rem 1.25rem;
}

/* Transaction item improvements */
.transaction-item {
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
}

.transaction-item:hover {
    background-color: rgba(0, 123, 255, 0.02);
}

.transaction-item:last-child {
    border-bottom: none !important;
}

/* Badge improvements */
.badge.rounded-pill {
    font-size: 10px;
    font-weight: 500;
    padding: 4px 8px;
}

/* Empty state */
.empty-state .avatar-xl {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

/* Card header improvements */
.card-header.bg-white {
    padding: 1.25rem 1.5rem;
}

/* Quick actions button improvements */
.btn-purple {
    background: linear-gradient(135deg, #6f42c1 0%, #e83e8c 100%);
    border-color: #6f42c1;
    color: white;
}

.btn-purple:hover {
    background: linear-gradient(135deg, #5a32a3 0%, #d91a72 100%);
    border-color: #5a32a3;
    color: white;
}
</style>
@endsection