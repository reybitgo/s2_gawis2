@extends('layouts.admin')

@section('title', 'Application Settings')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-2">Application Settings</h1>
                    <p class="text-muted">Configure application-wide settings</p>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- E-Commerce Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                            </svg>
                            E-Commerce Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate</label>
                            <div class="input-group">
                                <input type="number"
                                       class="form-control @error('tax_rate') is-invalid @enderror"
                                       id="tax_rate"
                                       name="tax_rate"
                                       value="{{ old('tax_rate', $settings['tax_rate']) }}"
                                       step="0.001"
                                       min="0"
                                       max="1"
                                       required>
                                <span class="input-group-text">
                                    <span id="tax_percentage">{{ number_format($settings['tax_rate'] * 100, 1) }}%</span>
                                </span>
                            </div>
                            @error('tax_rate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Enter as decimal (e.g., 0.07 for 7%, 0.085 for 8.5%). This will be applied to all cart purchases.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Currency Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                            </svg>
                            Currency Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                <input type="text"
                                       class="form-control @error('currency_symbol') is-invalid @enderror"
                                       id="currency_symbol"
                                       name="currency_symbol"
                                       value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '₱') }}"
                                       maxlength="5"
                                       required>
                                @error('currency_symbol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Symbol displayed before amounts (e.g., ₱, $, €, £, ¥)
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Preview: <strong><span id="currency_preview">{{ $settings['currency_symbol'] ?? '₱' }}</span>1,234.56</strong></small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="currency" class="form-label">Currency Code</label>
                                <select class="form-select @error('currency') is-invalid @enderror"
                                        id="currency"
                                        name="currency"
                                        required>
                                    <option value="PHP" {{ ($settings['currency'] ?? 'PHP') === 'PHP' ? 'selected' : '' }}>PHP - Philippine Peso</option>
                                    <option value="USD" {{ ($settings['currency'] ?? 'PHP') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ ($settings['currency'] ?? 'PHP') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ ($settings['currency'] ?? 'PHP') === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="JPY" {{ ($settings['currency'] ?? 'PHP') === 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                    <option value="AUD" {{ ($settings['currency'] ?? 'PHP') === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                    <option value="CAD" {{ ($settings['currency'] ?? 'PHP') === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                    <option value="SGD" {{ ($settings['currency'] ?? 'PHP') === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    ISO currency code for the system
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            <strong>Note:</strong> Changing currency affects all prices, wallet balances, and transactions displayed throughout the system. This is for display purposes only and does not convert existing monetary values.
                        </div>
                    </div>
                </div>

                <!-- Authentication Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                            </svg>
                            Authentication Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="email_verification_required"
                                   name="email_verification_required"
                                   value="1"
                                   {{ $settings['email_verification_required'] ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_verification_required">
                                Require Email Verification After Registration
                            </label>
                            <div class="form-text">
                                When enabled, new users must verify their email address before they can login and access the application. When disabled, users can login immediately after registration.
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            <strong>Note:</strong> This setting is separate from the <strong>"Email Verification"</strong> setting in
                            <a href="{{ route('admin.system.settings') }}#security" class="alert-link">System Settings</a>,
                            which controls whether users need to re-verify their email on each login session.
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                        </svg>
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                        </svg>
                        Settings Information
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="card-title">Tax Rate</h6>
                    <p class="card-text small">
                        The tax rate is applied to all e-commerce purchases in the shopping cart.
                        Changes take effect immediately for new cart calculations.
                    </p>

                    <h6 class="card-title mt-3">Email Verification After Registration</h6>
                    <p class="card-text small">
                        Controls whether new user registrations require email verification before
                        users can login for the first time. When disabled, users can access the
                        application immediately after registration.
                    </p>

                    <h6 class="card-title mt-3">Difference from System Settings</h6>
                    <p class="card-text small">
                        <strong>Application Settings</strong> (this page): Controls verification requirement
                        for new registrations only.<br>
                        <strong>System Settings</strong>: Controls ongoing email verification requirements
                        for existing users during login sessions.
                    </p>

                    <div class="alert alert-warning mt-3">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        <strong>Note:</strong> Changes to these settings affect all users and take effect immediately.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const taxRateInput = document.getElementById('tax_rate');
    const taxPercentageSpan = document.getElementById('tax_percentage');
    const currencySymbolInput = document.getElementById('currency_symbol');
    const currencyPreviewSpan = document.getElementById('currency_preview');
    const currencySelect = document.getElementById('currency');

    // Tax rate preview
    taxRateInput.addEventListener('input', function() {
        const percentage = (parseFloat(this.value) * 100).toFixed(1);
        taxPercentageSpan.textContent = percentage + '%';
    });

    // Currency symbol preview
    currencySymbolInput.addEventListener('input', function() {
        currencyPreviewSpan.textContent = this.value || '₱';
    });

    // Auto-update currency symbol when currency code changes
    currencySelect.addEventListener('change', function() {
        const currencySymbols = {
            'PHP': '₱',
            'USD': '$',
            'EUR': '€',
            'GBP': '£',
            'JPY': '¥',
            'AUD': '$',
            'CAD': '$',
            'SGD': '$'
        };

        const symbol = currencySymbols[this.value] || '$';
        currencySymbolInput.value = symbol;
        currencyPreviewSpan.textContent = symbol;
    });
});
</script>
@endpush
@endsection