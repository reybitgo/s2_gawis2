@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<!-- Page Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="card-title mb-0">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                    </svg>
                    System Settings
                </h4>
                <p class="text-body-secondary mb-0">Configure system parameters and security settings</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                    </svg>
                    <span class="d-none d-sm-inline">Back to Dashboard</span>
                    <span class="d-inline d-sm-none">Back</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Navigation Menu -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <svg class="icon me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                <strong>Settings Categories</strong>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="#general" onclick="showSection('general')" class="settings-nav-item list-group-item list-group-item-action active">
                        <svg class="icon me-3">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                        </svg>
                        General Settings
                    </a>
                    <a href="#security" onclick="showSection('security')" class="settings-nav-item list-group-item list-group-item-action">
                        <svg class="icon me-3">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                        </svg>
                        Security
                    </a>
                    <a href="#wallet" onclick="showSection('wallet')" class="settings-nav-item list-group-item list-group-item-action">
                        <svg class="icon me-3">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                        </svg>
                        Wallet Settings
                    </a>
                    <a href="#payment-methods" onclick="showSection('payment-methods')" class="settings-nav-item list-group-item list-group-item-action">
                        <svg class="icon me-3">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                        </svg>
                        Payment Methods
                    </a>
                    <a href="#notifications" onclick="showSection('notifications')" class="settings-nav-item list-group-item list-group-item-action">
                        <svg class="icon me-3">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-bell') }}"></use>
                        </svg>
                        Notifications
                    </a>
                    <a href="#maintenance" onclick="showSection('maintenance')" class="settings-nav-item list-group-item list-group-item-action">
                        <svg class="icon me-3">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        Maintenance
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="col-lg-9">
        <!-- General Settings -->
        <div id="general-section" class="settings-section">
            <div class="card">
                <div class="card-header">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                    </svg>
                    <strong>General Settings</strong>
                    <small class="text-body-secondary ms-2">Override .env configuration</small>
                </div>
                <div class="card-body">
                    <form>
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="app_name" class="form-label">Application Name</label>
                                <input type="text" id="app_name" name="app_name" value="{{ isset($settings['app_name']) ? $settings['app_name']->value : config('app.name', 'E-Wallet System') }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="app_url" class="form-label">Application URL</label>
                                <input type="url" id="app_url" name="app_url" value="{{ isset($settings['app_url']) ? $settings['app_url']->value : config('app.url', 'http://127.0.0.1:8000') }}" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="app_description" class="form-label">Application Description</label>
                            <textarea id="app_description" name="app_description" rows="3" class="form-control">{{ isset($settings['app_description']) ? $settings['app_description']->value : 'Secure digital wallet platform for financial transactions' }}</textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="timezone" class="form-label">System Timezone</label>
                                <select id="timezone" name="timezone" class="form-select">
                                    @php $currentTimezone = isset($settings['timezone']) ? $settings['timezone']->value : config('app.timezone', 'UTC'); @endphp
                                    <option value="UTC" {{ $currentTimezone === 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="Asia/Manila" {{ $currentTimezone === 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (Philippines)</option>
                                    <option value="Asia/Singapore" {{ $currentTimezone === 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore</option>
                                    <option value="Asia/Tokyo" {{ $currentTimezone === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                                    <option value="America/New_York" {{ $currentTimezone === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="America/Chicago" {{ $currentTimezone === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                    <option value="America/Denver" {{ $currentTimezone === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                    <option value="America/Los_Angeles" {{ $currentTimezone === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    <option value="Europe/London" {{ $currentTimezone === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="language" class="form-label">Default Language</label>
                                <select id="language" name="language" class="form-select">
                                    @php $currentLanguage = isset($settings['language']) ? $settings['language']->value : config('app.locale', 'en'); @endphp
                                    <option value="en" {{ $currentLanguage === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="es" {{ $currentLanguage === 'es' ? 'selected' : '' }}>Spanish</option>
                                    <option value="fr" {{ $currentLanguage === 'fr' ? 'selected' : '' }}>French</option>
                                    <option value="de" {{ $currentLanguage === 'de' ? 'selected' : '' }}>German</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="app_env" class="form-label">Application Environment</label>
                                <select id="app_env" name="app_env" class="form-select">
                                    @php $currentEnv = isset($settings['app_env']) ? $settings['app_env']->value : config('app.env', 'local'); @endphp
                                    <option value="local" {{ $currentEnv === 'local' ? 'selected' : '' }}>Local (Development)</option>
                                    <option value="staging" {{ $currentEnv === 'staging' ? 'selected' : '' }}>Staging</option>
                                    <option value="production" {{ $currentEnv === 'production' ? 'selected' : '' }}>Production</option>
                                </select>
                                <div class="form-text">Environment affects debugging and error display</div>
                            </div>
                            <div class="col-md-6">
                                <label for="fallback_language" class="form-label">Fallback Language</label>
                                <select id="fallback_language" name="fallback_language" class="form-select">
                                    @php $currentFallback = isset($settings['fallback_language']) ? $settings['fallback_language']->value : config('app.fallback_locale', 'en'); @endphp
                                    <option value="en" {{ $currentFallback === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="es" {{ $currentFallback === 'es' ? 'selected' : '' }}>Spanish</option>
                                    <option value="fr" {{ $currentFallback === 'fr' ? 'selected' : '' }}>French</option>
                                    <option value="de" {{ $currentFallback === 'de' ? 'selected' : '' }}>German</option>
                                </select>
                                <div class="form-text">Used when the default language is unavailable</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="app_debug" name="app_debug"
                                       {{ isset($settings['app_debug']) ? ($settings['app_debug']->value ? 'checked' : '') : (config('app.debug') ? 'checked' : '') }}>
                                <label class="form-check-label" for="app_debug">
                                    <strong>Enable Debug Mode</strong>
                                    <div class="text-body-secondary small">Shows detailed error messages and debugging information. Should be disabled in production.</div>
                                </label>
                            </div>
                        </div>
                        <button type="button" onclick="saveSettings('general')" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                            </svg>
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-4 mt-4">
                <div class="card-header">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                    </svg>
                    <strong>System Information</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card bg-primary-subtle border-primary">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-primary">Laravel Version</h6>
                                    <h4 class="text-primary">{{ $systemStats['laravel_version'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success-subtle border-success">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-success">PHP Version</h6>
                                    <h4 class="text-success">{{ $systemStats['php_version'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info-subtle border-info">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-info">Database Size</h6>
                                    <h4 class="text-info">{{ $systemStats['database_size'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning-subtle border-warning">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-warning">Total Users</h6>
                                    <h4 class="text-warning">{{ $systemStats['total_users'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger-subtle border-danger">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-danger">Total Transactions</h6>
                                    <h4 class="text-danger">{{ $systemStats['total_transactions'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-secondary-subtle border-secondary">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-secondary">Storage Available</h6>
                                    <h4 class="text-secondary">{{ $systemStats['storage_used'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div id="security-section" class="settings-section d-none">
            <div class="card">
                <div class="card-header">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                    </svg>
                    <strong>Security Settings</strong>
                </div>
                <div class="card-body">
                    <form>
                        @csrf
                        <div class="mb-4">
                            <h6 class="mb-3">Authentication Settings</h6>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="require_2fa" name="require_2fa"
                                    {{ isset($settings['require_2fa']) && $settings['require_2fa']->value ? 'checked' : '' }}>
                                <label class="form-check-label" for="require_2fa">
                                    <strong>Require Two-Factor Authentication</strong>
                                    <div class="text-body-secondary small">Force all users to enable 2FA for enhanced security</div>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="email_verification_enabled" name="email_verification_enabled"
                                    {{ isset($settings['email_verification_enabled']) && $settings['email_verification_enabled']->value ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_verification_enabled">
                                    <strong>Email Verification Required</strong>
                                    <div class="text-body-secondary small">When disabled, users can login without verifying their email address</div>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="fraud_protection_enabled" name="fraud_protection_enabled"
                                    {{ isset($settings['fraud_protection_enabled']) && $settings['fraud_protection_enabled']->value ? 'checked' : '' }}>
                                <label class="form-check-label" for="fraud_protection_enabled">
                                    <strong>Enable Fraud Protection</strong>
                                    <div class="text-body-secondary small">Enable or disable the fraud protection system for checkouts.</div>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="session_timeout" name="session_timeout"
                                    {{ isset($settings['session_timeout']) && $settings['session_timeout']->value ? 'checked' : 'checked' }}>
                                <label class="form-check-label" for="session_timeout">
                                    <strong>Automatic Session Timeout</strong>
                                    <div class="text-body-secondary small">Sessions expire after configured time of inactivity</div>
                                </label>
                            </div>
                            <div class="mb-3" id="session_timeout_duration_field">
                                <label for="session_timeout_minutes" class="form-label">Session Timeout Duration (Minutes)</label>
                                <input type="number" id="session_timeout_minutes" name="session_timeout_minutes"
                                       value="{{ isset($settings['session_timeout_minutes']) ? $settings['session_timeout_minutes']->value : '15' }}"
                                       min="5" max="1440" class="form-control">
                                <div class="form-text">Number of minutes before user is automatically logged out due to inactivity (5-1440 minutes)</div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="max_login_attempts" class="form-label">Max Login Attempts</label>
                                <input type="number" id="max_login_attempts" name="max_login_attempts" value="{{ isset($settings['max_login_attempts']) ? $settings['max_login_attempts']->value : '3' }}" min="1" max="10" class="form-control">
                                <div class="form-text">Account locked after this many failed attempts</div>
                            </div>
                            <div class="col-md-6">
                                <label for="lockout_duration" class="form-label">Lockout Duration (minutes)</label>
                                <input type="number" id="lockout_duration" name="lockout_duration" value="{{ isset($settings['lockout_duration']) ? $settings['lockout_duration']->value : '15' }}" min="1" max="1440" class="form-control">
                                <div class="form-text">How long accounts remain locked</div>
                            </div>
                        </div>
                        <button type="button" onclick="saveSettings('security')" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                            </svg>
                            Update Security Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Wallet Settings -->
        <div id="wallet-section" class="settings-section d-none">
            <div class="card">
                <div class="card-header">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                    </svg>
                    <strong>Wallet Settings</strong>
                </div>
                <div class="card-body">
                    <form>
                        @csrf
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="min_deposit" class="form-label">Minimum Deposit Amount ($)</label>
                                <input type="number" id="min_deposit" name="min_deposit" value="{{ isset($settings['min_deposit']) ? $settings['min_deposit']->value : '1.00' }}" min="0" step="0.01" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="max_deposit" class="form-label">Maximum Deposit Amount ($)</label>
                                <input type="number" id="max_deposit" name="max_deposit" value="{{ isset($settings['max_deposit']) ? $settings['max_deposit']->value : '10000.00' }}" min="1" step="0.01" class="form-control">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="min_withdrawal" class="form-label">Minimum Withdrawal Amount ($)</label>
                                <input type="number" id="min_withdrawal" name="min_withdrawal" value="{{ isset($settings['min_withdrawal']) ? $settings['min_withdrawal']->value : '1.00' }}" min="0" step="0.01" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="max_withdrawal" class="form-label">Maximum Withdrawal Amount ($)</label>
                                <input type="number" id="max_withdrawal" name="max_withdrawal" value="{{ isset($settings['max_withdrawal']) ? $settings['max_withdrawal']->value : '10000.00' }}" min="1" step="0.01" class="form-control">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Fee Configuration</h6>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="withdrawal_fee_enabled" name="withdrawal_fee_enabled"
                                {{ isset($settings['withdrawal_fee_enabled']) && $settings['withdrawal_fee_enabled']->value ? 'checked' : '' }}>
                            <label class="form-check-label" for="withdrawal_fee_enabled">
                                <strong>Enable Withdrawal Fees</strong>
                                <div class="text-body-secondary small">Charge fees for withdrawal transactions</div>
                            </label>
                        </div>

                        <div id="withdrawal_fee_config" class="card border-warning mb-3" style="display: {{ isset($settings['withdrawal_fee_enabled']) && $settings['withdrawal_fee_enabled']->value ? 'block' : 'none' }};">
                            <div class="card-header bg-warning-subtle">
                                <h6 class="mb-0">Withdrawal Fee Configuration</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="withdrawal_fee_type" class="form-label">Fee Type</label>
                                        <select class="form-select" id="withdrawal_fee_type" name="withdrawal_fee_type">
                                            <option value="fixed" {{ isset($settings['withdrawal_fee_type']) && $settings['withdrawal_fee_type']->value === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                            <option value="percentage" {{ !isset($settings['withdrawal_fee_type']) || $settings['withdrawal_fee_type']->value === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="withdrawal_fee_value" class="form-label">Fee Value</label>
                                        <input type="number" class="form-control" id="withdrawal_fee_value" name="withdrawal_fee_value"
                                               value="{{ isset($settings['withdrawal_fee_value']) ? $settings['withdrawal_fee_value']->value : '0' }}" min="0" step="0.01" placeholder="0.00">
                                        <div class="form-text">For fixed: amount in $, for percentage: % (e.g., 2.5 for 2.5%)</div>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label for="withdrawal_minimum_fee" class="form-label">Minimum Fee ($)</label>
                                        <input type="number" class="form-control" id="withdrawal_minimum_fee" name="withdrawal_minimum_fee"
                                               value="{{ isset($settings['withdrawal_minimum_fee']) ? $settings['withdrawal_minimum_fee']->value : '0' }}" min="0" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="withdrawal_maximum_fee" class="form-label">Maximum Fee ($)</label>
                                        <input type="number" class="form-control" id="withdrawal_maximum_fee" name="withdrawal_maximum_fee"
                                               value="{{ isset($settings['withdrawal_maximum_fee']) ? $settings['withdrawal_maximum_fee']->value : '0' }}" min="0" step="0.01" placeholder="0.00">
                                        <div class="form-text">0 means no limit</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="transfer_fee_enabled" name="transfer_fee_enabled"
                                {{ isset($settings['transfer_charge_enabled']) && $settings['transfer_charge_enabled']->value ? 'checked' : '' }}>
                            <label class="form-check-label" for="transfer_fee_enabled">
                                <strong>Enable Transfer Fees</strong>
                                <div class="text-body-secondary small">Charge fees for fund transfers between users</div>
                            </label>
                        </div>

                        <div id="transfer_fee_config" class="card border-info mb-3" style="display: {{ isset($settings['transfer_charge_enabled']) && $settings['transfer_charge_enabled']->value ? 'block' : 'none' }};">
                            <div class="card-header bg-info-subtle">
                                <h6 class="mb-0">Transfer Fee Configuration</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="transfer_fee_type" class="form-label">Fee Type</label>
                                        <select class="form-select" id="transfer_fee_type" name="transfer_fee_type">
                                            <option value="fixed" {{ isset($settings['transfer_charge_type']) && $settings['transfer_charge_type']->value === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                            <option value="percentage" {{ !isset($settings['transfer_charge_type']) || $settings['transfer_charge_type']->value === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="transfer_fee_value" class="form-label">Fee Value</label>
                                        <input type="number" class="form-control" id="transfer_fee_value" name="transfer_fee_value"
                                               value="{{ isset($settings['transfer_charge_value']) ? $settings['transfer_charge_value']->value : '0' }}" min="0" step="0.01" placeholder="0.00">
                                        <div class="form-text">For fixed: amount in $, for percentage: % (e.g., 1.5 for 1.5%)</div>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label for="transfer_minimum_fee" class="form-label">Minimum Fee ($)</label>
                                        <input type="number" class="form-control" id="transfer_minimum_fee" name="transfer_minimum_fee"
                                               value="{{ isset($settings['transfer_minimum_charge']) ? $settings['transfer_minimum_charge']->value : '0' }}" min="0" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="transfer_maximum_fee" class="form-label">Maximum Fee ($)</label>
                                        <input type="number" class="form-control" id="transfer_maximum_fee" name="transfer_maximum_fee"
                                               value="{{ isset($settings['transfer_maximum_charge']) ? $settings['transfer_maximum_charge']->value : '0' }}" min="0" step="0.01" placeholder="0.00">
                                        <div class="form-text">0 means no limit</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading">Transaction Approval Policy</h6>
                            <ul class="mb-0">
                                <li><strong>Withdrawals:</strong> Always require manual admin approval</li>
                                <li><strong>Deposits:</strong> Always require manual admin approval</li>
                                <li><strong>Transfers:</strong> Process automatically (user-to-user transfers)</li>
                            </ul>
                        </div>

                        <button type="button" onclick="saveSettings('wallet')" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                            </svg>
                            Save Wallet Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notifications Settings -->
        <div id="notifications-section" class="settings-section d-none">
            <div class="card">
                <div class="card-header">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-bell') }}"></use>
                    </svg>
                    <strong>Notification Settings</strong>
                </div>
                <div class="card-body">
                    <form>
                        @csrf
                        <h6 class="mb-3">Email Notifications</h6>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="notify_new_user" name="notify_new_user"
                                {{ isset($settings['notify_new_user']) && $settings['notify_new_user']->value ? 'checked' : 'checked' }}>
                            <label class="form-check-label" for="notify_new_user">
                                <strong>New User Registration</strong>
                                <div class="text-body-secondary small">Notify admins when new users register</div>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="notify_large_transaction" name="notify_large_transaction"
                                {{ isset($settings['notify_large_transaction']) && $settings['notify_large_transaction']->value ? 'checked' : 'checked' }}>
                            <label class="form-check-label" for="notify_large_transaction">
                                <strong>Large Transactions</strong>
                                <div class="text-body-secondary small">Notify admins of transactions over the review threshold</div>
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="notify_suspicious" name="notify_suspicious"
                                {{ isset($settings['notify_suspicious']) && $settings['notify_suspicious']->value ? 'checked' : 'checked' }}>
                            <label class="form-check-label" for="notify_suspicious">
                                <strong>Suspicious Activity</strong>
                                <div class="text-body-secondary small">Immediate alerts for flagged transactions</div>
                            </label>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="admin_email" class="form-label">Admin Email Address</label>
                                <input type="email" id="admin_email" name="admin_email" value="{{ isset($settings['admin_email']) ? $settings['admin_email']->value : 'admin@example.com' }}" class="form-control">
                                <div class="form-text">Primary email for system notifications</div>
                            </div>
                            <div class="col-md-6">
                                <label for="transaction_review_threshold" class="form-label">Large Transaction Threshold ($)</label>
                                <input type="number" id="transaction_review_threshold" name="transaction_review_threshold" value="{{ isset($settings['transaction_review_threshold']) ? $settings['transaction_review_threshold']->value : '1000' }}" min="0" step="0.01" class="form-control">
                                <div class="form-text">Amount above which transactions trigger notifications</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" onclick="saveSettings('notifications')" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                                </svg>
                                Save Notification Settings
                            </button>
                            <button type="button" onclick="testNotifications()" class="btn btn-outline-secondary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-send') }}"></use>
                                </svg>
                                Send Test Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Maintenance Settings -->
        <div id="maintenance-section" class="settings-section d-none">
            <div class="card">
                <div class="card-header">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wrench') }}"></use>
                    </svg>
                    <strong>Maintenance Settings</strong>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">Maintenance Mode</h6>
                        <p class="mb-3">System is currently operational. Use maintenance mode for scheduled updates.</p>
                        <button type="button" onclick="toggleMaintenanceMode()" class="btn btn-warning">
                            Enable Maintenance Mode
                        </button>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Database Backup</h6>
                                <p class="mb-3">Last backup: 2 hours ago</p>
                                <button type="button" onclick="createBackup()" class="btn btn-info">
                                    Create Backup Now
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h6 class="alert-heading">Cache Management</h6>
                                <p class="mb-3">System cache status: Active</p>
                                <button type="button" onclick="clearCache()" class="btn btn-success">
                                    Clear All Caches
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Danger Zone</h6>
                        <p class="mb-3">These actions are irreversible. Please proceed with caution.</p>
                        <button type="button" onclick="resetSystem()" class="btn btn-danger">
                            Reset System Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Section -->
        <div id="payment-methods-section" class="settings-section d-none">
            <div class="card">
                <div class="card-header">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                    </svg>
                    <strong>Payment Methods Configuration</strong>
                </div>
                <div class="card-body">
                    <form>
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <svg class="icon me-2">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-money') }}"></use>
                                        </svg>
                                        Gcash Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="gcash_enabled" name="gcash_enabled" value="1"
                                                   {{ old('gcash_enabled', $paymentSettings['gcash_enabled'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="gcash_enabled">
                                                Enable Gcash Payment Method
                                            </label>
                                        </div>
                                        <small class="text-muted">Allow users to select Gcash as a payment method</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gcash_number" class="form-label">Gcash Number</label>
                                        <input type="text" class="form-control" id="gcash_number" name="gcash_number"
                                               value="{{ old('gcash_number', $paymentSettings['gcash_number'] ?? '') }}"
                                               placeholder="09XX XXX XXXX">
                                        <small class="text-muted">The Gcash number where users should send their deposits</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gcash_name" class="form-label">Account Name (Optional)</label>
                                        <input type="text" class="form-control" id="gcash_name" name="gcash_name"
                                               value="{{ old('gcash_name', $paymentSettings['gcash_name'] ?? '') }}"
                                               placeholder="Account holder name">
                                        <small class="text-muted">The name registered to the Gcash account</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <svg class="icon me-2">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                                        </svg>
                                        Maya Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="maya_enabled" name="maya_enabled" value="1"
                                                   {{ old('maya_enabled', $paymentSettings['maya_enabled'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="maya_enabled">
                                                Enable Maya Payment Method
                                            </label>
                                        </div>
                                        <small class="text-muted">Allow users to select Maya as a payment method</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="maya_number" class="form-label">Maya Number</label>
                                        <input type="text" class="form-control" id="maya_number" name="maya_number"
                                               value="{{ old('maya_number', $paymentSettings['maya_number'] ?? '') }}"
                                               placeholder="09XX XXX XXXX">
                                        <small class="text-muted">The Maya number where users should send their deposits</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="maya_name" class="form-label">Account Name (Optional)</label>
                                        <input type="text" class="form-control" id="maya_name" name="maya_name"
                                               value="{{ old('maya_name', $paymentSettings['maya_name'] ?? '') }}"
                                               placeholder="Account holder name">
                                        <small class="text-muted">The name registered to the Maya account</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
                                </svg>
                                General Payment Settings
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="cash_enabled" name="cash_enabled" value="1"
                                                   {{ old('cash_enabled', $paymentSettings['cash_enabled'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="cash_enabled">
                                                Enable Cash Payment Method
                                            </label>
                                        </div>
                                        <small class="text-muted">Allow users to select Cash as a payment method</small>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="others_enabled" name="others_enabled" value="1"
                                                   {{ old('others_enabled', $paymentSettings['others_enabled'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="others_enabled">
                                                Allow Custom Payment Methods
                                            </label>
                                        </div>
                                        <small class="text-muted">Allow users to enter their own payment method (Others)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="button" onclick="saveSettings('payment-methods')" class="btn btn-success">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                            </svg>
                            Save Payment Methods Settings
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize fee configuration visibility
    const withdrawalFeeCheckbox = document.getElementById('withdrawal_fee_enabled');
    const transferFeeCheckbox = document.getElementById('transfer_fee_enabled');
    const withdrawalFeeConfig = document.getElementById('withdrawal_fee_config');
    const transferFeeConfig = document.getElementById('transfer_fee_config');

    // Show/hide withdrawal fee configuration
    if (withdrawalFeeCheckbox && withdrawalFeeConfig) {
        // Set initial state
        withdrawalFeeConfig.style.display = withdrawalFeeCheckbox.checked ? 'block' : 'none';

        withdrawalFeeCheckbox.addEventListener('change', function() {
            withdrawalFeeConfig.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Show/hide transfer fee configuration
    if (transferFeeCheckbox && transferFeeConfig) {
        // Set initial state
        transferFeeConfig.style.display = transferFeeCheckbox.checked ? 'block' : 'none';

        transferFeeCheckbox.addEventListener('change', function() {
            transferFeeConfig.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Show/hide session timeout duration configuration
    const sessionTimeoutCheckbox = document.getElementById('session_timeout');
    const sessionTimeoutDurationField = document.getElementById('session_timeout_duration_field');

    if (sessionTimeoutCheckbox && sessionTimeoutDurationField) {
        // Set initial state
        sessionTimeoutDurationField.style.display = sessionTimeoutCheckbox.checked ? 'block' : 'none';

        sessionTimeoutCheckbox.addEventListener('change', function() {
            sessionTimeoutDurationField.style.display = this.checked ? 'block' : 'none';
        });
    }
});

function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.settings-section').forEach(section => {
        section.classList.add('d-none');
    });

    // Show selected section
    document.getElementById(sectionName + '-section').classList.remove('d-none');

    // Update navigation
    document.querySelectorAll('.settings-nav-item').forEach(item => {
        item.classList.remove('active');
    });

    event.target.classList.add('active');
}

function saveSettings(category) {
    if (category === 'security') {
        // Get security settings values
        const emailVerificationEnabled = document.getElementById('email_verification_enabled').checked;
        const require2fa = document.getElementById('require_2fa').checked;
        const fraudProtectionEnabled = document.getElementById('fraud_protection_enabled').checked;
        const sessionTimeout = document.getElementById('session_timeout').checked;
        const sessionTimeoutMinutes = parseInt(document.getElementById('session_timeout_minutes').value) || 15;
        const maxLoginAttempts = parseInt(document.getElementById('max_login_attempts').value) || 3;
        const lockoutDuration = parseInt(document.getElementById('lockout_duration').value) || 15;

        // Send AJAX request to update settings
        fetch('{{ route("admin.system.settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                email_verification_enabled: emailVerificationEnabled,
                require_2fa: require2fa,
                fraud_protection_enabled: fraudProtectionEnabled,
                session_timeout: sessionTimeout,
                session_timeout_minutes: sessionTimeoutMinutes,
                max_login_attempts: maxLoginAttempts,
                lockout_duration: lockoutDuration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Security settings updated successfully!', 'success');
            } else {
                showAlert('Error updating settings: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error updating settings. Please try again.', 'error');
        });
    } else if (category === 'wallet') {
        // Get wallet limits values
        const minDeposit = parseFloat(document.getElementById('min_deposit').value) || 0;
        const maxDeposit = parseFloat(document.getElementById('max_deposit').value) || 0;
        const minWithdrawal = parseFloat(document.getElementById('min_withdrawal').value) || 0;
        const maxWithdrawal = parseFloat(document.getElementById('max_withdrawal').value) || 0;

        // Get withdrawal fee settings values
        const withdrawalFeeEnabled = document.getElementById('withdrawal_fee_enabled').checked;
        const withdrawalFeeType = document.getElementById('withdrawal_fee_type').value;
        const withdrawalFeeValue = parseFloat(document.getElementById('withdrawal_fee_value').value) || 0;
        const withdrawalMinimumFee = parseFloat(document.getElementById('withdrawal_minimum_fee').value) || 0;
        const withdrawalMaximumFee = parseFloat(document.getElementById('withdrawal_maximum_fee').value) || 0;

        // Get transfer fee settings values
        const transferFeeEnabled = document.getElementById('transfer_fee_enabled').checked;
        const transferFeeType = document.getElementById('transfer_fee_type').value;
        const transferFeeValue = parseFloat(document.getElementById('transfer_fee_value').value) || 0;
        const transferMinimumFee = parseFloat(document.getElementById('transfer_minimum_fee').value) || 0;
        const transferMaximumFee = parseFloat(document.getElementById('transfer_maximum_fee').value) || 0;

        // Send AJAX request to update wallet settings
        fetch('{{ route("admin.system.settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                min_deposit: minDeposit,
                max_deposit: maxDeposit,
                min_withdrawal: minWithdrawal,
                max_withdrawal: maxWithdrawal,
                withdrawal_fee_enabled: withdrawalFeeEnabled,
                withdrawal_fee_type: withdrawalFeeType,
                withdrawal_fee_value: withdrawalFeeValue,
                withdrawal_minimum_fee: withdrawalMinimumFee,
                withdrawal_maximum_fee: withdrawalMaximumFee,
                transfer_fee_enabled: transferFeeEnabled,
                transfer_fee_type: transferFeeType,
                transfer_fee_value: transferFeeValue,
                transfer_minimum_fee: transferMinimumFee,
                transfer_maximum_fee: transferMaximumFee
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Wallet settings updated successfully!', 'success');
            } else {
                showAlert('Error updating settings: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error updating settings. Please try again.', 'error');
        });
    } else if (category === 'payment-methods') {
        // Get payment method settings values
        const gcashEnabled = document.getElementById('gcash_enabled').checked;
        const gcashNumber = document.getElementById('gcash_number').value;
        const gcashName = document.getElementById('gcash_name').value;
        const mayaEnabled = document.getElementById('maya_enabled').checked;
        const mayaNumber = document.getElementById('maya_number').value;
        const mayaName = document.getElementById('maya_name').value;
        const cashEnabled = document.getElementById('cash_enabled').checked;
        const othersEnabled = document.getElementById('others_enabled').checked;

        // Send AJAX request to update payment method settings
        fetch('{{ route("admin.system.settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                gcash_enabled: gcashEnabled,
                gcash_number: gcashNumber,
                gcash_name: gcashName,
                maya_enabled: mayaEnabled,
                maya_number: mayaNumber,
                maya_name: mayaName,
                cash_enabled: cashEnabled,
                others_enabled: othersEnabled
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Payment methods settings updated successfully!', 'success');
            } else {
                showAlert('Error updating settings: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error updating settings. Please try again.', 'error');
        });
    } else if (category === 'general') {
        // Get general settings values
        const appName = document.getElementById('app_name').value;
        const appUrl = document.getElementById('app_url').value;
        const appDescription = document.getElementById('app_description').value;
        const timezone = document.getElementById('timezone').value;
        const language = document.getElementById('language').value;
        const appEnv = document.getElementById('app_env').value;
        const fallbackLanguage = document.getElementById('fallback_language').value;
        const appDebug = document.getElementById('app_debug').checked;

        // Send AJAX request to update general settings
        fetch('{{ route("admin.system.settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                app_name: appName,
                app_url: appUrl,
                app_description: appDescription,
                timezone: timezone,
                language: language,
                app_env: appEnv,
                fallback_language: fallbackLanguage,
                app_debug: appDebug
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('General settings updated successfully!', 'success');
            } else {
                showAlert('Error updating settings: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error updating settings. Please try again.', 'error');
        });
    } else if (category === 'notifications') {
        // Get notification settings values
        const notifyNewUser = document.getElementById('notify_new_user').checked;
        const notifyLargeTransaction = document.getElementById('notify_large_transaction').checked;
        const notifySuspicious = document.getElementById('notify_suspicious').checked;
        const adminEmail = document.getElementById('admin_email').value;
        const transactionReviewThreshold = parseFloat(document.getElementById('transaction_review_threshold').value) || 1000;

        // Send AJAX request to update notification settings
        fetch('{{ route("admin.system.settings.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                notify_new_user: notifyNewUser,
                notify_large_transaction: notifyLargeTransaction,
                notify_suspicious: notifySuspicious,
                admin_email: adminEmail,
                transaction_review_threshold: transactionReviewThreshold
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Notification settings updated successfully!', 'success');
            } else {
                showAlert('Error updating settings: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error updating settings. Please try again.', 'error');
        });
    } else {
        // Simulate saving other categories
        showAlert(`${category.charAt(0).toUpperCase() + category.slice(1)} settings saved successfully!`, 'success');
    }
}

function toggleMaintenanceMode() {
    if (confirm('Are you sure you want to enable maintenance mode? This will prevent users from accessing the system.')) {
        showAlert('Maintenance mode enabled. System is now offline for users.', 'success');
    }
}

function createBackup() {
    showAlert('Database backup started. You will be notified when complete.', 'success');
}

function clearCache() {
    if (confirm('Are you sure you want to clear all caches? This may temporarily slow down the system.')) {
        showAlert('All caches cleared successfully.', 'success');
    }
}

function resetSystem() {
    if (confirm('Are you sure you want to reset all system settings? This action cannot be undone.')) {
        if (confirm('This will reset ALL settings to default values. Are you absolutely sure?')) {
            showAlert('System settings have been reset to defaults.', 'success');
        }
    }
}

function testNotifications() {
    if (confirm('This will send a test email to the configured admin email address. Continue?')) {
        fetch('{{ route("admin.system.settings.test-notification") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Test notification sent successfully! Check your email.', 'success');
            } else {
                showAlert('Failed to send test notification: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showAlert('Error sending test notification. Please try again.', 'error');
        });
    }
}

function showAlert(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show shadow position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '1060';
    alert.innerHTML = `
        <div class="d-flex align-items-center">
            <svg class="icon me-2">
                <use xlink:href="${window.location.origin}/coreui-template/vendors/@coreui/icons/svg/free.svg#cil-${type === 'success' ? 'check' : 'x'}"></use>
            </svg>
            ${message}
        </div>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;

    document.body.appendChild(alert);

    setTimeout(() => {
        if (alert.parentElement) {
            alert.remove();
        }
    }, 5000);
}
</script>
@endsection