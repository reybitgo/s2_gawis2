@extends('layouts.app')

@section('content')
<!-- Success/Error Messages -->
<div id="alert-container" class="fixed top-4 right-4 z-50"></div>
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Transaction Approval</h1>
                        <p class="mt-1 text-sm text-gray-600">Review and approve pending transactions</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Approval Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                                <dd class="text-lg font-medium text-gray-900">12</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Approved Today</dt>
                                <dd class="text-lg font-medium text-gray-900">45</dd>
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
                                <dt class="text-sm font-medium text-gray-500 truncate">Rejected Today</dt>
                                <dd class="text-lg font-medium text-gray-900">3</dd>
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
                                <dd class="text-lg font-medium text-gray-900">$23,450</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex" aria-label="Tabs">
                    <a href="#" class="border-yellow-500 text-yellow-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        Pending (12)
                    </a>
                    <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm ml-8">
                        Approved
                    </a>
                    <a href="#" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm ml-8">
                        Rejected
                    </a>
                </nav>
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
            <ul class="divide-y divide-gray-200">
                <!-- Withdrawal Request -->
                <li class="px-4 py-6 sm:px-6" data-transaction-id="1">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <input type="checkbox" name="selected_transactions[]" value="1" class="transaction-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    <h4 class="text-sm font-medium text-gray-900">Withdrawal Request</h4>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        High Priority
                                    </span>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Bob Johnson</span> (bob@example.com)
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Bank Account: ****1234 (Chase Bank) • Requested: 2 hours ago
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="text-lg font-bold text-red-600">-$1,500.00</span>
                                    <span class="text-sm text-gray-500">Available Balance: $2,450.00</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="approveTransaction(1)"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Approve
                            </button>
                            <button onclick="rejectTransaction(1)"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Reject
                            </button>
                            <button onclick="reviewTransaction(1)"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                Review
                            </button>
                        </div>
                    </div>
                </li>

                <!-- Large Deposit -->
                <li class="px-4 py-6 sm:px-6" data-transaction-id="2">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <input type="checkbox" name="selected_transactions[]" value="2" class="transaction-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    <h4 class="text-sm font-medium text-gray-900">Large Deposit</h4>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Requires Review
                                    </span>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Alice Cooper</span> (alice@example.com)
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Bank Transfer • Requested: 4 hours ago
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="text-lg font-bold text-green-600">+$5,000.00</span>
                                    <span class="text-sm text-gray-500">Current Balance: $890.50</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="approveTransaction(2)"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Approve
                            </button>
                            <button onclick="rejectTransaction(2)"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Reject
                            </button>
                            <button onclick="reviewTransaction(2)"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                Review
                            </button>
                        </div>
                    </div>
                </li>

                <!-- International Transfer -->
                <li class="px-4 py-6 sm:px-6" data-transaction-id="3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <input type="checkbox" name="selected_transactions[]" value="3" class="transaction-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    <h4 class="text-sm font-medium text-gray-900">International Transfer</h4>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Compliance Check
                                    </span>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Mike Wilson</span> → <span class="font-medium">sarah.jones@example.org</span>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Cross-border transfer • Requested: 6 hours ago
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="text-lg font-bold text-blue-600">$750.00</span>
                                    <span class="text-sm text-gray-500">Note: "Family support payment"</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="approveTransaction(3)"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Approve
                            </button>
                            <button onclick="rejectTransaction(3)"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Reject
                            </button>
                            <button onclick="reviewTransaction(3)"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                Review
                            </button>
                        </div>
                    </div>
                </li>

                <!-- Suspicious Activity -->
                <li class="px-4 py-6 sm:px-6 bg-red-50" data-transaction-id="4">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <input type="checkbox" name="selected_transactions[]" value="4" class="transaction-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 bg-red-200 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L3.316 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    <h4 class="text-sm font-medium text-gray-900">Flagged Transaction</h4>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-200 text-red-800">
                                        Security Alert
                                    </span>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Unknown User</span> (suspicious@domain.com)
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Multiple failed attempts • Flagged: 1 hour ago
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="text-lg font-bold text-red-600">$10,000.00</span>
                                    <span class="text-sm text-red-600">⚠️ Potential fraud detected</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="blockTransaction(4)"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Block
                            </button>
                            <button onclick="investigateTransaction(4)"
                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Investigate
                            </button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Bulk Actions -->
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Bulk Actions</h3>
                <div class="text-sm text-gray-500">
                    <span id="selected-count">0</span> transactions selected
                </div>
            </div>
            <div class="flex flex-wrap gap-4">
                <button onclick="selectAll()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Select All
                </button>
                <button onclick="clearSelection()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Clear Selection
                </button>
                <button onclick="bulkApprove()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md text-sm font-medium" disabled id="bulk-approve-btn">
                    Approve Selected
                </button>
                <button onclick="bulkReject()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md text-sm font-medium" disabled id="bulk-reject-btn">
                    Reject Selected
                </button>
                <button onclick="exportReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                    Export Report
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Rejection Reason -->
<div id="rejection-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Rejection Reason</h3>
        <form id="rejection-form">
            @csrf
            <div class="mb-4">
                <label for="rejection-reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Please provide a reason for rejection:
                </label>
                <textarea id="rejection-reason" name="reason" rows="3" required
                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    placeholder="Enter rejection reason..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectionModal()"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Cancel
                </button>
                <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Approval Notes -->
<div id="approval-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Approval Confirmation</h3>
        <form id="approval-form">
            @csrf
            <div class="mb-4">
                <label for="approval-notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Additional notes (optional):
                </label>
                <textarea id="approval-notes" name="notes" rows="3"
                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    placeholder="Enter any additional notes..."></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeApprovalModal()"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Cancel
                </button>
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Confirm Approval
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentTransactionId = null;
let currentAction = null;

// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                  document.querySelector('input[name="_token"]')?.value;

// Show alert message
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'bg-green-100 text-green-700 border-green-400' : 'bg-red-100 text-red-700 border-red-400';

    const alert = document.createElement('div');
    alert.className = `${alertClass} border px-4 py-3 rounded mb-4 shadow-lg`;
    alert.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="float-right text-xl leading-none">&times;</button>
    `;

    alertContainer.appendChild(alert);

    // Auto remove after 5 seconds
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Approve Transaction
function approveTransaction(id) {
    currentTransactionId = id;
    currentAction = 'approve';
    document.getElementById('approval-modal').classList.remove('hidden');
    document.getElementById('approval-modal').classList.add('flex');
}

// Reject Transaction
function rejectTransaction(id) {
    currentTransactionId = id;
    currentAction = 'reject';
    document.getElementById('rejection-modal').classList.remove('hidden');
    document.getElementById('rejection-modal').classList.add('flex');
}

// Block Transaction
function blockTransaction(id) {
    const reason = prompt('Please provide a reason for blocking this transaction:');
    if (!reason) return;

    makeRequest(`/admin/transactions/${id}/block`, 'POST', { reason })
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                removeTransactionFromView(id);
            } else {
                showAlert(data.message || 'Failed to block transaction', 'error');
            }
        })
        .catch(error => {
            showAlert('Error blocking transaction: ' + error.message, 'error');
        });
}

// Investigate Transaction
function investigateTransaction(id) {
    makeRequest(`/admin/transactions/${id}/investigate`, 'POST', {})
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                updateTransactionStatus(id, 'Under Investigation');
            } else {
                showAlert(data.message || 'Failed to flag transaction for investigation', 'error');
            }
        })
        .catch(error => {
            showAlert('Error flagging transaction: ' + error.message, 'error');
        });
}

// Review Transaction (placeholder)
function reviewTransaction(id) {
    showAlert(`Transaction ${id} marked for detailed review`, 'success');
}

// Close modals
function closeApprovalModal() {
    document.getElementById('approval-modal').classList.add('hidden');
    document.getElementById('approval-modal').classList.remove('flex');
    document.getElementById('approval-form').reset();
}

function closeRejectionModal() {
    document.getElementById('rejection-modal').classList.add('hidden');
    document.getElementById('rejection-modal').classList.remove('flex');
    document.getElementById('rejection-form').reset();
}

// Handle approval form submission
document.getElementById('approval-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const notes = formData.get('notes');

    makeRequest(`/admin/transactions/${currentTransactionId}/approve`, 'POST', { notes })
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                removeTransactionFromView(currentTransactionId);
                closeApprovalModal();
            } else {
                showAlert(data.message || 'Failed to approve transaction', 'error');
            }
        })
        .catch(error => {
            showAlert('Error approving transaction: ' + error.message, 'error');
        });
});

// Handle rejection form submission
document.getElementById('rejection-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const reason = formData.get('reason');

    makeRequest(`/admin/transactions/${currentTransactionId}/reject`, 'POST', { reason })
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                removeTransactionFromView(currentTransactionId);
                closeRejectionModal();
            } else {
                showAlert(data.message || 'Failed to reject transaction', 'error');
            }
        })
        .catch(error => {
            showAlert('Error rejecting transaction: ' + error.message, 'error');
        });
});

// Bulk Actions
function selectAll() {
    const checkboxes = document.querySelectorAll('.transaction-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    updateSelectedCount();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.transaction-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    updateSelectedCount();
}

function bulkApprove() {
    const selected = getSelectedTransactions();
    if (selected.length === 0) {
        showAlert('Please select transactions to approve', 'error');
        return;
    }

    if (confirm(`Are you sure you want to approve ${selected.length} transactions?`)) {
        makeRequest('/admin/transactions/bulk-approval', 'POST', {
            transaction_ids: selected,
            action: 'approve'
        })
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                selected.forEach(id => removeTransactionFromView(id));
                clearSelection();
            } else {
                showAlert(data.message || 'Failed to bulk approve', 'error');
            }
        })
        .catch(error => {
            showAlert('Error with bulk approval: ' + error.message, 'error');
        });
    }
}

function bulkReject() {
    const selected = getSelectedTransactions();
    if (selected.length === 0) {
        showAlert('Please select transactions to reject', 'error');
        return;
    }

    const reason = prompt('Please provide a reason for rejecting these transactions:');
    if (!reason) return;

    makeRequest('/admin/transactions/bulk-approval', 'POST', {
        transaction_ids: selected,
        action: 'reject',
        notes: reason
    })
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            selected.forEach(id => removeTransactionFromView(id));
            clearSelection();
        } else {
            showAlert(data.message || 'Failed to bulk reject', 'error');
        }
    })
    .catch(error => {
        showAlert('Error with bulk rejection: ' + error.message, 'error');
    });
}

function exportReport() {
    makeRequest('/admin/transactions/export-report', 'POST', {})
        .then(data => {
            if (data.success) {
                showAlert('Report generated successfully! Download will begin shortly.', 'success');
                // In a real implementation, you would trigger a download here
            } else {
                showAlert(data.message || 'Failed to generate report', 'error');
            }
        })
        .catch(error => {
            showAlert('Error generating report: ' + error.message, 'error');
        });
}

// Utility functions
function getSelectedTransactions() {
    const checkboxes = document.querySelectorAll('.transaction-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function updateSelectedCount() {
    const selected = getSelectedTransactions();
    document.getElementById('selected-count').textContent = selected.length;

    const bulkApproveBtn = document.getElementById('bulk-approve-btn');
    const bulkRejectBtn = document.getElementById('bulk-reject-btn');

    if (selected.length > 0) {
        bulkApproveBtn.disabled = false;
        bulkRejectBtn.disabled = false;
        bulkApproveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        bulkRejectBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        bulkApproveBtn.disabled = true;
        bulkRejectBtn.disabled = true;
        bulkApproveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        bulkRejectBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

function removeTransactionFromView(id) {
    const transactionElement = document.querySelector(`[data-transaction-id="${id}"]`);
    if (transactionElement) {
        transactionElement.remove();
    }
}

function updateTransactionStatus(id, status) {
    const transactionElement = document.querySelector(`[data-transaction-id="${id}"]`);
    if (transactionElement) {
        const statusElement = transactionElement.querySelector('.text-sm.text-gray-500:last-child');
        if (statusElement) {
            statusElement.textContent = status;
        }
    }
}

// Make API request
function makeRequest(url, method, data = {}) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    });
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add change listeners to checkboxes
    const checkboxes = document.querySelectorAll('.transaction-checkbox');
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    // Initialize button states
    updateSelectedCount();
});
</script>
@endsection