@extends('layouts.admin')

@section('title', 'Transaction Approval')

@section('content')
<!-- Success/Error Messages -->
<div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-task') }}"></use>
                    </svg>
                    Transaction Approval
                </h4>
                <p class="text-body-secondary mb-0">Review and approve pending transactions</p>
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

<!-- Approval Stats -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-warning-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div id="pending-count" class="fs-4 fw-semibold">{{ $pendingTransactions->total() }}</div>
                    <div>Pending</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-success-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div class="fs-4 fw-semibold">{{ $approvedToday ?? 0 }}</div>
                    <div>Approved Today</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                </svg>
            </div>
        </div>
    </div>
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Today Approved</dt>
                                <dd id="approved-count" class="text-lg font-medium text-gray-900">{{ \App\Models\Transaction::where('status', 'approved')->whereDate('approved_at', today())->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Today Rejected</dt>
                                <dd id="rejected-count" class="text-lg font-medium text-gray-900">{{ \App\Models\Transaction::where('status', 'rejected')->whereDate('approved_at', today())->count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Value</dt>
                                <dd id="total-value" class="text-lg font-medium text-gray-900">${{ number_format($pendingTransactions->sum('amount'), 2) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Bulk Actions</h3>
            </div>
            <div class="px-6 py-4">
                <div class="flex flex-wrap gap-4">
                    <button onclick="selectAll()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Select All
                    </button>
                    <button onclick="clearSelection()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                        Clear Selection
                    </button>
                    <button onclick="bulkApprove()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Approve Selected
                    </button>
                    <button onclick="bulkReject()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Reject Selected
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Transactions -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Pending Transactions</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Transactions awaiting administrative approval.
                </p>
            </div>
            @forelse($pendingTransactions as $transaction)
                <div class="list-group-item" data-transaction-id="{{ $transaction->id }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex align-items-start">
                            <div class="me-3 pt-1">
                                <input type="checkbox" name="selected_transactions[]" value="{{ $transaction->id }}" class="transaction-checkbox form-check-input">
                            </div>
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
                            <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold">{{ ucfirst($transaction->type) }} Request</div>
                                <span class="badge {{ $transaction->amount > 5000 ? 'bg-danger' : 'bg-primary' }}">
                                    {{ $transaction->amount > 5000 ? 'High Priority' : 'Standard' }}
                                </span>
                            </div>
                            <div class="mt-1">
                                <div class="text-body-secondary">
                                    <span class="fw-semibold">{{ $transaction->user->fullname ?? $transaction->user->username }}</span> ({{ $transaction->user->email }})
                                </div>
                                <div class="text-body-secondary small">
                                    Ref: {{ $transaction->reference_number ?? 'N/A' }} •
                                    {{ ucfirst($transaction->payment_method ?? 'N/A') }} •
                                    Requested: {{ $transaction->created_at->diffForHumans() }}
                                </div>
                                @if(isset($transaction->description) && $transaction->description)
                                    <div class="text-body-secondary mt-1">{{ $transaction->description }}</div>
                                @endif
                            </div>
                            <div class="mt-2 d-flex align-items-center">
                                <span class="fs-5 fw-bold me-3 {{ $transaction->type === 'deposit' ? 'text-success' : ($transaction->type === 'withdrawal' ? 'text-danger' : 'text-primary') }}">
                                    {{ $transaction->type === 'withdrawal' ? '-' : '' }}${{ number_format($transaction->amount, 2) }}
                                </span>
                                @if($transaction->user->wallet)
                                    <span class="text-body-secondary small">Current Balance: ${{ number_format($transaction->user->wallet->balance, 2) }}</span>
                                @else
                                    <span class="text-body-secondary small">Current Balance: $0.00</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                        <div class="btn-group" role="group">
                            <button onclick="approveTransaction({{ $transaction->id }})" class="btn btn-sm btn-success">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                </svg>
                                Approve
                            </button>
                            <button onclick="rejectTransaction({{ $transaction->id }})" class="btn btn-sm btn-danger">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                </svg>
                                Reject
                            </button>
                            <button onclick="reviewTransaction({{ $transaction->id }})" class="btn btn-sm btn-secondary">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                                </svg>
                                Review
                            </button>
                        </div>
                </div>
            @empty
                <div class="list-group-item text-center py-5">
                    <svg class="icon icon-3xl text-body-secondary mx-auto mb-3" style="width: 3rem; height: 3rem;">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                    </svg>
                    <h5 class="text-body-secondary">No pending transactions</h5>
                    <p class="text-body-secondary mb-0">All transactions have been processed.</p>
                </div>
            @endforelse
        </div>
    </div>
    @if($pendingTransactions->hasPages())
        <div class="card-footer">
            {{ $pendingTransactions->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')

<!-- CoreUI Modals -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="approvalModalLabel">Approve Transaction</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="approvalForm">
          <div class="mb-3">
            <label for="approvalNotes" class="form-label">Admin Notes (Optional)</label>
            <textarea id="approvalNotes" rows="3" class="form-control" placeholder="Add any notes about this approval..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="confirmApproval()">Confirm Approval</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectionModalLabel">Reject Transaction</h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="rejectionForm">
          <div class="mb-3">
            <label for="rejectionReason" class="form-label">Rejection Reason (Required)</label>
            <textarea id="rejectionReason" rows="3" class="form-control" placeholder="Explain why this transaction is being rejected..." required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmRejection()">Confirm Rejection</button>
      </div>
    </div>
  </div>
</div>

<script>
let currentTransactionId = null;

function approveTransaction(id) {
    currentTransactionId = id;
    const approvalModal = new coreui.Modal(document.getElementById('approvalModal'));
    approvalModal.show();
}

function rejectTransaction(id) {
    currentTransactionId = id;
    const rejectionModal = new coreui.Modal(document.getElementById('rejectionModal'));
    rejectionModal.show();
}

function reviewTransaction(id) {
    alert(`Review functionality for transaction ${id} would be implemented here.`);
}

function confirmApproval() {
    if (!currentTransactionId) return;

    const notes = document.getElementById('approvalNotes').value;

    fetch(`/admin/transactions/${currentTransactionId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Transaction approved successfully!', 'success');
            location.reload();
        } else {
            showAlert(data.message || 'Error approving transaction', 'danger');
        }
    })
    .catch(error => {
        showAlert('Error approving transaction', 'danger');
        console.error('Error:', error);
    });

    const modal = coreui.Modal.getInstance(document.getElementById('approvalModal'));
    modal.hide();
}

function confirmRejection() {
    if (!currentTransactionId) return;

    const reason = document.getElementById('rejectionReason').value;
    if (!reason.trim()) {
        showAlert('Please provide a rejection reason', 'warning');
        return;
    }

    fetch(`/admin/transactions/${currentTransactionId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Transaction rejected successfully!', 'success');
            location.reload();
        } else {
            showAlert(data.message || 'Error rejecting transaction', 'danger');
        }
    })
    .catch(error => {
        showAlert('Error rejecting transaction', 'danger');
        console.error('Error:', error);
    });

    const modal = coreui.Modal.getInstance(document.getElementById('rejectionModal'));
    modal.hide();
}


function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    alertContainer.innerHTML = alertHtml;

    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

function selectAll() {
    document.querySelectorAll('.transaction-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function clearSelection() {
    document.querySelectorAll('.transaction-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function updateStats() {
    // Fetch updated statistics from server
    fetch('/admin/transaction-stats', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update all statistics
            document.getElementById('pending-count').textContent = data.pending_count;
            document.getElementById('approved-count').textContent = data.approved_today;
            document.getElementById('rejected-count').textContent = data.rejected_today;
            document.getElementById('total-value').textContent = '$' + data.total_value;
        }
    })
    .catch(error => {
        console.error('Error updating stats:', error);
    });
}

</script>
@endpush