@extends('layouts.admin')

@section('title', 'Email Verification')

@section('content')
<div class="d-flex justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card">
            <div class="card-header text-center">
                <svg class="icon icon-2xl text-warning mb-2">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-envelope-letter') }}"></use>
                </svg>
                <h4 class="card-title">Verify Your Email Address</h4>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <p class="text-body-secondary">
                        A verification link has been sent to your email address. You can continue using the site normally, but verifying your email helps with account security.
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                        </svg>
                        A new verification link has been sent to your email address.
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="alert alert-info" role="alert">
                    <svg class="icon me-2">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                    </svg>
                    <strong>Optional verification:</strong> Email verification is completely optional. You can skip this and continue using the site normally, or verify for enhanced security.
                </div>

                <div class="d-grid gap-2">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                            </svg>
                            Resend Verification Email (Optional)
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-footer">
                <div class="row g-2">
                    <div class="col-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-success w-100">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check') }}"></use>
                            </svg>
                            Skip & Continue
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary w-100">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                            </svg>
                            Back to Profile
                        </a>
                    </div>
                    <div class="col-4">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-account-logout') }}"></use>
                                </svg>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bottom spacing for better visual layout -->
<div class="pb-5"></div>
@endsection