@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center pt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card p-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <!-- Add theme-aware logos here -->
                            <div class="mb-3">
                                <img class="logo-dark" src="{{ asset('coreui-template/assets/brand/gawis_logo.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                                <img class="logo-light" src="{{ asset('coreui-template/assets/brand/gawis_logo_light.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
                            </div>
                            <h1>Reset Password</h1>
                            <p class="text-body-secondary">Enter your email address and we'll send you a password reset link</p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf
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
                                       placeholder="Enter your email address"
                                       value="{{ old('email') }}"
                                       autocomplete="email"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <button class="btn btn-primary w-100" type="submit">
                                        <svg class="icon me-2">
                                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-paper-plane') }}"></use>
                                        </svg>
                                        Send Password Reset Link
                                    </button>
                                </div>
                            </div>

                            <div class="text-center">
                                <p class="text-body-secondary">
                                    Remember your password?
                                    <a href="{{ route('login') }}" class="text-decoration-none">
                                        Back to login
                                    </a>
                                </p>
                            </div>

                            <div class="text-center mt-3">
                                <a href="{{ url('/') }}" class="text-decoration-none">&larr; Back to Home</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection