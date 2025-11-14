@extends('layouts.admin')

@section('title', 'Wallet Management')
@section('page-title', 'Wallet Management')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                    </svg>
                    Wallet Management
                </h4>
                <p class="text-body-secondary mb-0">Monitor and manage user e-wallets</p>
            </div>
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Wallet Overview Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ currency($totalBalance) }}</div>
                    <div>Total Wallet Balance</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-primary-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $todayDeposits ?? 0 }}</div>
                    <div>Today's Deposits</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-danger-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ currency($todayWithdrawals ?? 0) }}</div>
                    <div>Today's Withdrawals</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-warning-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $pendingTransactions ?? 0 }}</div>
                    <div>Pending Transactions</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Additional Transaction Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $approvedTransactions ?? 0 }}</div>
                    <div>Approved Transactions</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card text-white bg-danger-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $rejectedTransactions ?? 0 }}</div>
                    <div>Rejected Transactions</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-4">
        <div class="card text-white bg-info-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ ($pendingTransactions + $approvedTransactions + $rejectedTransactions) ?? 0 }}</div>
                    <div>Total Transactions</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- User Wallets Table -->
<div class="card mb-4">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-people') }}"></use>
        </svg>
        <strong>User Wallets</strong>
        <small class="text-body-secondary ms-auto">Overview of all member wallet balances and activity.</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Balance</th>
                        <th scope="col">Last Transaction</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-3 {{ $user->wallet && $user->wallet->is_active ? 'bg-primary' : 'bg-danger' }}">
                                        <span class="text-white">{{ strtoupper(substr($user->fullname ?? $user->username, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $user->fullname ?? $user->username }}</div>
                                        <div class="text-body-secondary">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ currency($user->wallet ? $user->wallet->total_balance : 0) }}</div>
                                @if($user->wallet && isset($user->wallet->last_transaction_at))
                                    <div class="text-body-secondary">Last activity: {{ $user->wallet->last_transaction_at->diffForHumans() }}</div>
                                @else
                                    <div class="text-body-secondary">No activity</div>
                                @endif
                            </td>
                            <td>
                                @if(isset($user->transactions) && $user->transactions->isNotEmpty())
                                    @php $lastTransaction = $user->transactions->first(); @endphp
                                    <div class="fw-semibold">
                                        {{ ucfirst($lastTransaction->type) }}:
                                        {{ $lastTransaction->type === 'deposit' ? '+' : '-' }}{{ currency($lastTransaction->amount) }}
                                    </div>
                                    <div class="text-body-secondary">{{ $lastTransaction->created_at->diffForHumans() }}</div>
                                @else
                                    <div class="text-body-secondary">No transactions</div>
                                @endif
                            </td>
                            <td>
                                @if($user->wallet)
                                    <span class="badge {{ $user->wallet->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->wallet->is_active ? 'Active' : 'Frozen' }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">No Wallet</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-body-secondary py-4">
                                No wallets found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(isset($wallets) && $wallets->hasPages())
        <div class="card-footer">
            {{ $wallets->links('vendor.pagination.coreui') }}
        </div>
    @endif
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
        </svg>
        <strong>Recent Transactions</strong>
        <small class="text-body-secondary ms-auto">Latest wallet transactions across all users.</small>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($recentTransactions ?? [] as $transaction)
                <div class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center">
                        <div class="avatar me-3 {{ $transaction->type === 'deposit' ? 'bg-success' : ($transaction->type === 'withdrawal' ? 'bg-danger' : 'bg-primary') }}">
                            <svg class="icon text-white">
                                @if($transaction->type === 'deposit')
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                @elseif($transaction->type === 'withdrawal')
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                                @else
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                                @endif
                            </svg>
                        </div>
                        <div>
                            <div class="fw-semibold">
                                {{ ucfirst($transaction->type) }} by {{ $transaction->user->fullname ?? $transaction->user->username }}
                            </div>
                            <div class="text-body-secondary">
                                {{ ucfirst($transaction->payment_method ?? 'N/A') }} • {{ $transaction->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold {{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                            {{ $transaction->type === 'deposit' ? '+' : '-' }}{{ currency($transaction->amount) }}
                        </div>
                        <span class="badge
                            @if($transaction->status == 'approved') bg-success
                            @elseif($transaction->status == 'rejected') bg-danger
                            @elseif($transaction->status == 'pending') bg-warning
                            @else bg-secondary @endif">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center text-body-secondary py-4">
                    No recent transactions
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- All Transactions Section -->
<div class="card mt-4">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                <strong>All Transactions</strong>
                <small class="text-body-secondary ms-2">Complete transaction history with filtering options</small>
            </div>
            <x-per-page-selector :perPage="$perPage" />
        </div>
    </div>

    <!-- Filters -->
    <div class="card-body border-bottom">
        <form method="GET" action="{{ route('admin.wallet.management') }}" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Status Filter</label>
                <select id="status" name="status" class="form-select">
                    <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ $statusFilter == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $statusFilter == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ $statusFilter == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="type" class="form-label">Type Filter</label>
                <select id="type" name="type" class="form-select">
                    <option value="all" {{ $typeFilter == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="deposit" {{ $typeFilter == 'deposit' ? 'selected' : '' }}>Deposits</option>
                    <option value="withdrawal" {{ $typeFilter == 'withdrawal' ? 'selected' : '' }}>Withdrawals</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-filter') }}"></use>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.wallet.management') }}" class="btn btn-secondary">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                        </svg>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="card-body p-0">
        @if($allTransactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allTransactions as $transaction)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar me-3 {{ $transaction->type === 'deposit' ? 'bg-success' : ($transaction->type === 'withdrawal' ? 'bg-danger' : 'bg-primary') }}">
                                            <svg class="icon text-white">
                                                @if($transaction->type === 'deposit')
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                                                @elseif($transaction->type === 'withdrawal')
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                                                @else
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                                                @endif
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ ucfirst($transaction->type) }} Request</div>
                                            <div class="text-body-secondary small">
                                                Ref: {{ $transaction->reference_number ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $transaction->user->fullname ?? $transaction->user->username }}</div>
                                    <div class="text-body-secondary">{{ $transaction->user->email }}</div>
                                    @if($transaction->user->wallet)
                                        <div class="text-body-secondary small">Balance: {{ currency($transaction->user->wallet->total_balance) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold {{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                        {{ $transaction->type === 'withdrawal' ? '-' : '+' }}{{ currency($transaction->amount) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ ucfirst($transaction->payment_method ?? 'N/A') }}</div>
                                </td>
                                <td>
                                    <span class="badge
                                        @if($transaction->status == 'approved') bg-success
                                        @elseif($transaction->status == 'rejected') bg-danger
                                        @elseif($transaction->status == 'pending') bg-warning
                                        @else bg-secondary @endif">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $transaction->created_at->format('M d, Y') }}</div>
                                    <div class="text-body-secondary">{{ $transaction->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button onclick="viewTransactionDetails({{ $transaction->id }})" class="btn btn-sm btn-outline-secondary" title="View Details">
                                            <svg class="icon">
                                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-search') }}"></use>
                                            </svg>
                                        </button>
                                        @if($transaction->status === 'pending')
                                            <a href="{{ route('admin.transaction.approval') }}" class="btn btn-sm btn-outline-primary" title="Go to Transaction Approval">
                                                <svg class="icon">
                                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-external-link') }}"></use>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $allTransactions->firstItem() ?? 0 }} to {{ $allTransactions->lastItem() ?? 0 }}
                        of {{ $allTransactions->total() }} transactions
                    </div>
                    {{ $allTransactions->links('vendor.pagination.coreui') }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <svg class="icon icon-3xl text-body-secondary mb-3">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-inbox') }}"></use>
                </svg>
                <h5 class="text-body-secondary">No transactions found</h5>
                <p class="text-body-secondary mb-0">
                    @if($statusFilter !== 'all' || $typeFilter !== 'all')
                        Try adjusting your filters to see more results.
                    @else
                        No transactions have been created yet.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<style>
/* Enhanced styling for wallet management page */
.table-responsive {
    border-radius: 8px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.card {
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 12px 12px 0 0 !important;
}

.badge {
    font-size: 11px;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 6px;
}

.btn-group .btn {
    border-radius: 6px !important;
    margin: 0 1px;
}

.form-select, .form-control {
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.form-select:focus, .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
}

.list-group-item {
    border: none;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1rem 1.25rem;
}

.list-group-item:last-child {
    border-bottom: none;
}

.list-group-item:hover {
    background-color: rgba(0, 123, 255, 0.02);
}

/* Pagination improvements */
.card-footer {
    background-color: #f8f9fa;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 0 0 12px 12px !important;
}

/* Transaction amount styling */
.text-success {
    color: #198754 !important;
}

.text-danger {
    color: #dc3545 !important;
}

/* Stats cards gradient enhancements */
.bg-primary-gradient {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.bg-success-gradient {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
}

.bg-danger-gradient {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
}

.bg-warning-gradient {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #0dcaf0 0%, #0baccc 100%);
}

/* Action buttons styling */
.btn-outline-primary:hover, .btn-outline-success:hover, .btn-outline-warning:hover, .btn-outline-danger:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-sm {
    border-radius: 6px;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
}

/* Ensure proper spacing in action button groups */
.btn-group .btn-sm {
    padding: 0.375rem 0.5rem;
    margin-right: 0.25rem;
}

.btn-group .btn-sm:last-child {
    margin-right: 0;
}

.btn-group .btn-sm svg.icon {
    width: 14px;
    height: 14px;
}

/* Add gap between buttons in action column */
td .btn-group {
    gap: 0.25rem;
    display: flex;
}

/* Filter section styling */
.card-body.border-bottom {
    background-color: #fafafa;
    border-radius: 0;
}

/* Empty state styling */
.text-center.py-5 {
    padding: 3rem 1rem !important;
}

/* Table responsive improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 14px;
    }

    .btn-group .btn {
        font-size: 12px;
        padding: 0.25rem 0.5rem;
    }

    .avatar {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }
}
</style>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="transactionDetailsModalLabel">
          <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
          </svg>
          Transaction Details
        </h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-muted mb-3">Transaction Information</h6>
            <table class="table table-borderless table-sm">
              <tr>
                <td class="text-muted">Transaction ID:</td>
                <td class="fw-semibold" id="detail-transaction-id">-</td>
              </tr>
              <tr>
                <td class="text-muted">Reference Number:</td>
                <td class="fw-semibold" id="detail-reference-number">-</td>
              </tr>
              <tr>
                <td class="text-muted">Type:</td>
                <td>
                  <span id="detail-type-badge" class="badge">-</span>
                </td>
              </tr>
              <tr>
                <td class="text-muted">Amount:</td>
                <td class="fw-semibold" id="detail-amount">-</td>
              </tr>
              <tr>
                <td class="text-muted">Payment Method:</td>
                <td class="fw-semibold" id="detail-payment-method">-</td>
              </tr>
              <tr>
                <td class="text-muted">Status:</td>
                <td>
                  <span id="detail-status-badge" class="badge">-</span>
                </td>
              </tr>
              <tr>
                <td class="text-muted">Created:</td>
                <td class="fw-semibold" id="detail-created-at">-</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-3">User Information</h6>
            <table class="table table-borderless table-sm">
              <tr>
                <td class="text-muted">Name:</td>
                <td class="fw-semibold" id="detail-user-name">-</td>
              </tr>
              <tr>
                <td class="text-muted">Email:</td>
                <td class="fw-semibold" id="detail-user-email">-</td>
              </tr>
              <tr>
                <td class="text-muted">Current Balance:</td>
                <td class="fw-semibold" id="detail-user-balance">-</td>
              </tr>
              <tr>
                <td class="text-muted">User ID:</td>
                <td class="fw-semibold" id="detail-user-id">-</td>
              </tr>
            </table>

            <h6 class="text-muted mb-3 mt-4">
              <span id="detail-description-label">Transaction Information</span>
            </h6>
            <div class="border rounded p-3 bg-light">
              <p class="mb-0" id="detail-description" style="white-space: pre-wrap;">No information provided</p>
            </div>
            <small class="text-muted" id="detail-description-hint"></small>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function viewTransactionDetails(transactionId) {
    // Fetch transaction details
    fetch(`/admin/transactions/${transactionId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateTransactionDetailsModal(data.transaction);
                const modal = new coreui.Modal(document.getElementById('transactionDetailsModal'));
                modal.show();
            } else {
                alert('Error loading transaction details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading transaction details');
        });
}

function populateTransactionDetailsModal(transaction) {
    document.getElementById('detail-transaction-id').textContent = transaction.id;
    document.getElementById('detail-reference-number').textContent = transaction.reference_number || 'N/A';

    // Type badge
    const typeBadge = document.getElementById('detail-type-badge');
    typeBadge.textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
    typeBadge.className = `badge ${transaction.type === 'deposit' ? 'bg-success' : 'bg-danger'}`;

    document.getElementById('detail-amount').textContent = `${transaction.type === 'withdrawal' ? '-' : '+'}$${parseFloat(transaction.amount).toFixed(2)}`;
    document.getElementById('detail-payment-method').textContent = transaction.payment_method || 'N/A';

    // Status badge
    const statusBadge = document.getElementById('detail-status-badge');
    statusBadge.textContent = transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1);
    statusBadge.className = `badge ${
        transaction.status === 'approved' ? 'bg-success' :
        transaction.status === 'rejected' ? 'bg-danger' :
        transaction.status === 'pending' ? 'bg-warning' : 'bg-secondary'
    }`;

    document.getElementById('detail-created-at').textContent = new Date(transaction.created_at).toLocaleString();

    // User information
    document.getElementById('detail-user-name').textContent = transaction.user.fullname || transaction.user.username;
    document.getElementById('detail-user-email').textContent = transaction.user.email;
    document.getElementById('detail-user-balance').textContent = transaction.user.wallet ? `$${parseFloat(transaction.user.wallet.total_balance).toFixed(2)}` : 'N/A';
    document.getElementById('detail-user-id').textContent = transaction.user.id;

    // Description with contextual labeling
    const descriptionLabel = document.getElementById('detail-description-label');
    const descriptionHint = document.getElementById('detail-description-hint');
    const descriptionText = document.getElementById('detail-description');

    if (transaction.type === 'deposit') {
        descriptionLabel.textContent = 'Payment Notification / Reference';
        descriptionText.textContent = transaction.description || 'No payment notification provided';
        descriptionHint.textContent = 'Check reference number against payment received';
        descriptionHint.className = 'text-info d-block mt-2';
    } else if (transaction.type === 'withdrawal') {
        descriptionLabel.textContent = 'Withdrawal Details';
        descriptionText.textContent = transaction.description || 'No details provided';
        descriptionHint.textContent = '';
    } else {
        descriptionLabel.textContent = 'Transaction Information';
        descriptionText.textContent = transaction.description || 'No information provided';
        descriptionHint.textContent = '';
    }
}
</script>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>
@endsection