@extends('layouts.auth')

@section('title', '404 - Page Not Found')

@section('content')
<div class="min-vh-100 d-flex flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="clearfix">
                    <h1 class="float-start display-3 me-4">404</h1>
                    <h4 class="pt-3">Oops! You're lost.</h4>
                    <p class="text-body-secondary">The page you are looking for was not found.</p>
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

                        @can('deposit_funds')
                        <a href="{{ route('wallet.deposit') }}" class="btn btn-success">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-plus') }}"></use>
                            </svg>
                            Deposit Funds
                        </a>
                        @endcan

                        @can('view_transactions')
                        <a href="{{ route('wallet.transactions') }}" class="btn btn-secondary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                            </svg>
                            View Transactions
                        </a>
                        @endcan
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-account-logout') }}"></use>
                            </svg>
                            Sign In
                        </a>

                        <a href="{{ route('register') }}" class="btn btn-outline-primary">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user-plus') }}"></use>
                            </svg>
                            Create Account
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
                        Need help? Contact our support team for assistance.
                    </small>
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