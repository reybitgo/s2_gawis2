@extends('layouts.auth')

@section('title', '429 - Too Many Requests')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="clearfix">
                    <h1 class="float-start display-3 me-4">429</h1>
                    <h4 class="pt-3">Too Many Requests</h4>
                    <p class="text-body-secondary">You have made too many requests in a short period of time.</p>
                </div>

                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <svg class="icon me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                    </svg>
                    <div>
                        <strong>Rate Limit Exceeded</strong><br>
                        You have exceeded the maximum number of requests allowed in a given time period.
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <svg class="icon me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                    </svg>
                    <div>
                        <strong>What you can do:</strong><br>
                        • Wait a few minutes before trying again<br>
                        • Slow down your requests<br>
                        • Contact support if this continues
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
                            Sign In
                        </a>
                    @endauth

                    <button onclick="setTimeout(() => window.location.reload(), 60000); this.disabled = true; this.innerHTML = 'Waiting 60s...';" class="btn btn-warning">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-clock') }}"></use>
                        </svg>
                        Wait & Retry
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
                        Rate limits help protect our services. Please wait before making more requests.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection