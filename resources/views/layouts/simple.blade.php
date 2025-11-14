<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>@yield('title', 'Database Reset') - {{ config('app.name', 'Gawis iHerbal') }}</title>

    <!-- CoreUI CSS -->
    <link href="{{ asset('coreui-template/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/icons/css/free.min.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <div class="bg-light min-vh-100 d-flex flex-row align-items-center">
        <div class="container">
            @yield('content')
        </div>
    </div>

    <!-- CoreUI and Vendors JS -->
    <script src="{{ asset('coreui-template/vendors/@coreui/coreui-pro/js/coreui.bundle.min.js') }}"></script>

    @stack('scripts')
</body>
</html>
