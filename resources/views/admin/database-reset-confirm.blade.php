@extends('layouts.simple')

@section('title', 'Database Reset Confirmation')

@section('content')
<div class="container-lg px-4 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="card-title mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        Database Reset Confirmation
                    </h4>
                </div>

                <div class="card-body">
                    <div class="alert alert-danger d-flex align-items-center mb-4">
                        <svg class="icon me-3 flex-shrink-0">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        <div>
                            <strong>WARNING:</strong> This action will permanently delete all data and cannot be undone!
                        </div>
                    </div>

                    <h5 class="text-danger mb-3">What will happen:</h5>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-warning me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-trash') }}"></use>
                            </svg>
                            Non-default user accounts will be deleted (admin & member preserved)
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-danger me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                            </svg>
                            All transactions will be cleared (wallet balances reset to initial amounts)
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-danger me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                            </svg>
                            All orders, order items, and order history will be cleared
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-danger me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-action-undo') }}"></use>
                            </svg>
                            All return requests and refund history will be cleared
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-info me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-file') }}"></use>
                            </svg>
                            All log files will be&nbsp;<strong>cleared</strong>&nbsp;(fresh logs for debugging)
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-info me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                            </svg>
                            All system caches will be&nbsp;<strong>cleared</strong>&nbsp;(config, routes, views, application)
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-success me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                            </svg>
                            System settings will be&nbsp;<strong>preserved</strong>&nbsp;(fees, email verification)
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-success me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-applications-settings') }}"></use>
                            </svg>
                            Application settings will be&nbsp;<strong>preserved</strong>&nbsp;(tax rate, email verification)
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-success me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                            </svg>
                            Roles and permissions will be&nbsp;<strong>preserved</strong>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-success me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                            </svg>
                            Default admin and member accounts will be refreshed with&nbsp;<strong>complete profiles</strong>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-success me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-gift') }}"></use>
                            </svg>
                            Sample Packages (MLM) and Products (Unilevel) will be&nbsp;<strong>restored</strong>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <svg class="icon text-success me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-task') }}"></use>
                            </svg>
                            MLM and Unilevel bonus settings will be&nbsp;<strong>restored</strong>
                        </li>
                    </ul>

                    @if($last_reset || $reset_count > 0)
                    <div class="alert alert-info">
                        <h6>Reset History:</h6>
                        @if($last_reset)
                            <p class="mb-1"><strong>Last Reset:</strong> {{ \Carbon\Carbon::parse($last_reset)->format('F j, Y g:i A') }}</p>
                        @endif
                        <p class="mb-0"><strong>Total Resets:</strong> {{ $reset_count }}</p>
                    </div>
                    @endif

                    <div class="alert alert-success">
                        <h6>Default Credentials After Reset:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Admin Account:</strong><br>
                                Email: admin@gawisherbal.com<br>
                                Password: Admin123!@#<br>
                                Balance: {{ currency(1000) }} (Purchase)<br>
                                <small class="text-muted">Complete profile with address</small>
                            </div>
                            <div class="col-md-6">
                                <strong>Member Account:</strong><br>
                                Email: member@gawisherbal.com<br>
                                Password: Member123!@#<br>
                                Balance: {{ currency(1000) }} (Purchase)<br>
                                <small class="text-muted">Complete delivery address + MLM referral code</small>
                            </div>
                        </div>
                    </div>

                    <form id="resetForm" method="GET" action="{{ route('database.reset') }}">
                        <input type="hidden" name="confirm" value="yes">

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirmCheck" required>
                            <label class="form-check-label text-danger" for="confirmCheck">
                                <strong>I understand that this action will permanently delete all data and cannot be undone</strong>
                            </label>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-danger" id="confirmButton" disabled data-coreui-toggle="modal" data-coreui-target="#resetConfirmModal">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                                </svg>
                                Reset Database
                            </button>

                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                                </svg>
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Final Confirmation Modal -->
<div class="modal fade" id="resetConfirmModal" tabindex="-1" aria-labelledby="resetConfirmModalLabel" aria-hidden="true" data-coreui-backdrop="static" data-coreui-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <div class="d-flex align-items-center">
                    <svg class="icon icon-lg me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    <h5 class="modal-title" id="resetConfirmModalLabel">Final Confirmation Required</h5>
                </div>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <svg class="icon text-danger" style="width: 50px; height: 50px;">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                        </svg>
                    </div>
                </div>

                <h5 class="text-center text-danger mb-3">Are you absolutely sure?</h5>
                <p class="text-center text-body-secondary mb-4">
                    This action will <strong>permanently delete ALL data</strong> and cannot be undone!
                </p>

                <div class="alert alert-danger mb-4">
                    <h6 class="alert-heading mb-2">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        Data that will be lost:
                    </h6>
                    <ul class="mb-0">
                        <li>Non-default user accounts and profiles (admin & member preserved)</li>
                        <li>All transaction history and records</li>
                        <li>All orders and order items (complete order history with 26-status lifecycle)</li>
                        <li>All return requests and refund history</li>
                        <li>All order status histories and timeline notes</li>
                        <li>Non-default wallet balances (default wallets reset to initial amounts)</li>
                        <li>All log files and debugging information</li>
                        <li>All cached data (config, routes, views, application cache)</li>
                    </ul>
                </div>

                <div class="alert alert-info mb-4">
                        <h6>
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                        </svg>
                        What will be restored:
                    </h6>
                    <ul class="mb-0">
                        <li>Default admin account (admin@gawisherbal.com) with <strong>complete profile</strong></li>
                        <li>Default member account (member@gawisherbal.com) with <strong>complete delivery address</strong></li>
                        <li><strong>Current system settings preserved</strong> (fees, email verification)</li>
                        <li><strong>Current application settings preserved</strong> (tax rate, email verification after registration)</li>
                        <li><strong>All roles and permissions preserved</strong></li>
                        <li><strong>Sample Packages (MLM) and Products (Unilevel) restored</strong> to initial state</li>
                        <li><strong>MLM (for Packages) and Unilevel (for Products) settings restored</strong></li>
                        <li>Fresh wallets with initial balances ($1,000 & $100) and segregated MLM/Purchase balances</li>
                        <li>Clean e-commerce system ready for new orders (26-status order lifecycle)</li>
                        <li>Return & refund system ready for testing (7-day return window)</li>
                        <li><strong>Fresh log files for clean debugging</strong></li>
                        <li><strong>Optimized performance with cleared caches</strong></li>
                    </ul>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
                    </svg>
                    Cancel
                </button>
                <button type="button" class="btn btn-danger" id="finalResetButton">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                    </svg>
                    Yes, Reset Database
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing -->
<div class="pb-5"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmCheck = document.getElementById('confirmCheck');
    const confirmButton = document.getElementById('confirmButton');
    const finalResetButton = document.getElementById('finalResetButton');
    const resetForm = document.getElementById('resetForm');

    // Enable/disable first confirmation button based on checkbox
    confirmCheck.addEventListener('change', function() {
        confirmButton.disabled = !this.checked;
    });

    // Handle final reset confirmation (no checkbox needed in modal)
    finalResetButton.addEventListener('click', function() {
        // Show loading state on final button
        this.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Resetting Database...
        `;
        this.disabled = true;

        // Disable modal close buttons
        document.querySelector('#resetConfirmModal [data-coreui-dismiss="modal"]').disabled = true;

        // Also show loading state on original button
        confirmButton.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Processing...
        `;
        confirmButton.disabled = true;

        // Disable cancel button
        document.querySelector('a[href*="dashboard"]').classList.add('disabled');

        // Submit the form
        resetForm.submit();
    });

    // Reset modal state when modal is closed
    document.getElementById('resetConfirmModal').addEventListener('hidden.coreui.modal', function() {
        finalResetButton.innerHTML = `
            <svg class="icon me-2">
                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
            </svg>
            Yes, Reset Database
        `;
    });
});
</script>
@endpush
@endsection