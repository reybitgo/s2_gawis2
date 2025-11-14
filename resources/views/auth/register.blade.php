@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center pt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mb-4 mx-4">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img class="logo-dark" src="{{ asset('coreui-template/assets/brand/gawis_logo.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                            <img class="logo-light" src="{{ asset('coreui-template/assets/brand/gawis_logo_light.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h1>Register</h1>
                            <a href="{{ url('/') }}" class="small">&larr; Back to Home</a>
                        </div>
                        <p class="text-body-secondary">Create your account</p>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                                <svg class="icon me-3 flex-shrink-0" style="width: 2.5rem; height: 2.5rem;">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                </svg>
                                <div class="flex-grow-1">
                                    @if($errors->count() === 1)
                                        {{ $errors->first() }}
                                    @else
                                        <strong>Please correct the following issues:</strong>
                                        <div class="mt-2">
                                            @foreach ($errors->all() as $error)
                                                <div class="mb-1">• {{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('register') }}" method="POST">
                            @csrf

                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('fullname') is-invalid @enderror"
                                       type="text"
                                       name="fullname"
                                       id="fullname"
                                       placeholder="Full Name"
                                       value="{{ old('fullname') }}"
                                       autocomplete="name"
                                       required>
                            </div>

                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('username') is-invalid @enderror"
                                       type="text"
                                       name="username"
                                       id="username"
                                       placeholder="Username"
                                       value="{{ old('username') }}"
                                       autocomplete="username"
                                       required>
                            </div>

                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-envelope-open') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('email') is-invalid @enderror"
                                       type="email"
                                       name="email"
                                       id="email"
                                       placeholder="Email (Optional)"
                                       value="{{ old('email') }}"
                                       autocomplete="email">
                            </div>
                            <small class="text-muted d-block mb-3">Email is optional. If provided, you will need to verify it. You can add it later in your profile.</small>

                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-people') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('sponsor_name') is-invalid @enderror"
                                       type="text"
                                       name="sponsor_name"
                                       id="sponsor_name"
                                       placeholder="Sponsor Name or Referral Code (Optional)"
                                       value="{{ old('sponsor_name', session('referral_code') ?? request('ref')) }}"
                                       autocomplete="off"
                                       {{ session('referral_code') || request('ref') ? 'readonly' : '' }}>
                            </div>
                            @if(session('referral_code') || request('ref'))
                                <div class="alert alert-success mb-3">
                                    <small><svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                                    </svg><strong>Referral Code Applied:</strong> {{ session('referral_code') ?? request('ref') }}</small>
                                </div>
                            @else
                                <small class="text-muted d-block mb-3">Leave blank to be assigned to default sponsor (Admin)</small>
                            @endif

                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('password') is-invalid @enderror"
                                       type="password"
                                       name="password"
                                       id="password"
                                       placeholder="Password"
                                       autocomplete="new-password"
                                       required>
                            </div>

                            <div class="input-group mb-4">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control"
                                       type="password"
                                       name="password_confirmation"
                                       id="password_confirmation"
                                       placeholder="Repeat password"
                                       autocomplete="new-password"
                                       required>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="showPassword">
                                <label class="form-check-label" for="showPassword">
                                    Show password
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-decoration-none" data-coreui-toggle="modal" data-coreui-target="#termsModal">Terms of Service</a>
                                    and <a href="#" class="text-decoration-none" data-coreui-toggle="modal" data-coreui-target="#privacyModal">Privacy Policy</a>
                                </label>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-success" type="submit" id="submitBtn" disabled>Create Account</button>
                            </div>

                            <div class="text-center mt-3">
                                <p class="text-body-secondary">
                                    Already have an account?
                                    <a href="{{ route('login') }}" class="text-decoration-none">Sign in here</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Legal Modals -->
@include('legal.terms-of-service')
@include('legal.privacy-policy')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const termsCheckbox = document.getElementById('terms');
    const submitBtn = document.getElementById('submitBtn');
    const showPasswordCheckbox = document.getElementById('showPassword');
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    // Function to toggle submit button state
    function toggleSubmitButton() {
        if (termsCheckbox.checked) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('disabled');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled');
        }
    }

    // Function to toggle password visibility
    function togglePasswordVisibility() {
        const inputType = showPasswordCheckbox.checked ? 'text' : 'password';
        passwordInput.type = inputType;
        passwordConfirmationInput.type = inputType;
    }

    // Listen for checkbox changes
    termsCheckbox.addEventListener('change', toggleSubmitButton);
    showPasswordCheckbox.addEventListener('change', togglePasswordVisibility);

    // Initial state check
    toggleSubmitButton();
});
</script>
@endsection