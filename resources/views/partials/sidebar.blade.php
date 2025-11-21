<div class="sidebar sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
        <div class="sidebar-brand">
            <!-- Dark theme logos (default) -->
            <img class="sidebar-brand-full logo-dark" src="{{ asset('coreui-template/assets/brand/gawis_logo.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
            <img class="sidebar-brand-narrow logo-dark" src="{{ asset('coreui-template/assets/brand/gawis.png') }}" width="32" height="32" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
            <!-- Light theme logos -->
            <img class="sidebar-brand-full logo-light" src="{{ asset('coreui-template/assets/brand/gawis_logo_light.png') }}" width="110" height="39" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
            <img class="sidebar-brand-narrow logo-light" src="{{ asset('coreui-template/assets/brand/gawis_light.png') }}" width="32" height="32" alt="{{ config('app.name', 'Gawis iHerbal') }} Logo" />
        </div>
        <button class="btn-close d-lg-none" type="button" aria-label="Close" onclick='coreui.Sidebar.getInstance(document.querySelector("#sidebar")).toggle()'></button>
    </div>
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link{{ Request::routeIs('dashboard') ? ' active' : '' }}" href="{{ route('dashboard') }}">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
                </svg>
                <span>Dashboard</span>
            </a>
        </li>

        @auth
        @if(auth()->user()->hasRole('admin'))
            <!-- Admin Section -->
            <li class="nav-title">Administration</li>
            <li class="nav-item">
                <a class="nav-link{{ Request::routeIs('admin.dashboard') ? ' active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <svg class="nav-icon">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-pie') }}"></use>
                    </svg>
                    <span>Admin Dashboard</span>
                </a>
            </li>

            <!-- Admin Management Group -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle{{ Request::routeIs('admin.*') && !Request::routeIs('admin.dashboard') ? ' active' : '' }}" href="#">
                    <svg class="nav-icon">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-puzzle') }}"></use>
                    </svg>
                    <span>Management</span>
                </a>
                <ul class="nav-group-items compact">
                    @can('wallet_management')
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.wallet.*') ? ' active' : '' }}" href="{{ route('admin.wallet.management') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Wallet Management
                        </a>
                    </li>
                    @endcan
                    @can('transaction_approval')
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.transaction.*') ? ' active' : '' }}" href="{{ route('admin.transaction.approval') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Transaction Approval
                        </a>
                    </li>
                    @endcan
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.users') ? ' active' : '' }}" href="{{ route('admin.users') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.packages.*') ? ' active' : '' }}" href="{{ route('admin.packages.index') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Package Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.products.*') ? ' active' : '' }}" href="{{ route('admin.products.index') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Product Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.orders.*') ? ' active' : '' }}" href="{{ route('admin.orders.index') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Order Management
                            @php
                                $paidOrdersCount = \App\Models\Order::where('status', 'paid')->count();
                            @endphp
                            @if($paidOrdersCount > 0)
                                <span class="badge badge-sm bg-info ms-auto">{{ $paidOrdersCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.returns.*') ? ' active' : '' }}" href="{{ route('admin.returns.index') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Return Requests
                            @php
                                $pendingReturnsCount = \App\Models\ReturnRequest::where('status', 'pending')->count();
                            @endphp
                            @if($pendingReturnsCount > 0)
                                <span class="badge badge-sm bg-warning ms-auto">{{ $pendingReturnsCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.settings.*') ? ' active' : '' }}" href="{{ route('admin.settings.index') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Application Settings
                        </a>
                    </li>
                    @can('system_settings')
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.reports*') ? ' active' : '' }}" href="{{ route('admin.reports') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Reports & Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.logs') ? ' active' : '' }}" href="{{ route('admin.logs') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            System Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.system.*') ? ' active' : '' }}" href="{{ route('admin.system.settings') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            System Settings
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>

            <!-- Monthly Quota System (Phase 4) -->
            <li class="nav-group">
                <a class="nav-link nav-group-toggle{{ Request::routeIs('admin.monthly-quota.*') ? ' active' : '' }}" href="#">
                    <svg class="nav-icon">
                        <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-task') }}"></use>
                    </svg>
                    <span>Monthly Quota</span>
                </a>
                <ul class="nav-group-items compact">
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.monthly-quota.index') ? ' active' : '' }}" href="{{ route('admin.monthly-quota.index') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.monthly-quota.packages') ? ' active' : '' }}" href="{{ route('admin.monthly-quota.packages') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Package Quotas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link{{ Request::routeIs('admin.monthly-quota.reports*') ? ' active' : '' }}" href="{{ route('admin.monthly-quota.reports') }}">
                            <span class="nav-icon">
                                <span class="nav-icon-bullet"></span>
                            </span>
                            Quota Reports
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @endauth

        <!-- E-Wallet Section -->
        <li class="nav-title">E-Wallet</li>
        <li class="nav-group">
            <a class="nav-link nav-group-toggle{{ Request::routeIs('wallet.*') ? ' active' : '' }}" href="#">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-wallet') }}"></use>
                </svg>
                <span>Wallet Operations</span>
            </a>
            <ul class="nav-group-items compact">
                @can('deposit_funds')
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('wallet.deposit*') ? ' active' : '' }}" href="{{ route('wallet.deposit') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Deposit Funds
                    </a>
                </li>
                @endcan
                @can('transfer_funds')
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('wallet.transfer*') ? ' active' : '' }}" href="{{ route('wallet.transfer') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Transfer Funds
                    </a>
                </li>
                @endcan
                @can('withdraw_funds')
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('wallet.withdraw*') ? ' active' : '' }}" href="{{ route('wallet.withdraw') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Withdraw Funds
                    </a>
                </li>
                @endcan
                @can('view_transactions')
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('wallet.transactions*') ? ' active' : '' }}" href="{{ route('wallet.transactions') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Transaction History
                    </a>
                </li>
                @endcan
            </ul>
        </li>

        <!-- Member Actions Section -->
        <li class="nav-title">Member Actions</li>
        <li class="nav-group">
            <a class="nav-link nav-group-toggle{{ Request::routeIs('member.unilevel.genealogy') || Request::routeIs('member.mlm.genealogy') ? ' active' : '' }}" href="#">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-sitemap') }}"></use>
                </svg>
                <span>Genealogy</span>
            </a>
            <ul class="nav-group-items compact">
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('member.mlm.genealogy') ? ' active' : '' }}" href="{{ route('member.mlm.genealogy') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        MLM
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('member.unilevel.genealogy') ? ' active' : '' }}" href="{{ route('member.unilevel.genealogy') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Unilevel
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-group">
            <a class="nav-link nav-group-toggle{{ Request::routeIs('member.quota.*') ? ' active' : '' }}" href="#">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-chart-line') }}"></use>
                </svg>
                <span>My Quota</span>
            </a>
            <ul class="nav-group-items compact">
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('member.quota.index') ? ' active' : '' }}" href="{{ route('member.quota.index') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Current Month
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('member.quota.history') ? ' active' : '' }}" href="{{ route('member.quota.history') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Quota History
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a class="nav-link{{ Request::routeIs('member.register.*') ? ' active' : '' }}" href="{{ route('member.register.show') }}">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-user-follow') }}"></use>
                </svg>
                <span>Register New Member</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link{{ Request::routeIs('referral.*') ? ' active' : '' }}" href="{{ route('referral.index') }}">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-share-alt') }}"></use>
                </svg>
                <span>My Referral Link</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link{{ Request::routeIs('activities.*') ? ' active' : '' }}" href="{{ route('activities.index') }}">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-list') }}"></use>
                </svg>
                <span>My Activity Logs</span>
            </a>
        </li>

        <!-- E-commerce Section -->
        <li class="nav-title">E-commerce</li>
        <li class="nav-group">
            <a class="nav-link nav-group-toggle{{ Request::routeIs('packages.*') ? ' active' : '' }}" href="#">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('coreui-template/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
                </svg>
                <span>Shopping</span>
            </a>
            <ul class="nav-group-items compact">
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('packages.index') ? ' active' : '' }}" href="{{ route('packages.index') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Browse Packages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('products.index') ? ' active' : '' }}" href="{{ route('products.index') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Browse Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('cart.*') ? ' active' : '' }}" href="{{ route('cart.index') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Shopping Cart
                        @php
                            $cartCount = app('App\Services\CartService')->getItemCount();
                        @endphp
                        @if($cartCount > 0)
                            <span class="badge bg-primary ms-auto">{{ $cartCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link{{ Request::routeIs('orders.*') ? ' active' : '' }}" href="{{ route('orders.index') }}">
                        <span class="nav-icon">
                            <span class="nav-icon-bullet"></span>
                        </span>
                        Order History
                        @php
                            $userOrdersCount = auth()->user() ? App\Models\Order::where('user_id', auth()->id())->count() : 0;
                        @endphp
                        @if($userOrdersCount > 0)
                            <span class="badge bg-success ms-auto">{{ $userOrdersCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </li>
    </ul>
    <div class="sidebar-footer border-top d-none d-lg-flex">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
</div>