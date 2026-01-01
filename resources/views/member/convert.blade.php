@extends('layouts.admin')

@section('title', 'Convert Balance')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-vertical') }}"></use>
                    </svg>
                    Convert Balance
                </h4>
                <p class="text-body-secondary mb-0">Convert your withdrawable earnings to Purchase Balance</p>
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

<!-- Current Balance Card -->
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card bg-success-gradient text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Network Balance</h5>
                <h2 class="display-4 fw-bold">{{ currency($wallet->available_for_withdrawal) }}</h2>
                <p class="mb-0 small">Available for conversion</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-primary-gradient text-white">
            <div class="card-body text-center">
                <h5 class="card-title">Purchase Balance</h5>
                <h2 class="display-4 fw-bold">{{ currency($wallet->purchase_balance) }}</h2>
                <p class="mb-0 small">Can be transferred to other users</p>
            </div>
        </div>
    </div>
</div>

<!-- Conversion Info Banner -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h6 class="alert-heading">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                </svg>
                Why Convert Balance?
            </h6>
            <p class="mb-0">
                <strong>Network Balance</strong> (your total MLM and Unilevel earnings) is withdrawable.
                Convert it to <strong>Purchase Balance</strong> to transfer funds to other users within the system.
            </p>
            <hr class="my-2">
            <p class="mb-0 small">
                <svg class="icon me-1">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                </svg>
                Conversions are instant and free with no fees
            </p>
        </div>
    </div>
</div>

<!-- Quick Amount Buttons -->
@if($wallet->available_for_withdrawal > 0)
<div class="row mb-4">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                </svg>
                <strong>Quick Amounts</strong>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    @foreach([10, 25, 50, 100, 250, 500] as $quickAmount)
                        @if($wallet->available_for_withdrawal >= $quickAmount)
                            <button type="button" class="btn btn-outline-primary" onclick="setAmount({{ $quickAmount }})">
                                {{ currency_symbol() }}{{ $quickAmount }}
                            </button>
                        @endif
                    @endforeach
                    @if($wallet->available_for_withdrawal > 0)
                        <button type="button" class="btn btn-outline-success" onclick="setAmount({{ $wallet->available_for_withdrawal }})">
                            <svg class="icon me-1">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-layers') }}"></use>
                            </svg>
                            All ({{ currency($wallet->available_for_withdrawal) }})
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Alert Messages -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <svg class="icon me-2">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
        </svg>
        {{ session('success') }}
        <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
    </div>
@endif

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

<!-- Convert Form -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-swap-vertical') }}"></use>
                </svg>
                <strong>Conversion Form</strong>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('wallet.convert.process') }}" id="convert-form">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="amount" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                                    </svg>
                                    Conversion Amount
                                </label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">{{ currency_symbol() }}</span>
                                    <input type="number" name="amount" id="amount" class="form-control"
                                           placeholder="0.00" min="1" max="{{ min($wallet->available_for_withdrawal, 10000) }}" step="0.01" required
                                           value="{{ old('amount') }}">
                                    <span class="input-group-text">{{ currency_code() }}</span>
                                </div>
                                <div class="form-text">
                                    Minimum: {{ currency(1) }} | Maximum: {{ currency(min($wallet->available_for_withdrawal, 10000)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conversion Summary -->
                    <div id="conversion-summary" class="card bg-success-subtle border-success mb-3 d-none">
                        <div class="card-body">
                            <h6 class="card-title">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-calculator') }}"></use>
                                </svg>
                                Conversion Summary
                            </h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="text-body-secondary small">From Network Balance</div>
                                    <div class="fw-bold text-danger" id="from-amount-display">{{ currency_symbol() }}0.00</div>
                                    <small class="text-muted">Will be deducted</small>
                                </div>
                                <div class="col-6">
                                    <div class="text-body-secondary small">To Purchase Balance</div>
                                    <div class="fw-bold text-success" id="to-amount-display">{{ currency_symbol() }}0.00</div>
                                    <small class="text-muted">Will be added</small>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <div class="badge bg-success">
                                    <svg class="icon me-1">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-gift') }}"></use>
                                    </svg>
                                    No Conversion Fee - 100% of amount transferred
                                </div>
                            </div>
                            <p class="small mb-0 mt-2 text-success text-center">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-bolt') }}"></use>
                                </svg>
                                <strong>Instant:</strong> Conversion will be processed immediately.
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
                            <li>Conversions are processed instantly with no fees</li>
                            <li>Your Withdrawable Balance will be decreased by the converted amount</li>
                            <li>Your Purchase Balance will be increased by the same amount</li>
                            <li>After conversion, you can transfer the funds to other users</li>
                            <li>Completed conversions cannot be reversed</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-success btn-lg flex-md-fill"
                                {{ !$wallet->is_active || $wallet->available_for_withdrawal <= 0 ? 'disabled' : '' }}>
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                            </svg>
                            Convert Balance
                        </button>
                        <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-secondary btn-lg">
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
    function setAmount(amount) {
        document.getElementById('amount').value = amount;
        updateConversionSummary();
    }

    function updateConversionSummary() {
        const amountInput = document.getElementById('amount');
        const amount = parseFloat(amountInput.value) || 0;

        if (amount > 0) {
            document.getElementById('from-amount-display').textContent = '{{ currency_symbol() }}' + amount.toFixed(2);
            document.getElementById('to-amount-display').textContent = '{{ currency_symbol() }}' + amount.toFixed(2);
            document.getElementById('conversion-summary').classList.remove('d-none');
        } else {
            document.getElementById('conversion-summary').classList.add('d-none');
        }
    }

    // Add event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('amount');
        amountInput.addEventListener('input', updateConversionSummary);
        amountInput.addEventListener('change', updateConversionSummary);
    });
</script>
@endpush
