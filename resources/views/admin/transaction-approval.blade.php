@extends('layouts.admin')

@section('title', 'Transaction Approval')
@section('page-title', 'Transaction Approval')

@section('content')
<!-- Success/Error Messages -->
<div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1060;"></div>

<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-task') }}"></use>
                    </svg>
                    Transaction Approval
                </h4>
                <p class="text-body-secondary mb-0">Review and approve pending transactions</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
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
                    <div id="approved-count" class="fs-4 fw-semibold">{{ \App\Models\Transaction::where('status', 'approved')->whereDate('approved_at', today())->count() }}</div>
                    <div>Approved Today</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-danger-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div id="rejected-count" class="fs-4 fw-semibold">{{ \App\Models\Transaction::where('status', 'rejected')->whereDate('approved_at', today())->count() }}</div>
                    <div>Rejected Today</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                </svg>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card text-white bg-info-gradient">
            <div class="card-body pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <div id="total-value" class="fs-4 fw-semibold">{{ currency($pendingTransactions->sum('amount')) }}</div>
                    <div>Total Value</div>
                </div>
                <svg class="icon icon-3xl">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card mb-4">
    <div class="card-header">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
        </svg>
        <strong>Bulk Actions</strong>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <button onclick="selectAll()" class="btn btn-secondary">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-alt') }}"></use>
                </svg>
                Select All
            </button>
            <button onclick="clearSelection()" class="btn btn-outline-secondary">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                </svg>
                Clear Selection
            </button>
            <button onclick="bulkApprove()" class="btn btn-success">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                </svg>
                Approve Selected
            </button>
            <button onclick="bulkReject()" class="btn btn-danger">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-ban') }}"></use>
                </svg>
                Reject Selected
            </button>
        </div>
    </div>
</div>

<!-- Pending Transactions -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                <strong>Pending Transactions</strong>
                <small class="text-body-secondary ms-2">Transactions awaiting administrative approval.</small>
            </div>
            <x-per-page-selector :perPage="$perPage" />
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 50px;">
                            <input type="checkbox" class="form-check-input" id="selectAllTransactions">
                        </th>
                        <th scope="col">Transaction</th>
                        <th scope="col">User</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingTransactions as $transaction)
                        <tr data-transaction-id="{{ $transaction->id }}">
                            <td>
                                <input type="checkbox" name="selected_transactions[]" value="{{ $transaction->id }}" class="transaction-checkbox form-check-input">
                            </td>
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
                                            Ref: {{ $transaction->reference_number ?? 'N/A' }} •
                                            {{ $transaction->created_at->diffForHumans() }}
                                        </div>
                                        @if($transaction->amount > 5000)
                                            <span class="badge bg-danger">High Priority</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $transaction->user->fullname ?? $transaction->user->username }}</div>
                                <div class="text-body-secondary">{{ $transaction->user->email }}</div>
                                @if($transaction->user->wallet)
                                    <div class="text-body-secondary small">Balance: {{ currency($transaction->user->wallet->balance) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold {{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type === 'withdrawal' ? '-' : '+' }}{{ currency($transaction->amount) }}
                                </div>
                                <div class="text-body-secondary small">{{ ucfirst($transaction->payment_method ?? 'N/A') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ ucfirst($transaction->status) }}</span>
                            </td>
                            <td class="text-center">
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-body-secondary py-4">
                                <svg class="icon icon-3xl text-body-secondary mx-auto mb-3" style="width: 3rem; height: 3rem;">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                                </svg>
                                <h5 class="text-body-secondary">No pending transactions</h5>
                                <p class="text-body-secondary mb-0">All transactions have been processed.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pendingTransactions->hasPages())
        <div class="card-footer">
            {{ $pendingTransactions->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- CoreUI Modals -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="approvalModalLabel">
          <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
          </svg>
          Approve Transaction
        </h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Transaction Summary -->
        <div class="alert alert-success border-success">
          <h6 class="alert-heading">Transaction Summary</h6>
          <div class="row">
            <div class="col-md-6">
              <p class="mb-1"><strong>Transaction ID:</strong> <span id="approve-transaction-id">-</span></p>
              <p class="mb-1"><strong>Type:</strong> <span id="approve-type">-</span></p>
              <p class="mb-1"><strong>Amount:</strong> <span id="approve-amount">-</span></p>
            </div>
            <div class="col-md-6">
              <p class="mb-1"><strong>User:</strong> <span id="approve-user-name">-</span></p>
              <p class="mb-1"><strong>Payment Method:</strong> <span id="approve-payment-method">-</span></p>
              <p class="mb-1"><strong>Current Balance:</strong> <span id="approve-user-balance">-</span></p>
            </div>
          </div>
          <div class="mt-2">
            <p class="mb-0"><strong id="approve-description-label">Description:</strong></p>
            <p class="mb-0 text-muted" id="approve-description" style="white-space: pre-wrap;">No description provided</p>
            <small class="text-muted" id="approve-description-hint"></small>
          </div>
        </div>

        <form id="approvalForm">
          <div class="mb-3">
            <label for="approvalNotes" class="form-label">Admin Notes (Optional)</label>
            <textarea id="approvalNotes" rows="3" class="form-control" placeholder="Add any notes about this approval..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="confirmApproval()">
          <svg class="icon me-1">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
          </svg>
          Confirm Approval
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectionModal" tabindex="-1" aria-labelledby="rejectionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectionModalLabel">
          <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
          </svg>
          Reject Transaction
        </h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Transaction Summary -->
        <div class="alert alert-danger border-danger">
          <h6 class="alert-heading">Transaction Summary</h6>
          <div class="row">
            <div class="col-md-6">
              <p class="mb-1"><strong>Transaction ID:</strong> <span id="reject-transaction-id">-</span></p>
              <p class="mb-1"><strong>Type:</strong> <span id="reject-type">-</span></p>
              <p class="mb-1"><strong>Amount:</strong> <span id="reject-amount">-</span></p>
            </div>
            <div class="col-md-6">
              <p class="mb-1"><strong>User:</strong> <span id="reject-user-name">-</span></p>
              <p class="mb-1"><strong>Payment Method:</strong> <span id="reject-payment-method">-</span></p>
              <p class="mb-1"><strong>Current Balance:</strong> <span id="reject-user-balance">-</span></p>
            </div>
          </div>
          <div class="mt-2">
            <p class="mb-0"><strong id="reject-description-label">Description:</strong></p>
            <p class="mb-0 text-muted" id="reject-description" style="white-space: pre-wrap;">No description provided</p>
            <small class="text-muted" id="reject-description-hint"></small>
          </div>
        </div>

        <form id="rejectionForm">
          <div class="mb-3">
            <label for="rejectionReason" class="form-label">Rejection Reason (Required)</label>
            <textarea id="rejectionReason" rows="3" class="form-control" placeholder="Explain why this transaction is being rejected..." required></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="confirmRejection()">
          <svg class="icon me-1">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
          </svg>
          Confirm Rejection
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Review Transaction Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reviewModalLabel">
          <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
          </svg>
          Transaction Review
        </h5>
        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-muted mb-3">Transaction Details</h6>
            <table class="table table-borderless table-sm">
              <tr>
                <td class="text-muted">Transaction ID:</td>
                <td class="fw-semibold" id="review-transaction-id">-</td>
              </tr>
              <tr>
                <td class="text-muted">Reference Number:</td>
                <td class="fw-semibold" id="review-reference-number">-</td>
              </tr>
              <tr>
                <td class="text-muted">Type:</td>
                <td>
                  <span id="review-type-badge" class="badge">-</span>
                </td>
              </tr>
              <tr>
                <td class="text-muted">Amount:</td>
                <td class="fw-semibold" id="review-amount">-</td>
              </tr>
              <tr>
                <td class="text-muted">Payment Method:</td>
                <td class="fw-semibold" id="review-payment-method">-</td>
              </tr>
              <tr>
                <td class="text-muted">Status:</td>
                <td>
                  <span id="review-status-badge" class="badge bg-warning">-</span>
                </td>
              </tr>
              <tr>
                <td class="text-muted">Created:</td>
                <td class="fw-semibold" id="review-created-at">-</td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-3">User Information</h6>
            <table class="table table-borderless table-sm">
              <tr>
                <td class="text-muted">Name:</td>
                <td class="fw-semibold" id="review-user-name">-</td>
              </tr>
              <tr>
                <td class="text-muted">Email:</td>
                <td class="fw-semibold" id="review-user-email">-</td>
              </tr>
              <tr>
                <td class="text-muted">Current Balance:</td>
                <td class="fw-semibold" id="review-user-balance">-</td>
              </tr>
              <tr>
                <td class="text-muted">User ID:</td>
                <td class="fw-semibold" id="review-user-id">-</td>
              </tr>
            </table>

            <h6 class="text-muted mb-3 mt-4">
              <span id="review-description-label">Transaction Information</span>
            </h6>
            <div class="border rounded p-3 bg-light">
              <p class="mb-0" id="review-description" style="white-space: pre-wrap;">No information provided</p>
            </div>
            <small class="text-muted" id="review-description-hint"></small>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" onclick="rejectFromReview()">
          <svg class="icon me-1">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
          </svg>
          Reject
        </button>
        <button type="button" class="btn btn-success" onclick="approveFromReview()">
          <svg class="icon me-1">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
          </svg>
          Approve
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>
@endsection

@push('scripts')
<script>
let currentTransactionId = null;

function approveTransaction(id) {
    currentTransactionId = id;
    populateApprovalModal();
    const approvalModal = new coreui.Modal(document.getElementById('approvalModal'));
    approvalModal.show();
}

function rejectTransaction(id) {
    currentTransactionId = id;
    populateRejectionModal();
    const rejectionModal = new coreui.Modal(document.getElementById('rejectionModal'));
    rejectionModal.show();
}

function reviewTransaction(id) {
    currentTransactionId = id;

    // Fetch transaction details
    fetch(`/admin/transactions/${id}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateReviewModal(data.transaction);
                const reviewModal = new coreui.Modal(document.getElementById('reviewModal'));
                reviewModal.show();
            } else {
                showAlert('Error loading transaction details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading transaction details', 'error');
        });
}

function populateReviewModal(transaction) {
    document.getElementById('review-transaction-id').textContent = transaction.id;
    document.getElementById('review-reference-number').textContent = transaction.reference_number || 'N/A';

    // Type badge
    const typeBadge = document.getElementById('review-type-badge');
    typeBadge.textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
    typeBadge.className = `badge ${transaction.type === 'deposit' ? 'bg-success' : 'bg-danger'}`;

    document.getElementById('review-amount').textContent = `${transaction.type === 'withdrawal' ? '-' : '+'}$${parseFloat(transaction.amount).toFixed(2)}`;
    document.getElementById('review-payment-method').textContent = transaction.payment_method || 'N/A';

    // Status badge
    const statusBadge = document.getElementById('review-status-badge');
    statusBadge.textContent = transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1);

    document.getElementById('review-created-at').textContent = new Date(transaction.created_at).toLocaleString();

    // User information
    document.getElementById('review-user-name').textContent = transaction.user.fullname || transaction.user.username;
    document.getElementById('review-user-email').textContent = transaction.user.email;
    document.getElementById('review-user-balance').textContent = transaction.user.wallet ? `$${parseFloat(transaction.user.wallet.balance).toFixed(2)}` : 'N/A';
    document.getElementById('review-user-id').textContent = transaction.user.id;

    // Description with contextual labeling
    const descriptionLabel = document.getElementById('review-description-label');
    const descriptionHint = document.getElementById('review-description-hint');
    const descriptionText = document.getElementById('review-description');

    if (transaction.type === 'deposit') {
        descriptionLabel.textContent = 'Payment Notification / Reference';
        descriptionText.textContent = transaction.description || 'No payment notification provided';
        descriptionHint.textContent = 'User should paste Gcash/Maya notification with reference number for faster verification';
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

function approveFromReview() {
    const reviewModal = coreui.Modal.getInstance(document.getElementById('reviewModal'));
    reviewModal.hide();

    // Show approval modal with pre-populated data
    populateApprovalModal();
    const approvalModal = new coreui.Modal(document.getElementById('approvalModal'));
    approvalModal.show();
}

function rejectFromReview() {
    const reviewModal = coreui.Modal.getInstance(document.getElementById('reviewModal'));
    reviewModal.hide();

    // Show rejection modal with pre-populated data
    populateRejectionModal();
    const rejectionModal = new coreui.Modal(document.getElementById('rejectionModal'));
    rejectionModal.show();
}

function populateApprovalModal() {
    // Fetch transaction details if not already loaded
    if (!currentTransactionId) return;

    fetch(`/admin/transactions/${currentTransactionId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const transaction = data.transaction;
                document.getElementById('approve-transaction-id').textContent = transaction.id;
                document.getElementById('approve-type').textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
                document.getElementById('approve-amount').textContent = `${transaction.type === 'withdrawal' ? '-' : '+'}$${parseFloat(transaction.amount).toFixed(2)}`;
                document.getElementById('approve-user-name').textContent = transaction.user.fullname || transaction.user.username;
                document.getElementById('approve-payment-method').textContent = transaction.payment_method || 'N/A';
                document.getElementById('approve-user-balance').textContent = transaction.user.wallet ? `$${parseFloat(transaction.user.wallet.balance).toFixed(2)}` : 'N/A';

                // Contextual description labeling
                const approveDescLabel = document.getElementById('approve-description-label');
                const approveDescHint = document.getElementById('approve-description-hint');
                const approveDescText = document.getElementById('approve-description');

                if (transaction.type === 'deposit') {
                    approveDescLabel.textContent = 'Payment Notification / Reference:';
                    approveDescText.textContent = transaction.description || 'No payment notification provided';
                    approveDescHint.textContent = 'Check reference number against payment received';
                    approveDescHint.className = 'text-info d-block mt-1';
                } else if (transaction.type === 'withdrawal') {
                    approveDescLabel.textContent = 'Withdrawal Details:';
                    approveDescText.textContent = transaction.description || 'No details provided';
                    approveDescHint.textContent = '';
                } else {
                    approveDescLabel.textContent = 'Description:';
                    approveDescText.textContent = transaction.description || 'No description provided';
                    approveDescHint.textContent = '';
                }
            }
        })
        .catch(error => {
            console.error('Error loading transaction details:', error);
        });
}

function populateRejectionModal() {
    // Fetch transaction details if not already loaded
    if (!currentTransactionId) return;

    fetch(`/admin/transactions/${currentTransactionId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const transaction = data.transaction;
                document.getElementById('reject-transaction-id').textContent = transaction.id;
                document.getElementById('reject-type').textContent = transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1);
                document.getElementById('reject-amount').textContent = `${transaction.type === 'withdrawal' ? '-' : '+'}$${parseFloat(transaction.amount).toFixed(2)}`;
                document.getElementById('reject-user-name').textContent = transaction.user.fullname || transaction.user.username;
                document.getElementById('reject-payment-method').textContent = transaction.payment_method || 'N/A';
                document.getElementById('reject-user-balance').textContent = transaction.user.wallet ? `$${parseFloat(transaction.user.wallet.balance).toFixed(2)}` : 'N/A';

                // Contextual description labeling
                const rejectDescLabel = document.getElementById('reject-description-label');
                const rejectDescHint = document.getElementById('reject-description-hint');
                const rejectDescText = document.getElementById('reject-description');

                if (transaction.type === 'deposit') {
                    rejectDescLabel.textContent = 'Payment Notification / Reference:';
                    rejectDescText.textContent = transaction.description || 'No payment notification provided';
                    rejectDescHint.textContent = 'Verify if payment was actually received before rejecting';
                    rejectDescHint.className = 'text-warning d-block mt-1';
                } else if (transaction.type === 'withdrawal') {
                    rejectDescLabel.textContent = 'Withdrawal Details:';
                    rejectDescText.textContent = transaction.description || 'No details provided';
                    rejectDescHint.textContent = '';
                } else {
                    rejectDescLabel.textContent = 'Description:';
                    rejectDescText.textContent = transaction.description || 'No description provided';
                    rejectDescHint.textContent = '';
                }
            }
        })
        .catch(error => {
            console.error('Error loading transaction details:', error);
        });
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

function bulkApprove() {
    const selected = document.querySelectorAll('.transaction-checkbox:checked');
    if (selected.length === 0) {
        showAlert('Please select transactions to approve', 'warning');
        return;
    }

    if (confirm(`Are you sure you want to approve ${selected.length} transaction(s)?`)) {
        // Implement bulk approval logic here
        showAlert(`${selected.length} transaction(s) approved successfully!`, 'success');
        location.reload();
    }
}

function bulkReject() {
    const selected = document.querySelectorAll('.transaction-checkbox:checked');
    if (selected.length === 0) {
        showAlert('Please select transactions to reject', 'warning');
        return;
    }

    if (confirm(`Are you sure you want to reject ${selected.length} transaction(s)?`)) {
        // Implement bulk rejection logic here
        showAlert(`${selected.length} transaction(s) rejected successfully!`, 'success');
        location.reload();
    }
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