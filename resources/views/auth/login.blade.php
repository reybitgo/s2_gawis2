@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center pt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card-group d-block d-md-flex row">
                    <div class="card col-md-7 p-4 mb-0">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <img class="logo-dark" src="{{ asset('coreui-template/assets/brand/gawis_logo.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                                <img class="logo-light" src="{{ asset('coreui-template/assets/brand/gawis_logo_light.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h1>Login</h1>
                                <a href="{{ url('/') }}" class="small">&larr; Back to Home</a>
                            </div>
                            <p class="text-body-secondary">Sign In to your account</p>

                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif


                            @if ($errors->any())
                                <div class="alert alert-danger d-flex align-items-start">
                                    <svg class="icon icon-lg me-2 flex-shrink-0 mt-1">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                                    </svg>
                                    <div>
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <form action="{{ route('login') }}" method="POST">
                                @csrf
                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <svg class="icon">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                        </svg>
                                    </span>
                                    <input class="form-control @error('email') is-invalid @enderror"
                                           type="text"
                                           name="email"
                                           id="email"
                                           placeholder="Email address or Username"
                                           value="{{ old('email') }}"
                                           autocomplete="email"
                                           required>
                                </div>

                                <div class="input-group mb-4">
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
                                           autocomplete="current-password"
                                           required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="showPassword">
                                            <label class="form-check-label" for="showPassword">
                                                Show password
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                            <label class="form-check-label" for="remember">
                                                Remember me
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <button class="btn btn-primary px-4" type="submit">Login</button>
                                    </div>
                                    <div class="col-6 text-end">
                                        <a href="{{ route('password.request') }}" class="btn btn-link px-0">Forgot password?</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card col-md-5 text-white bg-primary py-5">
                        <div class="card-body text-center">
                            <div>
                                <h2>Sign up</h2>
                                <p>Join our secure E-Wallet platform and manage your digital transactions with confidence and ease.</p>
                                <a href="{{ route('register') }}" class="btn btn-lg btn-outline-light mt-3">Register Now!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Database Reset Success Modal --}}
@if (session('success') || session('error'))
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true" data-coreui-backdrop="static" data-coreui-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            @if (session('success'))
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="resetModalLabel">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                        </svg>
                        Database Reset Successful
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">{{ session('success') }}</p>

                    @if (session('reset_info'))
                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading">
                                <svg class="icon me-1">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
                                </svg>
                                Default Credentials
                            </h6>
                            <hr>
                            <div class="mb-2">
                                <span class="badge bg-primary me-2">Admin</span>
                                <code class="text-dark">admin@gawisherbal.com</code>
                                <span class="mx-1">/</span>
                                <code class="text-dark">Admin123!@#</code>
                            </div>
                            <div>
                                <span class="badge bg-info me-2">Member</span>
                                <code class="text-dark">member@gawisherbal.com</code>
                                <span class="mx-1">/</span>
                                <code class="text-dark">Member123!@#</code>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-coreui-dismiss="modal">Got it!</button>
                </div>
            @endif

            @if (session('error'))
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="resetModalLabel">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-x-circle') }}"></use>
                        </svg>
                        Database Reset Failed
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-0">
                        <h6 class="alert-heading">Error Details:</h6>
                        <p class="mb-0">{{ session('error') }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-coreui-dismiss="modal">Close</button>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showPasswordCheckbox = document.getElementById('showPassword');
    const passwordInput = document.getElementById('password');

    // Function to toggle password visibility
    function togglePasswordVisibility() {
        const inputType = showPasswordCheckbox.checked ? 'text' : 'password';
        passwordInput.type = inputType;
    }

    // Listen for checkbox changes
    showPasswordCheckbox.addEventListener('change', togglePasswordVisibility);

    // Auto-show reset modal if session has reset info
    @if (session('success') || session('error'))
        const resetModal = document.getElementById('resetModal');
        if (resetModal) {
            const modal = new coreui.Modal(resetModal);
            modal.show();
        }
    @endif
});
</script>
@endsection