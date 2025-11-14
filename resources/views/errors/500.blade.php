@extends('layouts.auth')

@section('title', '500 - Server Error')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="clearfix">
                    <h1 class="float-start display-3 me-4">500</h1>
                    <h4 class="pt-3">Houston, we have a problem!</h4>
                    <p class="text-body-secondary">The page you are looking for is temporarily unavailable.</p>
                </div>

                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <svg class="icon me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                    </svg>
                    <div>
                        <strong>What happened?</strong><br>
                        Our servers encountered an unexpected error. Our technical team has been automatically notified and is working to resolve this issue.
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <svg class="icon me-3">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-shield-alt') }}"></use>
                    </svg>
                    <div>
                        <strong>Your data is safe</strong><br>
                        Your account and wallet information remain secure. This is a temporary technical issue.
                    </div>
                </div>

                <div class="input-group mb-4">
                    <span class="input-group-text">
                        <svg class="icon">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass') }}"></use>
                        </svg>
                    </span>
                    <input class="form-control" id="searchInput" type="text" placeholder="What are you looking for?">
                    <button class="btn btn-info" type="button" onclick="searchSite()">Search</button>
                </div>

                <div class="d-grid gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-home') }}"></use>
                            </svg>
                            Back to Dashboard
                        </a>

                        @can('view_transactions')
                        <a href="{{ route('wallet.transactions') }}" class="btn btn-secondary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                            </svg>
                            Check Wallet Status
                        </a>
                        @endcan
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-account-logout') }}"></use>
                            </svg>
                            Sign In
                        </a>
                    @endauth

                    <button onclick="window.location.reload()" class="btn btn-warning">
                        <svg class="icon me-2">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-reload') }}"></use>
                        </svg>
                        Try Again
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
                        Error ID: <code>{{ Str::random(8) }}</code> •
                        <span class="text-muted">{{ now()->format('Y-m-d H:i:s T') }}</span>
                    </small>
                    <div class="mt-1">
                        <small class="text-body-secondary">
                            Please include this information when contacting support.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function searchSite() {
    const searchTerm = document.getElementById('searchInput').value;
    if (searchTerm.trim()) {
        window.location.href = '{{ route("dashboard") }}?search=' + encodeURIComponent(searchTerm);
    }
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchSite();
    }
});
</script>
@endpush
@endsection