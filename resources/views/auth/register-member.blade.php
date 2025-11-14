@extends('layouts.admin')

@section('title', 'Register New Member')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user-follow') }}"></use>
                        </svg>
                        Register New Member
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('member.register.process') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('fullname') is-invalid @enderror"
                                       type="text"
                                       name="fullname"
                                       id="fullname"
                                       placeholder="Enter full name"
                                       value="{{ old('fullname') }}"
                                       autocomplete="name"
                                       required>
                                @error('fullname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('username') is-invalid @enderror"
                                       type="text"
                                       name="username"
                                       id="username"
                                       placeholder="Enter username (letters, numbers, underscores only)"
                                       value="{{ old('username') }}"
                                       autocomplete="username"
                                       required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Username can only contain letters, numbers, and underscores.</small>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-muted">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-envelope-open') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('email') is-invalid @enderror"
                                       type="email"
                                       name="email"
                                       id="email"
                                       placeholder="Enter email address (optional)"
                                       value="{{ old('email') }}"
                                       autocomplete="email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Email is optional. If provided, the member will need to verify it. They can add it later in their profile.</small>
                        </div>

                        <div class="mb-3">
                            <label for="sponsor_name" class="form-label">Sponsor Name/Username <span class="text-muted">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-people') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('sponsor_name') is-invalid @enderror"
                                       type="text"
                                       name="sponsor_name"
                                       id="sponsor_name"
                                       placeholder="Enter sponsor username or leave default"
                                       value="{{ old('sponsor_name', $sponsor->username) }}"
                                       autocomplete="off">
                                @error('sponsor_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Default: {{ $sponsor->fullname }} ({{ $sponsor->username }}). You can change this to assign a different sponsor.</small>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control @error('password') is-invalid @enderror"
                                       type="password"
                                       name="password"
                                       id="password"
                                       placeholder="Enter password (minimum 8 characters)"
                                       autocomplete="new-password"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <svg class="icon">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-lock-locked') }}"></use>
                                    </svg>
                                </span>
                                <input class="form-control"
                                       type="password"
                                       name="password_confirmation"
                                       id="password_confirmation"
                                       placeholder="Re-enter password"
                                       autocomplete="new-password"
                                       required>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="showPassword">
                            <label class="form-check-label" for="showPassword">
                                Show password
                            </label>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I confirm that the new member agrees to the
                                <a href="#" class="text-decoration-none" data-coreui-toggle="modal" data-coreui-target="#termsModal">Terms of Service</a>
                                and <a href="#" class="text-decoration-none" data-coreui-toggle="modal" data-coreui-target="#privacyModal">Privacy Policy</a>
                                <span class="text-danger">*</span>
                            </label>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary" type="submit" id="submitBtn" disabled>
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user-follow') }}"></use>
                                </svg>
                                Register New Member
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="pb-5"></div>

<!-- Include Legal Modals -->
@include('legal.terms-of-service')
@include('legal.privacy-policy')

@push('styles')
<style>
.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.card-header.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    border-bottom: none;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3 0%, #6a3f8e 100%);
}

.btn-primary:disabled {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0.65;
}
</style>
@endpush

@push('scripts')
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
@endpush
@endsection
