@extends('layouts.auth')

@section('title', '403 - Forbidden')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="clearfix">
                    <h1 class="float-start display-3 me-4">403</h1>
                    <h4 class="pt-3">Access Forbidden</h4>
                    <p class="text-body-secondary">You don't have permission to access this resource.</p>
                </div>

                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                    <svg class="icon me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-ban') }}"></use>
                    </svg>
                    <div>
                        <strong>Access Denied</strong><br>
                        You do not have sufficient privileges to access this page or perform this action.
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <svg class="icon me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-info') }}"></use>
                    </svg>
                    <div>
                        <strong>What you can do:</strong><br>
                        • Contact your administrator for access<br>
                        • Return to dashboard<br>
                        • Check if you're logged in with the correct account
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

                    <button onclick="window.history.back()" class="btn btn-outline-secondary">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-arrow-left') }}"></use>
                        </svg>
                        Go Back
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <small class="text-body-secondary">
                        If you believe this is an error, please contact your system administrator.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection