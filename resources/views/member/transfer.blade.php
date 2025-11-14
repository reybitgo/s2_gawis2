@extends('layouts.admin')

@section('title', 'Transfer Funds')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                    </svg>
                    Transfer Funds
                </h4>
                <p class="text-body-secondary mb-0">Send funds to another user</p>
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

<!-- Current Balance Card -->
<div class="row mb-4">
    <div class="col-md-6 mx-auto">
        <div class="card bg-success-gradient text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Available Balance (Transferable)</h5>
                <h2 class="display-4 fw-bold">{{ currency($wallet->purchase_balance) }}</h2>
                <p class="mb-0">
                    <span class="badge {{ $wallet->is_active ? 'bg-light text-success' : 'bg-danger' }}">
                        {{ $wallet->is_active ? 'Account Active' : 'Account Frozen' }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Recipients -->
@if($frequentRecipients->count() > 0)
<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-people') }}"></use>
                </svg>
                <strong>Frequent Recipients</strong>
                <small class="text-body-secondary ms-auto">Your most frequent transfer recipients</small>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    @foreach($frequentRecipients as $recipient)
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="setRecipient('{{ $recipient->username ?: $recipient->email }}')"
                                title="Transferred {{ $recipient->transfer_count }} time(s) - Last: {{ \Carbon\Carbon::parse($recipient->last_transfer_at)->diffForHumans() }}">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                            </svg>
                            {{ $recipient->fullname ?: ($recipient->username ?: $recipient->email) }}
                            <span class="badge bg-primary ms-1">{{ $recipient->transfer_count }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="text-center mt-2">
                    <small class="text-body-secondary">Click to quickly select a recipient • Numbers show transfer count</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Quick Amount Buttons -->
<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                </svg>
                <strong>Quick Amounts</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    @foreach([10, 25, 50, 100, 250, 500, 1000] as $quickAmount)
                        @if($wallet->purchase_balance >= $quickAmount)
                            <button type="button" class="btn btn-outline-primary" onclick="setAmount({{ $quickAmount }})">
                                {{ currency_symbol() }}{{ $quickAmount }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
{{-- @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
        </svg>
        {{ session('success') }}
        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
    </div>
@endif --}}

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x') }}"></use>
        </svg>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
    </div>
@endif

<!-- Transfer Form -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-horizontal') }}"></use>
                </svg>
                <strong>Transfer Form</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('wallet.transfer.process') }}" id="transfer-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="recipient_identifier" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-envelope-open') }}"></use>
                                    </svg>
                                    Recipient Email or Username
                                </label>
                                <input type="text" name="recipient_identifier" id="recipient_identifier" class="form-control"
                                       placeholder="recipient@example.com or username" required
                                       value="{{ old('recipient_identifier') }}">
                                <div class="form-text">
                                    Enter the email address or username of the recipient
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-right') }}"></use>
                                    </svg>
                                    Transfer Amount
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ currency_symbol() }}</span>
                                    <input type="number" name="amount" id="amount" class="form-control"
                                           placeholder="0.00" min="1" max="{{ min($wallet->purchase_balance, 10000) }}" step="0.01" required
                                           value="{{ old('amount') }}">
                                    <span class="input-group-text">{{ currency_code() }}</span>
                                </div>
                                <div class="form-text">
                                    Minimum: {{ currency(1) }} | Maximum: {{ currency(min($wallet->purchase_balance, 10000)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-notes') }}"></use>
                            </svg>
                            Transfer Note (Optional)
                        </label>
                        <textarea name="note" id="note" rows="3" class="form-control"
                                  placeholder="What's this transfer for?" maxlength="255">{{ old('note') }}</textarea>
                        <div class="form-text">
                            Add a note to help identify this transfer (optional)
                        </div>
                    </div>

                    <!-- Transfer Summary -->
                    <div id="transfer-fee-info" class="card bg-info-subtle border-info mb-3 d-none">
                        <div class="card-body">
                            <h6 class="card-title">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-calculator') }}"></use>
                                </svg>
                                Transfer Summary
                            </h6>
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="text-body-secondary small">Transfer Amount</div>
                                    <div class="fw-bold" id="transfer-amount-display">{{ currency_symbol() }}0.00</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-body-secondary small">Transfer Fee</div>
                                    <div class="fw-bold text-warning" id="transfer-fee-display">{{ currency_symbol() }}0.00</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-body-secondary small">Total Deducted</div>
                                    <div class="fw-bold text-primary" id="total-amount-display">{{ currency_symbol() }}0.00</div>
                                </div>
                            </div>
                            <hr>
                            <p class="small mb-0 text-info">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                </svg>
                                <strong>Note:</strong> Transfer will be processed instantly once confirmed.
                            </p>
                        </div>
                    </div>

                    <!-- Important Information -->
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                            </svg>
                            Important Information
                        </h6>
                        <ul class="mb-0">
                            <li>Transfers are processed instantly upon confirmation</li>
                            <li>Please verify the recipient email or username carefully</li>
                            <li>Transfer fees are deducted from your wallet immediately</li>
                            <li>Completed transfers cannot be reversed</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-success btn-lg flex-md-fill"
                                {{ !$wallet->is_active || $wallet->purchase_balance <= 0 ? 'disabled' : '' }}>
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-send') }}"></use>
                            </svg>
                            Send Transfer
                        </button>
                        <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-secondary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                            </svg>
                            View Transactions
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@endsection

@push('scripts')
<script>
    // Transfer settings from server
    const transferSettings = @json($transferSettings);

    // Debug: Log transfer settings to console
    console.log('Transfer Settings:', transferSettings);

    function setRecipient(email) {
        document.getElementById('recipient_identifier').value = email;
        document.getElementById('recipient_identifier').focus();
    }

    function setAmount(amount) {
        document.getElementById('amount').value = amount;
        updateTransferSummary();
    }

    function calculateTransferFee(amount) {
        // These values are loaded from system settings
        const chargeEnabled = transferSettings.charge_enabled;
        const chargeType = transferSettings.charge_type;
        const chargeValue = transferSettings.charge_value;
        const minCharge = transferSettings.minimum_charge;
        const maxCharge = transferSettings.maximum_charge;

        if (!chargeEnabled || amount <= 0) {
            return 0;
        }

        let charge;
        if (chargeType === 'percentage') {
            charge = (amount * chargeValue) / 100;
        } else {
            charge = chargeValue;
        }

        // Apply minimum limit
        charge = Math.max(charge, minCharge);

        // Apply maximum limit (0 means no limit)
        if (maxCharge > 0) {
            charge = Math.min(charge, maxCharge);
        }

        return Math.round(charge * 100) / 100; // Round to 2 decimal places
    }

    function updateTransferSummary() {
        const amountInput = document.getElementById('amount');
        const amount = parseFloat(amountInput.value) || 0;

        if (amount > 0) {
            const fee = calculateTransferFee(amount);
            const total = amount + fee;

            document.getElementById('transfer-amount-display').textContent = '{{ currency_symbol() }}' + amount.toFixed(2);
            document.getElementById('transfer-fee-display').textContent = '{{ currency_symbol() }}' + fee.toFixed(2);
            document.getElementById('total-amount-display').textContent = '{{ currency_symbol() }}' + total.toFixed(2);
            document.getElementById('transfer-fee-info').classList.remove('d-none');
        } else {
            document.getElementById('transfer-fee-info').classList.add('d-none');
        }
    }

    // Add event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('amount');
        amountInput.addEventListener('input', updateTransferSummary);
        amountInput.addEventListener('change', updateTransferSummary);

    });
</script>
@endpush