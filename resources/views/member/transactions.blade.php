@extends('layouts.admin')

@section('title', 'Transaction History')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                    </svg>
                    Transaction History
                </h4>
                <p class="text-body-secondary mb-0">View your e-wallet transactions and activity</p>
            </div>
            <div>
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

<!-- Wallet Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-success-gradient text-white">
            <div class="card-body text-center">
                <h6 class="card-title">MLM Balance</h6>
                <h3 class="fw-bold">{{ currency($wallet->mlm_balance) }}</h3>
                <small class="opacity-75">Withdrawable</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary-gradient text-white">
            <div class="card-body text-center">
                <h6 class="card-title">Purchase Balance</h6>
                <h3 class="fw-bold">{{ currency($wallet->purchase_balance) }}</h3>
                <small class="opacity-75">Transferable</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info-gradient text-white">
            <div class="card-body text-center">
                <h6 class="card-title">Total Balance</h6>
                <h3 class="fw-bold">{{ currency($wallet->total_balance) }}</h3>
                <p class="mb-0 mt-2">
                    <span class="badge {{ $wallet->is_active ? 'bg-light text-success' : 'bg-warning text-dark' }}">
                        {{ $wallet->is_active ? 'Account Active' : 'Account Frozen' }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                </svg>
                <strong>Quick Actions</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    @can('deposit_funds')
                    <a href="{{ route('wallet.deposit') }}" class="btn btn-success btn-sm">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                        </svg>
                        Deposit
                    </a>
                    @endcan
                    @can('transfer_funds')
                    <a href="{{ route('wallet.transfer') }}" class="btn btn-primary btn-sm">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                        </svg>
                        Transfer
                    </a>
                    @endcan
                    @can('transfer_funds')
                    <a href="{{ route('wallet.convert') }}" class="btn btn-purple btn-sm">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-vertical') }}"></use>
                        </svg>
                        Convert
                    </a>
                    @endcan
                    @can('withdraw_funds')
                    <a href="{{ route('wallet.withdraw') }}" class="btn btn-danger btn-sm">
                        <svg class="icon me-1">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-minus') }}"></use>
                        </svg>
                        Withdraw
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Statistics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-primary">
            <div class="card-body text-center">
                <div class="h4 mb-0">{{ $transactions->where('status', 'pending')->count() }}</div>
                <div>Pending</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-success">
            <div class="card-body text-center">
                <div class="h4 mb-0">{{ $transactions->where('status', 'approved')->count() }}</div>
                <div>Approved</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-danger">
            <div class="card-body text-center">
                <div class="h4 mb-0">{{ $transactions->where('status', 'rejected')->count() }}</div>
                <div>Rejected</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-white bg-info">
            <div class="card-body text-center">
                <div class="h4 mb-0">{{ $transactions->total() }}</div>
                <div>Total</div>
            </div>
        </div>
    </div>
</div>

<!-- Professional Transactions Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px; flex-shrink: 0;">
                    <svg class="icon text-white">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                    </svg>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold">Recent Transactions</h5>
                    <small class="text-muted">View and track all your wallet activity</small>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-start gap-2">
                <div class="badge bg-light text-dark px-3 py-2">
                    <strong>{{ $transactions->total() }}</strong> total
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="per_page" class="form-label mb-0 small text-muted text-nowrap">Show:</label>
                    <select id="per_page" onchange="changePerPage(this.value)" class="form-select form-select-sm border-primary" style="min-width: 70px;">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if($transactions->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="border-0 fw-bold text-uppercase small px-4 py-3">Transaction</th>
                    <th class="border-0 fw-bold text-uppercase small px-4 py-3">Type</th>
                    <th class="border-0 fw-bold text-uppercase small px-4 py-3">Amount</th>
                    <th class="border-0 fw-bold text-uppercase small px-4 py-3">Status</th>
                    <th class="border-0 fw-bold text-uppercase small px-4 py-3">Date</th>
                    <th class="border-0 fw-bold text-uppercase small px-4 py-3">Reference</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr class="border-bottom">
                    <td class="px-4 py-3">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle p-2 me-3 d-flex align-items-center justify-content-center {{
                                $transaction->type == 'deposit' || $transaction->type == 'transfer_in' || $transaction->type == 'balance_conversion' ? 'bg-success-subtle' :
                                ($transaction->type == 'transfer_charge' || $transaction->type == 'withdrawal_fee' ? 'bg-warning-subtle' : 'bg-danger-subtle')
                            }}" style="width: 40px; height: 40px; min-width: 40px; flex-shrink: 0;">
                                <svg class="icon {{
                                    $transaction->type == 'deposit' || $transaction->type == 'transfer_in' || $transaction->type == 'balance_conversion' ? 'text-success' :
                                    ($transaction->type == 'transfer_charge' || $transaction->type == 'withdrawal_fee' ? 'text-warning' : 'text-danger')
                                }}">
                                    @if($transaction->type == 'deposit' || $transaction->type == 'transfer_in')
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-bottom') }}"></use>
                                    @elseif($transaction->type == 'balance_conversion')
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-vertical') }}"></use>
                                    @elseif($transaction->type == 'transfer_out')
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                                    @elseif($transaction->type == 'transfer_charge' || $transaction->type == 'withdrawal_fee')
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                                    @else
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-top') }}"></use>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark mb-1">
                                    @if($transaction->type == 'transfer_out')
                                        Transfer Sent
                                    @elseif($transaction->type == 'transfer_in')
                                        Transfer Received
                                    @elseif($transaction->type == 'transfer_charge')
                                        Transfer Fee
                                    @elseif($transaction->type == 'withdrawal_fee')
                                        Withdrawal Fee
                                    @elseif($transaction->type == 'balance_conversion')
                                        Balance Conversion
                                    @else
                                        {{ ucfirst($transaction->type) }}
                                    @endif
                                </div>
                                @if($transaction->description)
                                    <div class="small text-muted">{{ Str::limit($transaction->description, 40) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge rounded-pill px-3 py-2 {{
                            $transaction->type == 'deposit' ? 'bg-success-subtle text-success' :
                            ($transaction->type == 'withdraw' ? 'bg-danger-subtle text-danger' :
                            ($transaction->type == 'balance_conversion' ? 'bg-primary-subtle text-primary' :
                            ($transaction->type == 'transfer_out' || $transaction->type == 'transfer_in' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning')))
                        }}">
                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="fw-bold {{
                            $transaction->type == 'balance_conversion' ? 'text-primary' :
                            (in_array($transaction->type, ['deposit', 'transfer_in']) ? 'text-success' :
                            (in_array($transaction->type, ['transfer_charge', 'withdrawal_fee']) ? 'text-warning' : 'text-danger'))
                        }}">
                            @if($transaction->type == 'balance_conversion')
                                <svg class="icon me-1" style="width: 14px; height: 14px;">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-vertical') }}"></use>
                                </svg>
                            @else
                                {{ in_array($transaction->type, ['deposit', 'transfer_in']) ? '+' : '-' }}
                            @endif
                            <span class="fs-6">${{ number_format($transaction->amount, 2) }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge rounded-pill px-3 py-2 {{
                            $transaction->status == 'approved' ? 'bg-success text-white' :
                            ($transaction->status == 'rejected' ? 'bg-danger text-white' :
                            ($transaction->status == 'pending' ? 'bg-warning text-dark' : 'bg-secondary text-white'))
                        }}">
                            <svg class="icon icon-sm me-1">
                                @if($transaction->status == 'approved')
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                @elseif($transaction->status == 'rejected')
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                @elseif($transaction->status == 'pending')
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                                @endif
                            </svg>
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-dark fw-medium">{{ $transaction->created_at->format('M d, Y') }}</div>
                        <div class="small text-muted">{{ $transaction->created_at->format('g:i A') }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($transaction->reference_number)
                            <div class="font-monospace small bg-light px-2 py-1 rounded border">
                                {{ $transaction->reference_number }}
                            </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card-body">
        <div class="text-center py-5">
            <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                <svg class="icon icon-3xl text-muted">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
            </div>
            <h5 class="text-muted mb-3">No transactions yet</h5>
            <p class="text-muted mb-4">Your transaction history will appear here once you start using your e-wallet.</p>
            <a href="{{ route('wallet.deposit') }}" class="btn btn-success btn-lg px-4">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                </svg>
                Make your first deposit
            </a>
        </div>
    </div>
    @endif

    <!-- Pagination -->
    @if($transactions->hasPages())
        <div class="card-footer bg-light">
            {{ $transactions->appends(request()->query())->links('vendor.pagination.coreui') }}
        </div>
    @endif
</div>

@push('styles')
<style>
.bg-success-gradient {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.bg-primary-gradient {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
}

.bg-info-gradient {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

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

.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1) !important;
}

.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.025);
    transition: background-color 0.15s ease-in-out;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.card-footer {
    border-radius: 0 0 12px 12px !important;
    border-top: 1px solid rgba(0, 0, 0, 0.125);
}

.badge {
    font-weight: 500;
}

.table th {
    letter-spacing: 0.5px;
}

.font-monospace {
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}

/* Pagination Improvements */
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    border-color: #dee2e6;
    color: #6c757d;
    padding: 0.5rem 0.75rem;
    transition: all 0.15s ease-in-out;
}

.pagination .page-link:hover {
    background-color: #e9ecef;
    border-color: #dee2e6;
    color: #495057;
}

.pagination .page-item.active .page-link {
    background-color: var(--cui-primary);
    border-color: var(--cui-primary);
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
}

/* Fix any layout issues */
.table-responsive {
    border-radius: 0;
}

/* Ensure icons are properly sized */
.icon {
    width: 1rem;
    height: 1rem;
}

.icon-sm {
    width: 0.875rem;
    height: 0.875rem;
}

.icon-3xl {
    width: 3rem;
    height: 3rem;
}
</style>
@endpush

@push('scripts')
<script>
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page when changing per_page
    window.location.href = url.toString();
}

// Add smooth loading animation
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 50);
    });
});
</script>
@endpush

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@endsection