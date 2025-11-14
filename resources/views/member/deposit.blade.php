@extends('layouts.admin')

@section('title', 'Deposit Funds')

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
                    Deposit Funds
                </h4>
                <p class="text-body-secondary mb-0">Add money to your e-wallet</p>
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
        <div class="card bg-primary-gradient text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Current Balance</h5>
                <h2 class="display-4 fw-bold">{{ currency(auth()->user()->wallet ? auth()->user()->wallet->total_balance : 0) }}</h2>
                <p class="mb-0">Available for withdrawal and transfers</p>
            </div>
        </div>
    </div>
</div>

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
                    <button type="button" class="btn btn-outline-primary" onclick="setAmount(25)">{{ currency_symbol() }}25</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setAmount(50)">{{ currency_symbol() }}50</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setAmount(100)">{{ currency_symbol() }}100</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setAmount(250)">{{ currency_symbol() }}250</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setAmount(500)">{{ currency_symbol() }}500</button>
                    <button type="button" class="btn btn-outline-primary" onclick="setAmount(1000)">{{ currency_symbol() }}1,000</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deposit Form -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                </svg>
                <strong>Deposit Form</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('wallet.deposit.process') }}" id="deposit-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-thick-bottom') }}"></use>
                                    </svg>
                                    Deposit Amount
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ currency_symbol() }}</span>
                                    <input type="number" name="amount" id="amount" class="form-control"
                                           placeholder="0.00" min="1" max="10000" step="0.01" required
                                           value="{{ old('amount') }}">
                                    <span class="input-group-text">{{ currency_code() }}</span>
                                </div>
                                <div class="form-text">
                                    Minimum: {{ currency(1) }} | Maximum: {{ currency(10000) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                                    </svg>
                                    Payment Method
                                </label>
                                <select id="payment_method" name="payment_method" class="form-select" required>
                                    <option value="">Select Payment Method</option>
                                    @if($paymentMethods['gcash_enabled'])
                                        <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>
                                            Gcash
                                        </option>
                                    @endif
                                    @if($paymentMethods['maya_enabled'])
                                        <option value="maya" {{ old('payment_method') == 'maya' ? 'selected' : '' }}>
                                            Maya
                                        </option>
                                    @endif
                                    @if($paymentMethods['cash_enabled'])
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>
                                            Cash
                                        </option>
                                    @endif
                                    @if($paymentMethods['others_enabled'])
                                        <option value="others" {{ old('payment_method') == 'others' ? 'selected' : '' }}>
                                            Others
                                        </option>
                                    @endif
                                </select>
                                <input type="text" id="custom_payment_method" class="form-control d-none"
                                       placeholder="Please type payment method" value="{{ old('payment_method') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Gcash Information -->
                    @if($paymentMethods['gcash_enabled'] && $paymentMethods['gcash_number'])
                    <div id="gcash_info" class="alert alert-info d-none mb-3">
                        <div class="d-flex align-items-start">
                            <svg class="icon me-2 flex-shrink-0">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-mobile') }}"></use>
                            </svg>
                            <div>
                                <h6 class="alert-heading">Gcash Payment Information</h6>
                                <p class="mb-1"><strong>Gcash Number:</strong> {{ $paymentMethods['gcash_number'] }}</p>
                                @if($paymentMethods['gcash_name'])
                                    <p class="mb-1"><strong>Account Name:</strong> {{ $paymentMethods['gcash_name'] }}</p>
                                @endif
                                <p class="mb-0 small text-muted">Send your deposit to this Gcash number and paste your Gcash notification in the field below for faster approval.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Maya Information -->
                    @if($paymentMethods['maya_enabled'] && $paymentMethods['maya_number'])
                    <div id="maya_info" class="alert alert-success d-none mb-3">
                        <div class="d-flex align-items-start">
                            <svg class="icon me-2 flex-shrink-0">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                            </svg>
                            <div>
                                <h6 class="alert-heading">Maya Payment Information</h6>
                                <p class="mb-1"><strong>Maya Number:</strong> {{ $paymentMethods['maya_number'] }}</p>
                                @if($paymentMethods['maya_name'])
                                    <p class="mb-1"><strong>Account Name:</strong> {{ $paymentMethods['maya_name'] }}</p>
                                @endif
                                <p class="mb-0 small text-muted">Send your deposit to this Maya number and paste your Maya notification in the field below for faster approval.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-file') }}"></use>
                            </svg>
                            Payment Notification / Reference
                            <span class="badge bg-success ms-2">Recommended for Fast Approval</span>
                        </label>
                        <textarea id="description" name="description" class="form-control" rows="6"
                                  placeholder="Paste your complete payment notification here (including reference number, amount, date & time)...&#10;&#10;Example:&#10;You sent ₱100.00 to John Doe&#10;Reference No: 1234567890&#10;Date & Time: Jan 15, 2025 10:30 AM&#10;&#10;Including this information helps us process your deposit faster!">{{ old('description') }}</textarea>
                        <div class="form-text">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lightbulb') }}"></use>
                            </svg>
                            <strong>Pro Tip:</strong> Paste your complete Gcash/Maya payment notification here (including reference number) for instant verification and faster approval! Without this, processing may take longer as admin needs to manually verify your payment.
                        </div>
                    </div>

                    <!-- Important Information Alert -->
                    <div class="alert alert-warning mb-3">
                        <h6 class="alert-heading">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                            </svg>
                            Speed Up Your Deposit Approval
                        </h6>
                        <ul class="mb-0 small">
                            <li><strong>With Payment Notification:</strong> Your deposit can be approved within minutes! Admin just needs to verify the reference number.</li>
                            <li><strong>Without Payment Notification:</strong> Processing may take several hours as admin needs to manually check all incoming payments.</li>
                            <li><strong>What to Include:</strong> Reference number, amount sent, date & time, and sender name.</li>
                        </ul>
                    </div>




                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                            </svg>
                            Submit Deposit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function setAmount(amount) {
    document.getElementById('amount').value = amount;
}

function handlePaymentMethodChange() {
    const select = document.getElementById('payment_method');
    const input = document.getElementById('custom_payment_method');
    const gcashInfo = document.getElementById('gcash_info');
    const mayaInfo = document.getElementById('maya_info');

    // Hide all payment info cards
    if (gcashInfo) gcashInfo.classList.add('d-none');
    if (mayaInfo) mayaInfo.classList.add('d-none');

    if (select.value === 'others') {
        // Hide select, show input
        select.classList.add('d-none');
        select.removeAttribute('name');
        select.removeAttribute('required');
        input.classList.remove('d-none');
        input.setAttribute('name', 'payment_method');
        input.setAttribute('required', 'required');
        input.focus();
    } else {
        // Show select, hide input
        select.classList.remove('d-none');
        select.setAttribute('name', 'payment_method');
        select.setAttribute('required', 'required');
        input.classList.add('d-none');
        input.removeAttribute('name');
        input.removeAttribute('required');
        input.value = '';

        // Show payment info based on selection
        if (select.value === 'gcash' && gcashInfo) {
            gcashInfo.classList.remove('d-none');
        } else if (select.value === 'maya' && mayaInfo) {
            mayaInfo.classList.remove('d-none');
        }
    }
}

// Add event listener for payment method change
document.getElementById('payment_method').addEventListener('change', handlePaymentMethodChange);

// Handle case where "others" is pre-selected (from old input)
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('payment_method');
    const input = document.getElementById('custom_payment_method');

    // If old input has a custom value that's not in the select options
    if (input.value && !['gcash', 'maya', 'cash', 'others', ''].includes(input.value)) {
        select.value = 'others';
        handlePaymentMethodChange();
    }
});
</script>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@endsection