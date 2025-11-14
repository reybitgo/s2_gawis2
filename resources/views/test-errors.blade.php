@extends('layouts.auth')

@section('title', 'Test Error Pages')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Test Error Pages</h4>
                        <small class="text-muted">Click any button below to test the error pages</small>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>Note:</strong> These are test routes for development. Remove them in production!
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-danger">404 - Not Found</h5>
                                        <p class="card-text">Page not found error</p>
                                        <a href="{{ route('test.404') }}" class="btn btn-danger">Test 404 Error</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-danger">500 - Server Error</h5>
                                        <p class="card-text">Internal server error</p>
                                        <a href="{{ route('test.500') }}" class="btn btn-danger">Test 500 Error</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-warning">419 - Page Expired</h5>
                                        <p class="card-text">CSRF token expired</p>
                                        <a href="{{ route('test.419') }}" class="btn btn-warning">Test 419 Error</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-warning">403 - Forbidden</h5>
                                        <p class="card-text">Access denied error</p>
                                        <a href="{{ route('test.403') }}" class="btn btn-warning">Test 403 Error</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-info">429 - Rate Limited</h5>
                                        <p class="card-text">Too many requests</p>
                                        <a href="{{ route('test.429') }}" class="btn btn-info">Test 429 Error</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card border-secondary">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-secondary">Natural 404</h5>
                                        <p class="card-text">Visit non-existent page</p>
                                        <a href="/non-existent-page" class="btn btn-secondary">Test Natural 404</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <svg class="icon me-2">
                                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-home') }}"></use>
                                </svg>
                                Back to Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection