<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Gawis iHerbal') }} - @yield('title', 'Authentication')</title>
    <meta name="description" content="@yield('description', 'Gawis iHerbal E-Wallet Authentication')">
    <meta name="author" content="{{ config('app.name', 'Gawis iHerbal') }}">
    <meta name="keyword" content="E-Wallet,Digital Wallet,Financial Management,Transaction,Payment">

    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">

    <!-- Favicon and PWA Icons -->
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('coreui-template/assets/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('coreui-template/assets/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('coreui-template/assets/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('coreui-template/assets/favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('coreui-template/assets/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('coreui-template/assets/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('coreui-template/assets/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('coreui-template/assets/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('coreui-template/assets/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('coreui-template/assets/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('coreui-template/assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('coreui-template/assets/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('coreui-template/assets/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('coreui-template/assets/favicon/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('coreui-template/assets/favicon/apple-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">

    <!-- CoreUI CSS -->
    <link rel="stylesheet" href="{{ asset('coreui-template/vendors/simplebar/css/simplebar.css') }}">
    <link href="{{ asset('coreui-template/css/style.css') }}" rel="stylesheet">
</head>
<body>
    @yield('content')

    <!-- CoreUI JavaScript -->
    <script src="{{ asset('coreui-template/vendors/@coreui/coreui-pro/js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/simplebar/js/simplebar.min.js') }}"></script>

    <!-- Theme Preference System -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing auth page theme system...');

            // Get stored theme or default to light
            const storedTheme = localStorage.getItem('coreui-theme') || 'light';
            console.log('Auth page stored theme:', storedTheme);

            // Function to determine if we should use dark theme
            function shouldUseDarkTheme(theme) {
                return theme === 'dark' ||
                    (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            }

            // Set initial theme
            const isDark = shouldUseDarkTheme(storedTheme);
            document.documentElement.setAttribute('data-coreui-theme', isDark ? 'dark' : 'light');

            // Update logos for theme
            function updateLogosForTheme(theme) {
                const darkLogos = document.querySelectorAll('.logo-dark');
                const lightLogos = document.querySelectorAll('.logo-light');

                const useDark = shouldUseDarkTheme(theme);

                if (useDark) {
                    darkLogos.forEach(logo => logo.style.display = 'none');
                    lightLogos.forEach(logo => logo.style.display = '');
                } else {
                    darkLogos.forEach(logo => logo.style.display = '');
                    lightLogos.forEach(logo => logo.style.display = 'none');
                }
            }

            // Initialize logos
            updateLogosForTheme(storedTheme);

            // Listen for system theme changes when in auto mode
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                const currentTheme = localStorage.getItem('coreui-theme') || 'light';
                if (currentTheme === 'auto') {
                    const newIsDark = shouldUseDarkTheme('auto');
                    document.documentElement.setAttribute('data-coreui-theme', newIsDark ? 'dark' : 'light');
                    updateLogosForTheme('auto');
                }
            });

            console.log('Auth page theme system initialized');
        });
    </script>

    @stack('scripts')
</body>
</html>