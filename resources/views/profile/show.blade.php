@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
<!-- Email Verification Status Information -->
@if ($user->email && !$user->hasVerifiedEmail())
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
        <svg class="icon me-3 flex-shrink-0" style="width: 2.5rem; height: 2.5rem;">
            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
        </svg>
        <div class="flex-grow-1">
            <strong>Email Not Verified</strong><br>
            Your email address is not verified. Email verification is optional.
        </div>
        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Profile Information Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                </svg>
                <strong>Profile Information</strong>
            </div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   id="username" name="username" value="{{ old('username', $user->username) }}" readonly>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-muted">(Optional)</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="your.email@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if (!$user->email)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <svg class="icon icon-sm me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                        </svg>
                                        Add an email to receive notifications. A verification email will be sent automatically.
                                    </small>
                                </div>
                            @elseif ($user->email && !$user->hasVerifiedEmail())
                                <div class="mt-2">
                                    <small class="text-warning">
                                        <svg class="icon icon-sm me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                        </svg>
                                        Your email address is not verified. Email verification is optional.
                                    </small>
                                </div>
                            @elseif ($user->email && $user->hasVerifiedEmail())
                                <div class="mt-2">
                                    <small class="text-success">
                                        <svg class="icon icon-sm me-1">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                                        </svg>
                                        Your email address is verified.
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Created</label>
                            <input type="text" class="form-control" value="{{ $user->created_at->format('M d, Y') }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Type</label>
                            <input type="text" class="form-control" value="{{ ucfirst($user->getRoleNames()->first() ?? 'Member') }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Delivery Address Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-location-pin') }}"></use>
                </svg>
                <strong>Delivery Address</strong>
                <small class="text-muted ms-2">Used for home delivery orders</small>
            </div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Full Name -->
                        <div class="col-md-6">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('fullname') is-invalid @enderror"
                                   id="fullname" name="fullname" value="{{ old('fullname', $user->fullname) }}">
                            @error('fullname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" placeholder="+1 (555) 123-4567"
                                   value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Required for delivery coordination</div>
                        </div>

                        <!-- Street Address -->
                        <div class="col-12">
                            <label for="address" class="form-label">Street Address</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror"
                                   id="address" name="address" placeholder="1234 Main Street"
                                   value="{{ old('address', $user->address) }}">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address Line 2 -->
                        <div class="col-12">
                            <label for="address_2" class="form-label">Address Line 2 <span class="text-muted">(Optional)</span></label>
                            <input type="text" class="form-control @error('address_2') is-invalid @enderror"
                                   id="address_2" name="address_2" placeholder="Apartment, suite, unit, floor, etc."
                                   value="{{ old('address_2', $user->address_2) }}">
                            @error('address_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- City, State, ZIP -->
                        <div class="col-md-6">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   id="city" name="city" placeholder="Enter city"
                                   value="{{ old('city', $user->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="state" class="form-label">State/Province</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror"
                                   id="state" name="state" placeholder="State"
                                   value="{{ old('state', $user->state) }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="zip" class="form-label">ZIP/Postal Code</label>
                            <input type="text" class="form-control @error('zip') is-invalid @enderror"
                                   id="zip" name="zip" placeholder="12345"
                                   value="{{ old('zip', $user->zip) }}">
                            @error('zip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Delivery Instructions -->
                        <div class="col-12">
                            <label for="delivery_instructions" class="form-label">
                                Delivery Instructions <span class="text-muted">(Optional)</span>
                            </label>
                            <textarea class="form-control @error('delivery_instructions') is-invalid @enderror"
                                      id="delivery_instructions" name="delivery_instructions"
                                      rows="3" placeholder="Special delivery instructions (e.g., gate code, building entrance, safe place to leave package)">{{ old('delivery_instructions', $user->delivery_instructions) }}</textarea>
                            @error('delivery_instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Help our delivery team find you easily</div>
                        </div>

                        <!-- Preferred Delivery Time -->
                        <div class="col-12">
                            <label class="form-label">Preferred Delivery Time</label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="delivery_time_preference"
                                               id="profile_anytime" value="anytime"
                                               {{ old('delivery_time_preference', $user->delivery_time_preference) === 'anytime' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="profile_anytime">
                                            Anytime (9 AM - 6 PM)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="delivery_time_preference"
                                               id="profile_morning" value="morning"
                                               {{ old('delivery_time_preference', $user->delivery_time_preference) === 'morning' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="profile_morning">
                                            Morning (9 AM - 12 PM)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="delivery_time_preference"
                                               id="profile_afternoon" value="afternoon"
                                               {{ old('delivery_time_preference', $user->delivery_time_preference) === 'afternoon' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="profile_afternoon">
                                            Afternoon (12 PM - 6 PM)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="delivery_time_preference"
                                               id="profile_weekend" value="weekend"
                                               {{ old('delivery_time_preference', $user->delivery_time_preference) === 'weekend' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="profile_weekend">
                                            Weekend preferred
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                        </svg>
                        Save Delivery Address
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment Preferences Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                </svg>
                <strong>Payment Preferences</strong>
                <small class="text-muted ms-2">Used for automatic withdrawal pre-filling</small>
            </div>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Payment Preference Dropdown -->
                        <div class="col-12">
                            <label for="payment_preference" class="form-label">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-dollar') }}"></use>
                                </svg>
                                Payment Preference
                            </label>
                            <select id="payment_preference" name="payment_preference" class="form-select @error('payment_preference') is-invalid @enderror">
                                <option value="">Select Payment Method</option>
                                <option value="Gcash" {{ old('payment_preference', $user->payment_preference) == 'Gcash' ? 'selected' : '' }}>Gcash</option>
                                <option value="Maya" {{ old('payment_preference', $user->payment_preference) == 'Maya' ? 'selected' : '' }}>Maya</option>
                                <option value="Cash" {{ old('payment_preference', $user->payment_preference) == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Others" {{ old('payment_preference', $user->payment_preference) == 'Others' ? 'selected' : '' }}>Others</option>
                            </select>
                            @error('payment_preference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This will be automatically pre-filled when you withdraw funds</div>
                        </div>

                        <!-- Gcash Number (shown when Gcash is selected) -->
                        <div class="col-12 payment-field" id="gcash_field" style="display: none;">
                            <label for="gcash_number" class="form-label">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-mobile') }}"></use>
                                </svg>
                                Gcash Number
                            </label>
                            <input type="text" class="form-control @error('gcash_number') is-invalid @enderror"
                                   id="gcash_number" name="gcash_number" placeholder="09XXXXXXXXX" maxlength="11"
                                   value="{{ old('gcash_number', $user->gcash_number) }}">
                            @error('gcash_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter your 11-digit Gcash mobile number</div>
                        </div>

                        <!-- Maya Number (shown when Maya is selected) -->
                        <div class="col-12 payment-field" id="maya_field" style="display: none;">
                            <label for="maya_number" class="form-label">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-mobile') }}"></use>
                                </svg>
                                Maya Number
                            </label>
                            <input type="text" class="form-control @error('maya_number') is-invalid @enderror"
                                   id="maya_number" name="maya_number" placeholder="09XXXXXXXXX" maxlength="11"
                                   value="{{ old('maya_number', $user->maya_number) }}">
                            @error('maya_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter your 11-digit Maya mobile number</div>
                        </div>

                        <!-- Pickup Location (shown when Cash is selected) -->
                        <div class="col-12 payment-field" id="cash_field" style="display: none;">
                            <label for="pickup_location" class="form-label">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-location-pin') }}"></use>
                                </svg>
                                Preferred Pickup Location <span class="text-muted">(Optional)</span>
                            </label>
                            <input type="text" class="form-control @error('pickup_location') is-invalid @enderror"
                                   id="pickup_location" name="pickup_location" placeholder="Leave blank to use admin's office address"
                                   value="{{ old('pickup_location', $user->pickup_location) }}">
                            @error('pickup_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <svg class="icon icon-sm me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                                </svg>
                                If left blank, the admin's office address will be used automatically for cash pickup
                            </div>
                        </div>

                        <!-- Other Payment Method (shown when Others is selected) -->
                        <div class="payment-field" id="others_field" style="display: none;">
                            <div class="col-12 mb-3">
                                <label for="other_payment_method" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                                    </svg>
                                    Payment Method Name
                                </label>
                                <input type="text" class="form-control @error('other_payment_method') is-invalid @enderror"
                                       id="other_payment_method" name="other_payment_method" placeholder="e.g., Bank Transfer, PayPal, etc."
                                       value="{{ old('other_payment_method', $user->other_payment_method) }}">
                                @error('other_payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="other_payment_details" class="form-label">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-notes') }}"></use>
                                    </svg>
                                    Payment Details
                                </label>
                                <textarea class="form-control @error('other_payment_details') is-invalid @enderror"
                                          id="other_payment_details" name="other_payment_details" rows="3"
                                          placeholder="Account number, bank name, or other relevant payment details...">{{ old('other_payment_details', $user->other_payment_details) }}</textarea>
                                @error('other_payment_details')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Provide complete payment details for this method</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-save') }}"></use>
                        </svg>
                        Save Payment Preferences
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Update Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                </svg>
                <strong>Update Password</strong>
            </div>
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showPasswords">
                                <label class="form-check-label" for="showPasswords">
                                    Show passwords
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                        </svg>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Wallet Information Card -->
        @if($user->wallet)
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                </svg>
                <strong>Wallet Information</strong>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h4 class="text-success">{{ currency($user->wallet->balance) }}</h4>
                    <p class="text-body-secondary">Current Balance</p>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-6 fw-semibold">{{ $user->transactions()->where('status', 'completed')->count() }}</div>
                            <div class="text-uppercase text-body-secondary" style="font-size: 0.7rem;">Trxs</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <div class="fs-6 fw-semibold">{{ currency($user->transactions()->where('type', 'deposit')->where('status', 'completed')->sum('amount')) }}</div>
                            <div class="text-uppercase text-body-secondary" style="font-size: 0.7rem;">In</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="fs-6 fw-semibold">{{ currency($user->transactions()->where('type', 'withdraw')->where('status', 'completed')->sum('amount')) }}</div>
                        <div class="text-uppercase text-body-secondary" style="font-size: 0.7rem;">Out</div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('wallet.transactions') }}" class="btn btn-outline-primary btn-sm">View All Transactions</a>
            </div>
        </div>
        @endif

        <!-- Account Security Card -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <svg class="icon icon-lg me-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                </svg>
                <strong>Account Status</strong>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>Network Status</strong>
                        <div class="text-body-secondary small">Commission eligibility</div>
                    </div>
                    <div>
                        @if($user->isNetworkActive())
                            <span class="badge bg-success">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                                </svg>
                                Active
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
                @if(!$user->isNetworkActive())
                    <div class="alert alert-info mb-3">
                        <div class="d-flex align-items-start">
                            <svg class="icon me-2 flex-shrink-0">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                            </svg>
                            <small>Purchase a package to activate your account and start earning MLM and Unilevel commissions from your downline.</small>
                        </div>
                    </div>
                @endif
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>Email Verification</strong>
                        <div class="text-body-secondary small">Secure your account</div>
                    </div>
                    <div>
                        @if(is_null($user->email))
                            <span class="badge bg-secondary">None</span>
                        @elseif($user->hasVerifiedEmail())
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-warning">Unverified</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Two-Factor Authentication</strong>
                        <div class="text-body-secondary small">Add extra security</div>
                    </div>
                    <div>
                        @if($user->two_factor_secret)
                            <span class="badge bg-success">Enabled</span>
                        @else
                            <span class="badge bg-secondary">Disabled</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showPasswordsCheckbox = document.getElementById('showPasswords');
    const currentPasswordInput = document.getElementById('current_password');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    // Function to toggle password visibility for all password fields
    function togglePasswordVisibility() {
        const inputType = showPasswordsCheckbox.checked ? 'text' : 'password';
        currentPasswordInput.type = inputType;
        passwordInput.type = inputType;
        passwordConfirmationInput.type = inputType;
    }

    // Listen for checkbox changes
    showPasswordsCheckbox.addEventListener('change', togglePasswordVisibility);

    // Payment preference handling
    const paymentPreferenceSelect = document.getElementById('payment_preference');
    const gcashField = document.getElementById('gcash_field');
    const mayaField = document.getElementById('maya_field');
    const cashField = document.getElementById('cash_field');
    const othersField = document.getElementById('others_field');

    function handlePaymentPreferenceChange() {
        // Hide all fields
        gcashField.style.display = 'none';
        mayaField.style.display = 'none';
        cashField.style.display = 'none';
        othersField.style.display = 'none';

        // Show the selected field
        const selectedValue = paymentPreferenceSelect.value;
        if (selectedValue === 'Gcash') {
            gcashField.style.display = 'block';
        } else if (selectedValue === 'Maya') {
            mayaField.style.display = 'block';
        } else if (selectedValue === 'Cash') {
            cashField.style.display = 'block';
        } else if (selectedValue === 'Others') {
            othersField.style.display = 'block';
        }
    }

    // Initial load - show the appropriate field based on saved preference
    handlePaymentPreferenceChange();

    // Listen for changes
    paymentPreferenceSelect.addEventListener('change', handlePaymentPreferenceChange);
});
</script>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>

@endsection