@extends('layouts.app')

@section('content')
    @php
        function buildURL($overrides = [])
        {
            $params = array_merge($_GET, $overrides);
            $params = array_filter($params, function ($v) {
                return $v !== null && $v !== '';
            });
            return 'order-history?' . http_build_query($params);
        }
    @endphp

    <div class="order-history-container">

        <!-- ========== TODAY'S BANNER ========== -->
        <div class="today-banner">
            <div class="today-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="today-info">
                <span class="today-label">Today's Performance</span>
                <div class="today-stats">
                    <span><strong>{{ number_format($todayOrders) }}</strong> order{{ $todayOrders != 1 ? 's' : '' }}</span>
                    <span class="divider-dot">•</span>
                    <span>₱<strong>{{ number_format($todaySales, 2) }}</strong> sales</span>
                </div>
            </div>
        </div>

        <!-- ========== STATS CARDS ========== -->
        <div class="stats-row">
            <div class="stat-mini-card stat-total-sales">
                <div class="stat-mini-icon"><i class="fas fa-peso-sign"></i></div>
                <div class="stat-mini-info">
                    <span class="stat-mini-value">₱{{ number_format($totalSales, 2) }}</span>
                    <span class="stat-mini-label">Total Sales</span>
                </div>
            </div>
            <div class="stat-mini-card stat-total-orders">
                <div class="stat-mini-icon"><i class="fas fa-receipt"></i></div>
                <div class="stat-mini-info">
                    <span class="stat-mini-value">{{ number_format($totalOrders) }}</span>
                    <span class="stat-mini-label">Total Orders</span>
                </div>
            </div>
            <div class="stat-mini-card stat-completed">
                <div class="stat-mini-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-mini-info">
                    <span class="stat-mini-value">{{ number_format($completedOrders) }}</span>
                    <span class="stat-mini-label">Completed</span>
                </div>
            </div>
            <div class="stat-mini-card stat-pending">
                <div class="stat-mini-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-mini-info">
                    <span class="stat-mini-value">{{ number_format($pendingOrders) }}</span>
                    <span class="stat-mini-label">Pending</span>
                </div>
            </div>
            <div class="stat-mini-card stat-avg">
                <div class="stat-mini-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-mini-info">
                    <span class="stat-mini-value">₱{{ number_format($avgOrderValue, 2) }}</span>
                    <span class="stat-mini-label">Average Order</span>
                </div>
            </div>
        </div>

        <!-- ========== FLASH MESSAGES ========== -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
            </div>
        @endif

        <!-- ========== ORDER TABLE CARD ========== -->
        <div class="data-card">
            <div class="card-header">
                <div class="header-left">
                    <div class="header-icon">
                        <i class="fas fa-table"></i>
                    </div>
                    <div>
                        <h3>My Order Transactions</h3>
                        <p class="header-subtitle">{{ number_format($totalCount) }} result{{ $totalCount != 1 ? 's' : '' }}
                            found</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('cashier.order-history') }}" class="search-form">
                    @if($statusFilter !== 'all')<input type="hidden" name="status" value="{{ $statusFilter }}">@endif
                    @if($dateFilter !== 'all')<input type="hidden" name="date" value="{{ $dateFilter }}">@endif
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Search order # or customer..."
                            value="{{ $search }}">
                        @if($search !== '')
                            <a href="{{ buildURL(['search' => null]) }}" class="clear-search"><i class="fas fa-times"></i></a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Status Filter Tabs -->
            <div class="filter-tabs">
                <a href="{{ buildURL(['status' => 'all', 'page' => 1]) }}"
                    class="filter-tab {{ $statusFilter === 'all' ? 'active' : '' }}">
                    <i class="fas fa-list"></i> All
                    <span class="count">{{ $totalOrders }}</span>
                </a>
                <a href="{{ buildURL(['status' => 'completed', 'page' => 1]) }}"
                    class="filter-tab {{ $statusFilter === 'completed' ? 'active' : '' }}">
                    <i class="fas fa-check-circle"></i> Completed
                    <span class="count">{{ $completedOrders }}</span>
                </a>
                <a href="{{ buildURL(['status' => 'pending', 'page' => 1]) }}"
                    class="filter-tab {{ $statusFilter === 'pending' ? 'active' : '' }}">
                    <i class="fas fa-clock"></i> Pending
                    <span class="count">{{ $pendingOrders }}</span>
                </a>
                <a href="{{ buildURL(['status' => 'cancelled', 'page' => 1]) }}"
                    class="filter-tab {{ $statusFilter === 'cancelled' ? 'active' : '' }}">
                    <i class="fas fa-times-circle"></i> Cancelled
                    <span class="count">{{ $cancelledOrders }}</span>
                </a>
            </div>

            <!-- Advanced Filter Bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label><i class="fas fa-filter"></i> Filters:</label>

                    <select class="filter-select" onchange="location.href=this.value">
                        <option value="{{ buildURL(['date' => 'all', 'page' => 1]) }}" {{ $dateFilter === 'all' ? 'selected' : '' }}>All Dates</option>
                        <option value="{{ buildURL(['date' => 'today', 'page' => 1]) }}" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="{{ buildURL(['date' => 'week', 'page' => 1]) }}" {{ $dateFilter === 'week' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="{{ buildURL(['date' => 'month', 'page' => 1]) }}" {{ $dateFilter === 'month' ? 'selected' : '' }}>This Month</option>
                    </select>

                    <select class="filter-select" onchange="location.href=this.value">
                        <option value="{{ buildURL(['sort' => 'newest']) }}" {{ $sortBy === 'newest' ? 'selected' : '' }}>
                            Newest First</option>
                        <option value="{{ buildURL(['sort' => 'oldest']) }}" {{ $sortBy === 'oldest' ? 'selected' : '' }}>
                            Oldest First</option>
                        <option value="{{ buildURL(['sort' => 'amount_high']) }}" {{ $sortBy === 'amount_high' ? 'selected' : '' }}>Highest Amount</option>
                        <option value="{{ buildURL(['sort' => 'amount_low']) }}" {{ $sortBy === 'amount_low' ? 'selected' : '' }}>Lowest Amount</option>
                    </select>

                    @if($statusFilter !== 'all' || $dateFilter !== 'all' || $search !== '' || $sortBy !== 'newest')
                        <a href="{{ route('cashier.order-history') }}" class="btn-clear-filters">
                            <i class="fas fa-times"></i> Clear All
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-order-no">Order No.</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-amount">Total Amount</th>
                            <th class="col-type">Type</th>
                            <th class="col-status">Status</th>
                            <th class="col-date">Date</th>
                            <th class="col-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($orders->count() > 0)
                            @foreach($orders as $o)
                                <tr>
                                    <td class="col-order-no">
                                        <span class="order-number">#{{ str_pad($o->id, 6, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td class="col-customer">
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                {{ strtoupper(substr($o->customer_name ?? 'U', 0, 1)) }}
                                            </div>
                                            <div class="customer-text">
                                                <span class="customer-name">{{ ucwords(strtolower($o->customer_name)) }}</span>
                                                @if(!empty($o->contact_number))
                                                    <small class="customer-contact">{{ $o->contact_number }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-amount">
                                        <span class="amount-value">₱{{ number_format($o->total_amount, 2) }}</span>
                                    </td>
                                    <td class="col-type">
                                        <span class="type-badge type-{{ $o->order_type ?? 'pickup' }}">
                                            <i
                                                class="fas {{ ($o->order_type ?? 'pickup') == 'pickup' ? 'fa-store' : 'fa-truck' }}"></i>
                                            {{ ($o->order_type ?? 'pickup') == 'pickup' ? 'Pick-up' : 'Delivery' }}
                                        </span>
                                    </td>
                                    <td class="col-status">
                                        <span class="status-badge status-{{ $o->status }}">
                                            <i
                                                class="fas {{ $o->status == 'pending' ? 'fa-clock' : ($o->status == 'completed' ? 'fa-check-circle' : 'fa-times-circle') }}"></i>
                                            {{ ucfirst($o->status) }}
                                        </span>
                                    </td>
                                    <td class="col-date">
                                        <span class="date-primary">{{ $o->order_date->format('M d, Y') }}</span>
                                        <span class="date-time">{{ $o->order_date->format('h:i A') }}</span>
                                    </td>
                                    <td class="col-actions">
                                        <div class="action-group">
                                            <a href="{{ route('orders.show', $o->id) }}" class="action-btn action-view"
                                                title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if($o->status === 'pending')
                                                <form method="POST" action="{{ route('orders.complete', $o->id) }}" class="action-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="action-btn action-complete" title="Mark as Complete"
                                                        onclick="return confirm('Mark order #{{ $o->id }} as COMPLETED?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('orders.cancel', $o->id) }}" class="action-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="action-btn action-cancel" title="Cancel Order"
                                                        onclick="return confirm('Cancel order #{{ $o->id }}? Stock will be restored.')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="empty-row">
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        @if($search || $statusFilter !== 'all' || $dateFilter !== 'all')
                                            <p>No orders match your filters</p>
                                            <a href="{{ route('cashier.order-history') }}" class="link-clear">Clear filters</a>
                                        @else
                                            <p>No orders found</p>
                                            <small>Orders you process will appear here</small>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($totalPages > 1)
                <div class="pagination">
                    <div class="pagination-info">
                        Showing {{ $offset + 1 }} to {{ min($offset + $limit, $totalCount) }} of
                        {{ number_format($totalCount) }} orders
                    </div>
                    <div class="pagination-links">
                        @if($page > 1)
                            <a href="{{ buildURL(['page' => 1]) }}" class="page-link"><i class="fas fa-angle-double-left"></i></a>
                            <a href="{{ buildURL(['page' => $page - 1]) }}" class="page-link">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        @endif

                        @php
                            $start_page = max(1, $page - 2);
                            $end_page = min($totalPages, $page + 2);
                        @endphp

                        @for($i = $start_page; $i <= $end_page; $i++)
                            @if($i == $page)
                                <span class="page-current">{{ $i }}</span>
                            @else
                                <a href="{{ buildURL(['page' => $i]) }}" class="page-link">{{ $i }}</a>
                            @endif
                        @endfor

                        @if($page < $totalPages)
                            <a href="{{ buildURL(['page' => $page + 1]) }}" class="page-link">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="{{ buildURL(['page' => $totalPages]) }}" class="page-link"><i
                                    class="fas fa-angle-double-right"></i></a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function (e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
            if (e.key === 'Escape' && document.activeElement.id === 'searchInput') {
                document.activeElement.blur();
            }
        });
    </script>

    <style>
        .order-history-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* ============ ALERTS ============ */
        .alert {
            padding: 0.875rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #E8F0E3;
            color: #3E4A28;
            border: 1px solid #C5D4A8;
        }

        .alert-error {
            background: #FEF0ED;
            color: #A85444;
            border: 1px solid #E8C5BD;
        }

        .alert-info {
            background: #FEF5E8;
            color: #B8893A;
            border: 1px solid #E8D5A8;
        }

        /* TODAY BANNER */
        .today-banner {
            background: linear-gradient(135deg, #576238, #7A8B4F);
            color: white;
            padding: 1.25rem 1.5rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.2);
        }

        .today-icon {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
            backdrop-filter: blur(4px);
        }

        .today-info {
            flex: 1;
        }

        .today-label {
            font-size: 0.7rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 0.35rem;
            font-weight: 600;
        }

        .today-stats {
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .today-stats strong {
            font-size: 1.25rem;
            font-family: 'Playfair Display', serif;
        }

        .divider-dot {
            opacity: 0.5;
        }

        /* STATS CARDS */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
        }

        @media (max-width: 1100px) {
            .stats-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .stat-mini-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-mini-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #576238;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .stat-mini-card:hover {
            transform: translateY(-3px);
            border-color: #576238;
            box-shadow: 0 8px 24px rgba(87, 98, 56, 0.1);
        }

        .stat-mini-card:hover::before {
            opacity: 1;
        }

        .stat-mini-card.stat-total-sales::before {
            background: #576238;
        }

        .stat-mini-card.stat-total-orders::before {
            background: #7A8B4F;
        }

        .stat-mini-card.stat-completed::before {
            background: #576238;
        }

        .stat-mini-card.stat-pending::before {
            background: #D4A054;
        }

        .stat-mini-card.stat-avg::before {
            background: #C5705A;
        }

        .stat-mini-icon {
            width: 48px;
            height: 48px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .stat-mini-icon i {
            color: #576238;
        }

        .stat-mini-card.stat-completed .stat-mini-icon {
            background: #E8F0E3;
        }

        .stat-mini-card.stat-completed .stat-mini-icon i {
            color: #576238;
        }

        .stat-mini-card.stat-pending .stat-mini-icon {
            background: #FEF5E8;
        }

        .stat-mini-card.stat-pending .stat-mini-icon i {
            color: #D4A054;
        }

        .stat-mini-card.stat-avg .stat-mini-icon {
            background: #FEF0ED;
        }

        .stat-mini-card.stat-avg .stat-mini-icon i {
            color: #C5705A;
        }

        .stat-mini-info {
            flex: 1;
            min-width: 0;
        }

        .stat-mini-value {
            font-size: 1.15rem;
            font-weight: 700;
            color: #2C2B26;
            display: block;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-mini-label {
            font-size: 0.65rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
            margin-top: 0.1rem;
            display: block;
        }

        /* DATA CARD */
        .data-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .card-header {
            background: #FDF8F0;
            padding: 1.25rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .header-icon {
            width: 40px;
            height: 40px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #576238;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .header-left h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0 0 0.15rem 0;
        }

        .header-subtitle {
            font-size: 0.75rem;
            color: #9E9D97;
            margin: 0;
        }

        /* SEARCH */
        .search-form {
            margin: 0;
        }

        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            color: #9E9D97;
            font-size: 0.8rem;
        }

        .search-wrapper input {
            padding: 0.55rem 2rem 0.55rem 2.25rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            width: 260px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background: white;
            color: #2C2B26;
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .search-wrapper input::placeholder {
            color: #C4C3BC;
        }

        .clear-search {
            position: absolute;
            right: 0.6rem;
            color: #9E9D97;
            text-decoration: none;
            font-size: 0.75rem;
        }

        .clear-search:hover {
            color: #C5705A;
        }

        /* FILTER TABS */
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: #FDF8F0;
            border-bottom: 1px solid #E3DCD0;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: thin;
        }

        .filter-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.45rem 0.9rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
            text-decoration: none;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .filter-tab:hover {
            border-color: #576238;
            color: #576238;
            transform: translateY(-1px);
        }

        .filter-tab.active {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .filter-tab .count {
            background: #F0EADC;
            color: #576238;
            padding: 0.1rem 0.45rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 600;
            min-width: 20px;
            text-align: center;
        }

        .filter-tab.active .count {
            background: rgba(255, 255, 255, 0.25);
            color: white;
        }

        /* FILTER BAR */
        .filter-bar {
            padding: 0.65rem 1.25rem;
            background: white;
            border-bottom: 1px solid #F0EADC;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-group label {
            font-size: 0.75rem;
            color: #7A7A75;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .filter-select {
            padding: 0.4rem 0.7rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.375rem;
            font-size: 0.72rem;
            background: white;
            color: #2C2B26;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .filter-select:hover {
            border-color: #576238;
        }

        .filter-select:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .btn-clear-filters {
            background: #FEF0ED;
            color: #C5705A;
            padding: 0.4rem 0.7rem;
            border-radius: 0.375rem;
            font-size: 0.7rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .btn-clear-filters:hover {
            background: #C5705A;
            color: white;
        }

        /* TABLE */
        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Inter', sans-serif;
            font-size: 0.8125rem;
            line-height: 1.5;
        }

        .data-table thead tr {
            background: #FDF8F0;
            border-bottom: 1px solid #E3DCD0;
        }

        .data-table th {
            padding: 0.875rem 1.25rem;
            text-align: left;
            font-weight: 600;
            color: #576238;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .data-table td {
            padding: 1rem 1.25rem;
            text-align: left;
            color: #2C2B26;
            border-bottom: 1px solid #F0EADC;
            vertical-align: middle;
        }

        .data-table tbody tr {
            transition: background 0.15s ease;
        }

        .data-table tbody tr:hover {
            background: #FDF8F0;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .col-order-no {
            width: 110px;
        }

        .col-customer {
            width: auto;
            min-width: 200px;
        }

        .col-amount {
            width: 130px;
        }

        .col-type {
            width: 130px;
            white-space: nowrap;
        }

        .col-status {
            width: 120px;
            white-space: nowrap;
        }

        .col-date {
            width: 130px;
        }

        .col-actions {
            width: 130px;
            text-align: center;
        }

        .order-number {
            font-weight: 600;
            color: #576238;
            font-family: 'SF Mono', 'Monaco', monospace;
            font-size: 0.8rem;
            background: #F0EADC;
            padding: 0.3rem 0.6rem;
            border-radius: 0.375rem;
            display: inline-block;
        }

        /* Customer cell */
        .customer-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .customer-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7A8B4F, #576238);
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .customer-text {
            min-width: 0;
        }

        .customer-name {
            font-weight: 600;
            color: #2C2B26;
            display: block;
            font-size: 0.85rem;
        }

        .customer-contact {
            font-size: 0.7rem;
            color: #9E9D97;
            display: block;
            margin-top: 0.1rem;
        }

        .amount-value {
            font-weight: 700;
            color: #576238;
            font-size: 0.9rem;
        }

        /* Type & Status badges */
        .type-badge,
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.7rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .type-badge {
            background: #F0EADC;
            color: #576238;
            border: 1px solid #E3DCD0;
        }

        .status-pending {
            background: #FEF5E8;
            color: #D4A054;
            border: 1px solid #F8E5C5;
        }

        .status-completed {
            background: #E8F0E3;
            color: #576238;
            border: 1px solid #C5D4A8;
        }

        .status-cancelled {
            background: #FEF0ED;
            color: #C5705A;
            border: 1px solid #F8DCD4;
        }

        .date-primary {
            display: block;
            font-size: 0.78rem;
            color: #2C2B26;
            font-weight: 500;
        }

        .date-time {
            display: block;
            font-size: 0.68rem;
            color: #9E9D97;
            margin-top: 0.1rem;
        }

        /* Action buttons */
        .action-group {
            display: flex;
            gap: 0.375rem;
            align-items: center;
            justify-content: center;
        }

        .action-form {
            display: inline;
            margin: 0;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            cursor: pointer;
            background: transparent;
            padding: 0;
        }

        .action-view {
            background: #F0EADC;
            color: #576238;
            border-color: #E3DCD0;
        }

        .action-view:hover {
            background: #576238;
            color: white;
            border-color: #576238;
            transform: translateY(-1px);
        }

        .action-complete {
            background: #E8F0E3;
            color: #576238;
            border-color: #C5D4A8;
        }

        .action-complete:hover {
            background: #576238;
            color: white;
            border-color: #576238;
            transform: translateY(-1px);
        }

        .action-cancel {
            background: #FEF0ED;
            color: #C5705A;
            border-color: #E8C5BD;
        }

        .action-cancel:hover {
            background: #C5705A;
            color: white;
            border-color: #C5705A;
            transform: translateY(-1px);
        }

        /* Empty state */
        .empty-row td {
            padding: 0 !important;
        }

        .empty-state {
            text-align: center;
            padding: 3.5rem 2rem;
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            background: #F0EADC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .empty-icon i {
            font-size: 2rem;
            color: #C4C3BC;
        }

        .empty-state p {
            color: #6B6A65;
            margin-bottom: 0.35rem;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .empty-state small {
            color: #C4C3BC;
            font-size: 0.75rem;
        }

        .link-clear {
            color: #576238;
            text-decoration: underline;
            font-size: 0.8rem;
        }

        /* PAGINATION */
        .pagination {
            padding: 1rem 1.25rem;
            border-top: 1px solid #E3DCD0;
            background: #FDF8F0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .pagination-info {
            font-size: 0.75rem;
            color: #9E9D97;
        }

        .pagination-links {
            display: flex;
            gap: 0.4rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .page-link {
            background: white;
            color: #576238;
            padding: 0.4rem 0.7rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-size: 0.75rem;
            border: 1px solid #E3DCD0;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .page-link:hover {
            background: #576238;
            color: white;
            border-color: #576238;
        }

        .page-current {
            background: #576238;
            color: white;
            padding: 0.4rem 0.7rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .today-banner {
                flex-direction: column;
                text-align: center;
            }

            .search-wrapper input {
                width: 200px;
            }

            .data-table th,
            .data-table td {
                padding: 0.75rem 0.75rem;
                font-size: 0.75rem;
            }
        }
    </style>
@endsection