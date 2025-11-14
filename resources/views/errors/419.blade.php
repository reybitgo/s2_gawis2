@extends('layouts.auth')

@section('title', '419 - Page Expired')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="clearfix">
                    <h1 class="float-start display-3 me-4">419</h1>
                    <h4 class="pt-3">Page Expired</h4>
                    <p class="text-body-secondary">Your session has expired due to inactivity.</p>
                </div>

                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <svg class="icon me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                    </svg>
                    <div>
                        <strong>Session Timeout</strong><br>
                        Your session token has expired for security reasons. This usually happens when the page has been idle for too long.
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <svg class="icon me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                    </svg>
                    <div>
                        <strong>What you can do:</strong><br>
                        • Refresh the page to get a new session<br>
                        • Go back and try again<br>
                        • Return to dashboard to continue
                    </div>
                </div>

                <div class="d-grid gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-home') }}"></use>
                            </svg>
                            Back to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-account-logout') }}"></use>
                            </svg>
                            Sign In Again
                        </a>
                    @endauth

                    <button onclick="window.location.reload()" class="btn btn-warning">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                        </svg>
                        Refresh Page
                    </button>

                    <button onclick="window.history.back()" class="btn btn-outline-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Go Back
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <small class="text-body-secondary">
                        If this problem persists, please contact support or try logging out and back in.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection