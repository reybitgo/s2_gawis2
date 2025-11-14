<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Gawis iHerbal') }}</title>
    <meta name="description" content="@yield('description', 'Gawis iHerbal E-Wallet Admin Dashboard')">
    <meta name="author" content="{{ config('app.name', 'Gawis iHerbal') }}">
    <meta name="keyword" content="E-Wallet,Digital Wallet,Financial Management,Transaction,Payment">

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
    <link href="{{ asset('coreui-template/vendors/simplebar/css/simplebar.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/chartjs/css/coreui-chartjs.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/icons/css/free.min.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/icons/css/flag.min.css') }}" rel="stylesheet">
    <link href="{{ asset('coreui-template/vendors/@coreui/icons/css/brand.min.css') }}" rel="stylesheet">

    <!-- Custom CSS for alignment -->
    <style>
        /* Align header topbar border with sidebar header border */
        .header .container-fluid.border-bottom {
            min-height: calc(4rem + 1px);
            display: flex;
            align-items: center;
        }
    </style>

    <!-- Additional CSS -->
    @stack('styles')

    <!-- Cart JavaScript -->
    <script>
        window.cartRoutes = {
            index: '{{ route("cart.index") }}',
            count: '{{ route("cart.count") }}',
            summary: '{{ route("cart.summary") }}',
            add: '/cart/add/{packageId}', // This is for packages only
            update: '/cart/update/{itemId}',
            remove: '/cart/remove/{itemId}',
            clear: '{{ route("cart.clear") }}'
        };
    </script>
</head>
<body>
    @include('partials.sidebar')

    <div class="wrapper d-flex flex-column min-vh-100">
        @include('partials.header')

        <div class="body flex-grow-1">
            <div class="container-lg h-auto px-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                        <svg class="icon me-3 flex-shrink-0" style="width: 2.5rem; height: 2.5rem;">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}"></use>
                        </svg>
                        <div class="flex-grow-1">{!! session('success') !!}</div>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <svg class="icon me-3 flex-shrink-0" style="width: 2.5rem; height: 2.5rem;">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        <div class="flex-grow-1">{!! session('error') !!}</div>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <svg class="icon me-3 flex-shrink-0" style="width: 2.5rem; height: 2.5rem;">
                            <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-warning') }}"></use>
                        </svg>
                        <div class="flex-grow-1">
                            @if($errors->count() === 1)
                                {!! $errors->first() !!}
                            @else
                                <div class="fw-bold mb-2">Please correct the following issues:</div>
                                @foreach($errors->all() as $error)
                                    <div class="mb-1">• {{ $error }}</div>
                                @endforeach
                            @endif
                        </div>
                        <button type="button" class="btn-close" data-coreui-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

        <footer class="footer p-0">
            <div class="container-lg text-center text-body-secondary py-3">
                © {{ date('Y') }} {{ config('app.name', 'Gawis iHerbal') }}. All rights reserved.
            </div>
        </footer>
    </div>

    <!-- CoreUI and Vendors JS -->
    <script src="{{ asset('coreui-template/vendors/@coreui/coreui-pro/js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/chart.js/js/chart.umd.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/@coreui/chartjs/js/coreui-chartjs.js') }}"></script>
    <script src="{{ asset('coreui-template/vendors/@coreui/utils/js/index.js') }}"></script>
    <script src="{{ asset('coreui-template/js/main.js') }}"></script>

    <!-- CoreUI Initialization -->
    <script>
        const header = document.querySelector("header.header");

        document.addEventListener("scroll", () => {
            if (header) {
                header.classList.toggle(
                    "shadow-sm",
                    document.documentElement.scrollTop > 0
                );
            }
        });

        // Initialize CoreUI theme system
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing CoreUI theme system...');

            // Force light mode only
            const storedTheme = 'light';
            console.log('Forced theme to light mode');

            // Set initial theme to light
            document.documentElement.setAttribute('data-coreui-theme', 'light');

            // Initialize theme switcher buttons
            const themeButtons = document.querySelectorAll('[data-coreui-theme-value]');
            console.log('Found theme buttons:', themeButtons.length);

            themeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const theme = this.getAttribute('data-coreui-theme-value');
                    console.log('Theme button clicked:', theme);

                    // Set theme attribute
                    document.documentElement.setAttribute('data-coreui-theme', theme);

                    // Store in localStorage
                    localStorage.setItem('coreui-theme', theme);

                    // Dispatch custom event for theme change
                    const event = new CustomEvent('ColorSchemeChange', {
                        detail: { theme: theme }
                    });
                    document.documentElement.dispatchEvent(event);

                    console.log('Theme changed to:', theme);
                });
            });

            // Update active button state
            function updateActiveThemeButton(theme) {
                themeButtons.forEach(btn => {
                    btn.classList.remove('active');
                    if (btn.getAttribute('data-coreui-theme-value') === theme) {
                        btn.classList.add('active');
                    }
                });
            }

            // Set initial active state
            updateActiveThemeButton(storedTheme);

            // Listen for theme changes to update button states
            document.documentElement.addEventListener('ColorSchemeChange', (e) => {
                updateActiveThemeButton(e.detail.theme);
                updateLogosForTheme(e.detail.theme);
            });

            // Function to ensure logos are properly displayed for the current theme
            function updateLogosForTheme(theme) {
                const darkLogos = document.querySelectorAll('.logo-dark');
                const lightLogos = document.querySelectorAll('.logo-light');

                // Check if we should use dark theme (either explicitly dark or auto with dark system preference)
                const shouldUseDarkTheme = theme === 'dark' ||
                    (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                if (shouldUseDarkTheme) {
                    darkLogos.forEach(logo => logo.style.display = 'none');
                    lightLogos.forEach(logo => logo.style.display = '');
                } else {
                    darkLogos.forEach(logo => logo.style.display = '');
                    lightLogos.forEach(logo => logo.style.display = 'none');
                }
            }

            // Initialize logos for light theme only
            updateLogosForTheme('light');

            // Listen for system theme changes when in auto mode
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                const currentTheme = localStorage.getItem('coreui-theme') || 'light';
                if (currentTheme === 'auto') {
                    updateLogosForTheme('auto');
                }
            });

            console.log('CoreUI theme system initialized');

            // Initialize sidebar to ensure it starts in full width mode
            // CoreUI's unfoldable toggle will handle the narrow mode with hover expand
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                // Remove any narrow classes that might be present
                sidebar.classList.remove('sidebar-narrow');
                sidebar.classList.remove('sidebar-narrow-unfoldable');
                console.log('Sidebar initialized in full width mode for CoreUI unfoldable toggle');
            }
        });
    </script>

    <!-- Cart JavaScript -->
    <script>
        // Cart management functionality
        class CartManager {
            constructor() {
                this.init();
            }

            init() {
                this.updateCartCount();
                this.bindEvents();
            }

            async updateCartCount() {
                try {
                    const response = await fetch(window.cartRoutes.count);
                    const data = await response.json();
                    this.setCartCount(data.count);
                } catch (error) {
                    console.error('Error updating cart count:', error);
                }
            }

            setCartCount(count) {
                const cartBadge = document.getElementById('cart-count');
                if (cartBadge) {
                    cartBadge.textContent = count;
                    cartBadge.style.display = count > 0 ? 'inline' : 'none';
                }
            }

            async refreshCartDropdown() {
                try {
                    const response = await fetch(window.cartRoutes.summary);
                    const data = await response.json();

                    if (data.summary) {
                        this.updateCartDropdownContent(data.summary);
                    }
                } catch (error) {
                    console.error('Error refreshing cart dropdown:', error);
                }
            }

            updateCartDropdownContent(cartSummary) {
                const dropdownContent = document.getElementById('cart-dropdown-content');
                if (!dropdownContent) return;

                if (cartSummary.is_empty) {
                    dropdownContent.innerHTML = `
                        <div class="dropdown-item-text text-center p-4">
                            <svg class="icon icon-xl text-muted mb-2">
                                <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                            </svg>
                            <div class="text-muted small mb-3">Your cart is empty</div>
                            <a href="{{ route('packages.index') }}" class="btn btn-primary btn-sm">
                                Browse Packages
                            </a>
                        </div>
                    `;
                } else {
                    let itemsHtml = '';
                    Object.values(cartSummary.items).forEach(item => {
                        itemsHtml += `
                            <div class="dropdown-item-text border-bottom p-3">
                                <div class="d-flex align-items-center">
                                    <img src="${item.image_url}" alt="${item.name}" class="rounded me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">${item.name.length > 25 ? item.name.substring(0, 25) + '...' : item.name}</div>
                                        <div class="text-muted small">
                                            ${item.quantity} × $${parseFloat(item.price).toFixed(2)}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold small">$${(item.price * item.quantity).toFixed(2)}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    dropdownContent.innerHTML = `
                        <div class="dropdown-header bg-light fw-semibold text-dark border-bottom">
                            Cart (${cartSummary.item_count} items)
                        </div>
                        ${itemsHtml}
                        <div class="dropdown-header bg-light border-bottom">
                            <div class="d-flex justify-content-between">
                                <span>Total:</span>
                                <span class="fw-bold">$${cartSummary.total.toFixed(2)}</span>
                            </div>
                        </div>
                        <div class="dropdown-item-text p-3">
                            <div class="d-grid gap-2">
                                <a href="{{ route('cart.index') }}" class="btn btn-primary btn-sm">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
                                    </svg>
                                    View Cart
                                </a>
                                <a href="{{ route('checkout.index') }}" class="btn btn-outline-primary btn-sm">
                                    <svg class="icon me-2">
                                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-credit-card') }}"></use>
                                    </svg>
                                    Checkout
                                </a>
                            </div>
                        </div>
                    `;
                }
            }

            async addToCart(packageId, quantity = 1) {
                try {
                    const url = window.cartRoutes.add.replace('{packageId}', packageId);
                    console.log('Adding to cart:', { packageId, quantity, url });

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ quantity: quantity })
                    });

                    console.log('Response status:', response.status);

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('HTTP error response:', errorText);
                        this.showMessage('Server error: ' + response.status, 'error');
                        return false;
                    }

                    const data = await response.json();
                    console.log('Response data:', data);

                    if (data.success) {
                        this.setCartCount(data.cart_count);
                        this.refreshCartDropdown();
                        this.showMessage(data.message, 'success');
                        return true;
                    } else {
                        this.showMessage(data.message, 'error');
                        return false;
                    }
                } catch (error) {
                    console.error('Error adding to cart:', error);
                    this.showMessage('Error adding item to cart: ' + error.message, 'error');
                    return false;
                }
            }

            showMessage(message, type = 'info') {
                // Create toast notification
                const toast = document.createElement('div');
                toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-coreui-dismiss="alert"></button>
                `;

                document.body.appendChild(toast);

                // Auto remove after 3 seconds
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 3000);
            }

            bindEvents() {
                // Bind add to cart buttons
                document.addEventListener('click', async (e) => {
                    if (e.target.matches('.add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
                        e.preventDefault();
                        const button = e.target.matches('.add-to-cart-btn') ? e.target : e.target.closest('.add-to-cart-btn');
                        const packageId = button.dataset.packageId;
                        const quantity = button.dataset.quantity || 1;

                        if (packageId) {
                            button.disabled = true;
                            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

                            const success = await this.addToCart(packageId, quantity);

                            if (success) {
                                // Update button to show "In Cart" state
                                const isLargeButton = button.classList.contains('btn-lg');
                                const buttonSize = isLargeButton ? 'btn-lg' : 'btn-sm';
                                const iconStyle = isLargeButton ? '' : 'style="width: 14px; height: 14px;"';

                                button.className = `btn btn-success ${buttonSize}`;
                                button.disabled = true;
                                button.innerHTML = `<svg class="icon me-${isLargeButton ? '2' : '1'}" ${iconStyle}><use xlink:href="{{ asset("coreui-template/vendors/@coreui/icons/svg/free.svg#cil-check") }}"></use></svg>${isLargeButton ? 'Already in Cart' : 'In Cart'}`;

                                // If on package detail page, also add View Cart button
                                if (isLargeButton && !button.parentElement.querySelector('.view-cart-btn')) {
                                    const viewCartBtn = document.createElement('a');
                                    viewCartBtn.href = window.cartRoutes ? window.cartRoutes.index : '/cart';
                                    viewCartBtn.className = 'btn btn-outline-primary view-cart-btn';
                                    viewCartBtn.innerHTML = '<svg class="icon me-2"><use xlink:href="{{ asset("coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart") }}"></use></svg>View Cart';
                                    button.parentElement.insertBefore(viewCartBtn, button.nextSibling);
                                }
                            } else {
                                // Restore original button state on failure
                                const isLargeButton = button.classList.contains('btn-lg');
                                button.disabled = false;
                                button.innerHTML = `<svg class="icon me-2"><use xlink:href="{{ asset("coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart") }}"></use></svg>Add to Cart`;
                            }
                        }
                    }
                });
            }
        }

        // Initialize cart manager when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Cart routes initialized:', window.cartRoutes);
            window.cartManager = new CartManager();
        });
    </script>

    <!-- Additional JavaScript -->
    @stack('scripts')
</body>
</html>