<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Lizas Bakeshop') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100" style="display: flex;">

        {{-- ============================ --}}
        {{-- SIDEBAR --}}
        {{-- ============================ --}}
        <aside class="sidebar">
            {{-- BRAND --}}
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <img src="{{ asset('images/liza-logo.png') }}" alt="Liza's Bakeshop Logo">
                </div>
                <div class="brand-info">
                    <h1>Liza's Bakeshop</h1>
                    <p>Est. 1989 | San Miguel</p>
                </div>
            </div>

            {{-- NAV --}}
            <nav class="sidebar-nav">

                <a href="{{ route('dashboard') }}"
                    class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>

                @if(auth()->user()->isAdmin())
                    <div class="nav-section">
                        <span class="nav-section-title">Management</span>

                        <a href="{{ route('admin.charts') }}"
                            class="nav-item {{ request()->routeIs('admin.charts') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie"></i>
                            <span>Analytics</span>
                        </a>

                        <a href="{{ route('admin.products.index') }}"
                            class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="fas fa-bread-slice"></i>
                            <span>Products</span>
                        </a>

                        <a href="{{ route('admin.inventory') }}"
                            class="nav-item {{ request()->routeIs('admin.inventory') ? 'active' : '' }}">
                            <i class="fas fa-boxes-stacked"></i>
                            <span>Inventory</span>
                        </a>

                        <a href="{{ route('orders.index') }}"
                            class="nav-item {{ request()->routeIs('orders.index') ? 'active' : '' }}">
                            <i class="fas fa-receipt"></i>
                            <span>Orders</span>
                        </a>

                        <a href="{{ route('admin.sales-report') }}"
                            class="nav-item {{ request()->routeIs('admin.sales-report') ? 'active' : '' }}">
                            <i class="fas fa-bullhorn"></i>
                            <span>Sales Report</span>
                        </a>

                        <a href="{{ route('admin.users.index') }}"
                            class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>Manage Users</span>
                        </a>
                    </div>
                @endif

                @if(auth()->user()->isCashier())
                    <div class="nav-section">
                        <span class="nav-section-title">Point of Sale</span>

                        <a href="{{ route('cashier.pos') }}"
                            class="nav-item {{ request()->routeIs('cashier.pos') ? 'active' : '' }}">
                            <i class="fas fa-basket-shopping"></i>
                            <span>Create Order</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <span class="nav-section-title">Shift</span>

                        <a href="{{ route('cashier.shift') }}"
                            class="nav-item {{ request()->routeIs('cashier.shift*') ? 'active' : '' }}">
                            <i class="fas fa-user-clock"></i>
                            <span>Cashier Shift</span>
                        </a>
                    </div>

                    <div class="nav-section">
                        <span class="nav-section-title">Transactions</span>

                        <a href="{{ route('cashier.order-history') }}"
                            class="nav-item {{ request()->routeIs('cashier.order-history') ? 'active' : '' }}">
                            <i class="fas fa-clock-rotate-left"></i>
                            <span>Order History</span>
                        </a>

                        <a href="{{ route('cashier.refund') }}"
                            class="nav-item {{ request()->routeIs('cashier.refund*') ? 'active' : '' }}">
                            <i class="fas fa-rotate-left"></i>
                            <span>Refund</span>
                        </a>
                    </div>
                @endif
            </nav>

            {{-- FOOTER --}}
            <div class="sidebar-footer">
                <div class="user-menu-container">
                    <button type="button" class="user-chip" id="userMenuToggle" aria-haspopup="true"
                        aria-expanded="false">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="user-details">
                            <span class="user-name">{{ auth()->user()->full_name ?? auth()->user()->username }}</span>
                            <span
                                class="user-role {{ auth()->user()->role == 'cashier' ? 'role-cashier' : 'role-admin' }}">
                                {{ auth()->user()->role == 'admin' ? 'Administrator' : 'Cashier' }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-up user-menu-caret"></i>
                    </button>

                    {{-- DROPDOWN MENU --}}
                    <div class="user-menu-dropdown" id="userMenuDropdown">
                        <a href="{{ route('profile.edit') }}" class="user-menu-item">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="{{ route('logout.confirm') }}" class="user-menu-item user-menu-item-danger">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ============================ --}}
        {{-- MAIN CONTENT --}}
        {{-- ============================ --}}
        <main style="flex: 1; margin-left: 280px; min-height: 100vh;">

            {{-- TOP HEADER --}}
            <header class="top-header">
                <div class="header-date">
                    <i class="fas fa-calendar"></i>
                    {{ date('l, F j, Y') }}
                </div>
            </header>

            {{-- PAGE CONTENT --}}
            <div style="padding: 1.5rem;">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- ============================ --}}
    {{-- STYLES --}}
    {{-- ============================ --}}
    <style>
        /* ============ SIDEBAR ============ */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #232320 0%, #2C2B26 50%, #1F1E1B 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 40;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
        }

        /* BRAND */
        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: #FFFFFF;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            padding: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .brand-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            margin: 0;
            letter-spacing: 0.3px;
        }

        .brand-info p {
            font-size: 0.6rem;
            color: #8A8A85;
            margin: 0.15rem 0 0 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* NAV */
        .sidebar-nav {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .nav-section {
            margin-bottom: 1.25rem;
        }

        .nav-section-title {
            font-size: 0.6rem;
            color: #6B6A65;
            padding: 0.5rem 0.875rem 0.4rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
            display: block;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.7rem 0.875rem;
            color: #A8A7A0;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 0.2rem;
            transition: all 0.2s ease;
            position: relative;
            font-weight: 500;
        }

        .nav-item i {
            width: 22px;
            text-align: center;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .nav-item:hover {
            background: rgba(87, 98, 56, 0.15);
            color: white;
            transform: translateX(2px);
        }

        .nav-item:hover i {
            color: #7A8B4F;
        }

        .nav-item.active {
            background: linear-gradient(90deg, #576238 0%, #3E4A28 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.3);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: #D4A054;
            border-radius: 0 4px 4px 0;
        }

        .nav-item.active i {
            color: #D4A054;
        }

        /* FOOTER */
        .sidebar-footer {
            padding: 1rem 0.875rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(0, 0, 0, 0.15);
        }

        /* ============ USER MENU DROPDOWN ============ */
        .user-menu-container {
            position: relative;
        }

        .user-chip {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            padding: 0.6rem 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            color: inherit;
            font-family: inherit;
            text-align: left;
            transition: all 0.2s ease;
        }

        .user-chip:hover {
            background: rgba(87, 98, 56, 0.15);
            border-color: rgba(87, 98, 56, 0.3);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #7A8B4F, #576238);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            font-size: 1.05rem;
        }

        .user-details {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
            flex: 1;
        }

        .user-name {
            font-size: 0.75rem;
            color: white;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.6rem;
            padding: 0.1rem 0.4rem;
            border-radius: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: fit-content;
            line-height: 1.4;
        }

        .user-role.role-admin {
            background: rgba(212, 160, 84, 0.2);
            color: #D4A054;
        }

        .user-role.role-cashier {
            background: rgba(87, 98, 56, 0.3);
            color: #A0B070;
        }

        .user-menu-caret {
            margin-left: auto;
            font-size: 0.7rem;
            color: #8A8A85;
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }

        .user-menu-container.open .user-menu-caret {
            transform: rotate(180deg);
        }

        .user-menu-dropdown {
            position: absolute;
            bottom: calc(100% + 0.5rem);
            left: 0;
            right: 0;
            background: #2C2B26;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 0.4rem;
            box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.4);
            opacity: 0;
            visibility: hidden;
            transform: translateY(8px);
            transition: all 0.2s ease;
            z-index: 50;
        }

        .user-menu-container.open .user-menu-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.65rem 0.75rem;
            border-radius: 8px;
            color: #D4D3CC;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            background: transparent;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-align: left;
            transition: all 0.15s ease;
        }

        .user-menu-item:hover {
            background: rgba(87, 98, 56, 0.2);
            color: white;
        }

        .user-menu-item i {
            width: 18px;
            text-align: center;
            font-size: 0.85rem;
        }

        .user-menu-item-danger:hover {
            background: rgba(197, 112, 90, 0.2);
            color: #E8A594;
        }

        .user-menu-item-danger:hover i {
            color: #C5705A;
        }

        /* ============ TOP HEADER ============ */
        .top-header {
            background: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 30;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .header-date {
            font-size: 0.85rem;
            color: #9E9D97;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .header-date i {
            color: #576238;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 900px) {
            .sidebar {
                width: 240px;
            }

            main {
                margin-left: 240px;
            }
        }
    </style>

    {{-- ============================ --}}
    {{-- SCRIPTS --}}
    {{-- ============================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.querySelector('.user-menu-container');
            const toggle = document.getElementById('userMenuToggle');

            if (toggle && container) {
                toggle.addEventListener('click', function (e) {
                    e.stopPropagation();
                    container.classList.toggle('open');
                    const isOpen = container.classList.contains('open');
                    toggle.setAttribute('aria-expanded', isOpen);
                });

                document.addEventListener('click', function (e) {
                    if (!container.contains(e.target)) {
                        container.classList.remove('open');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        container.classList.remove('open');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });
    </script>
</body>

</html>