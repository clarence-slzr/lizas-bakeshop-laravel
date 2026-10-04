@extends('layouts.app')

@section('content')
    <div class="dashboard-container">

        {{-- ============================================ --}}
        {{-- HERO HEADER — Bakeshop Command Center --}}
        {{-- ============================================ --}}
        @php
            $hour = (int) now()->format('G');
            if ($hour < 12) {
                $greeting = 'Good morning';
                $greetingMessage = 'Have a great day po!';
            } elseif ($hour < 18) {
                $greeting = 'Good afternoon';
                $greetingMessage = 'Hope the day is treating you well!';
            } else {
                $greeting = 'Good evening';
                $greetingMessage = 'Time to wrap up and count the day\'s blessings.';
            }

            // Dynamic background base sa oras
            if ($hour < 12) {
                $greetingBg = 'linear-gradient(135deg, #FEF5E8, #FDF0D5)';
                $greetingColor = '#D4A054';
            } elseif ($hour < 18) {
                $greetingBg = 'linear-gradient(135deg, #E8F0E3, #D8E5CF)';
                $greetingColor = '#576238';
            } else {
                $greetingBg = 'linear-gradient(135deg, #E8E1D4, #D4C9BD)';
                $greetingColor = '#6B4F00';
            }
        @endphp

        <div class="hero-header" style="background: {{ $greetingBg }};">
            <div class="hero-pattern"></div>
            <div class="hero-content">
                <div class="hero-left">
                    <div class="greeting-icon" style="color: {{ $greetingColor }};">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="hero-text">
                        <div class="hero-greeting">{{ $greeting }},
                            {{ auth()->user()->full_name ?? auth()->user()->username }}!</div>
                        <div class="hero-subtitle">{{ $greetingMessage }}</div>
                    </div>
                </div>
                <div class="hero-right">
                    <div class="hero-date">
                        <i class="fas fa-calendar-day"></i>
                        <span>{{ date('l, F j, Y') }}</span>
                    </div>
                    <div class="hero-clock" id="liveClock">
                        <i class="fas fa-clock"></i>
                        <span>{{ date('h:i A') }}</span>
                    </div>
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.sales-report') : route('cashier.pos') }}"
                        class="hero-cta">
                        <i class="fas {{ auth()->user()->isAdmin() ? 'fa-chart-line' : 'fa-cash-register' }}"></i>
                        {{ auth()->user()->isAdmin() ? 'Reports' : 'POS' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- TODAY'S FOCUS — Actionable Insights --}}
        {{-- ============================================ --}}
        @php
            $focusItems = [];

            // 1. Pending orders
            if ($pending > 0) {
                $focusItems[] = [
                    'icon' => 'fa-hourglass-half',
                    'color' => '#D4A054',
                    'bg' => '#FEF5E8',
                    'label' => $pending . ' Pending Order' . ($pending > 1 ? 's' : ''),
                    'description' => 'Waiting for confirmation — customer is waiting',
                    'action_text' => 'Confirm Now',
                    'action_url' => route('orders.index', ['status' => 'pending']),
                    'priority' => 'high',
                ];
            }

            // 2. Low stock items
            if ($lowStock > 0) {
                $focusItems[] = [
                    'icon' => 'fa-exclamation-triangle',
                    'color' => '#C5705A',
                    'bg' => '#FEF0ED',
                    'label' => $lowStock . ' Low Stock Item' . ($lowStock > 1 ? 's' : ''),
                    'description' => 'Running low on inventory — restock soon',
                    'action_text' => 'Manage Inventory',
                    'action_url' => route('admin.products.index', ['stock' => 'low']),
                    'priority' => 'medium',
                ];
            }

            // 3. Today's sales
            if ($today > 0) {
                $focusItems[] = [
                    'icon' => 'fa-chart-line',
                    'color' => '#576238',
                    'bg' => '#E8F0E3',
                    'label' => '₱' . number_format($today, 2) . ' in sales today',
                    'description' => $growth >= 0
                        ? '▲ +' . number_format(abs($growth), 1) . '% vs yesterday — keep it up!'
                        : '▼ -' . number_format(abs($growth), 1) . '% vs yesterday',
                    'action_text' => 'View Report',
                    'action_url' => route('admin.sales-report'),
                    'priority' => 'low',
                ];
            } else {
                $focusItems[] = [
                    'icon' => 'fa-sun',
                    'color' => '#576238',
                    'bg' => '#E8F0E3',
                    'label' => 'No sales yet today',
                    'description' => 'Fresh baked goods are ready — start strong!',
                    'action_text' => 'Open POS',
                    'action_url' => route('cashier.pos'),
                    'priority' => 'low',
                ];
            }

            // 4. Best seller
            if (isset($best) && count($best) > 0) {
                $topProduct = $best[0];
                $focusItems[] = [
                    'icon' => 'fa-fire',
                    'color' => '#D4A054',
                    'bg' => '#FEF5E8',
                    'label' => 'Best Seller: ' . Str::limit($topProduct->name, 30),
                    'description' => $topProduct->sold . ' units sold • ₱' . number_format($topProduct->revenue, 2),
                    'action_text' => 'View Products',
                    'action_url' => route('admin.products.index'),
                    'priority' => 'low',
                ];
            }
        @endphp

        @if(count($focusItems) > 0)
            <div class="focus-section">
                <div class="focus-header">
                    <div class="focus-title">
                        <div class="focus-icon-wrap">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div>
                            <h2>Today's Focus</h2>
                            <p>{{ count($focusItems) }} item{{ count($focusItems) > 1 ? 's' : '' }}
                                need{{ count($focusItems) === 1 ? 's' : '' }} your attention</p>
                        </div>
                    </div>
                    <span class="focus-badge">
                        <i class="fas fa-sparkles"></i>
                        Personalized for you
                    </span>
                </div>
                <div class="focus-grid">
                    @foreach($focusItems as $item)
                        <a href="{{ $item['action_url'] }}" class="focus-card focus-priority-{{ $item['priority'] }}">
                            <div class="focus-card-icon" style="background: {{ $item['bg'] }}; color: {{ $item['color'] }};">
                                <i class="fas {{ $item['icon'] }}"></i>
                            </div>
                            <div class="focus-card-content">
                                <div class="focus-card-label">{{ $item['label'] }}</div>
                                <div class="focus-card-description">{{ $item['description'] }}</div>
                            </div>
                            <div class="focus-card-action">
                                <span>{{ $item['action_text'] }}</span>
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ============================================ --}}
        {{-- REAL-TIME STATUS --}}
        {{-- ============================================ --}}
        <div class="section-label">
            <div class="section-label-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <span>Real-Time Status</span>
            <div class="section-label-line"></div>
        </div>

        <div class="stats-grid">
            <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="stat-card stat-card-link">
                <div class="stat-icon pending-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-content">
                    <h3>Pending Orders</h3>
                    <p class="stat-number">{{ $pending }}</p>
                </div>
                <div class="stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </a>

            <a href="{{ route('admin.sales-report') }}" class="stat-card stat-card-link">
                <div class="stat-icon sales-icon"><i class="fas fa-peso-sign"></i></div>
                <div class="stat-content">
                    <h3>Today's Sales</h3>
                    <p class="stat-number">₱{{ number_format($today ?: 0, 2) }}</p>
                    @if($growth != 0)
                        <span class="growth-indicator {{ $growth >= 0 ? 'up' : 'down' }}">
                            <i class="fas fa-arrow-{{ $growth >= 0 ? 'up' : 'down' }}"></i>
                            {{ number_format(abs($growth), 1) }}% vs yesterday
                        </span>
                    @else
                        <span class="growth-neutral">No change vs yesterday</span>
                    @endif
                </div>
                <div class="stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </a>

            <a href="{{ route('orders.index') }}" class="stat-card stat-card-link">
                <div class="stat-icon orders-icon"><i class="fas fa-receipt"></i></div>
                <div class="stat-content">
                    <h3>Total Orders</h3>
                    <p class="stat-number">{{ $totalOrders }}</p>
                </div>
                <div class="stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </a>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.products.index') }}" class="stat-card stat-card-link">
                    <div class="stat-icon products-icon"><i class="fas fa-cake-candles"></i></div>
                    <div class="stat-content">
                        <h3>Active Products</h3>
                        <p class="stat-number">{{ $totalProducts }}</p>
                    </div>
                    <div class="stat-arrow"><i class="fas fa-arrow-right"></i></div>
                </a>
            @else
                <div class="stat-card">
                    <div class="stat-icon products-icon"><i class="fas fa-cake-candles"></i></div>
                    <div class="stat-content">
                        <h3>Active Products</h3>
                        <p class="stat-number">{{ $totalProducts }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ============================================ --}}
        {{-- MINI STATS --}}
        {{-- ============================================ --}}
        <div class="stats-row">
            <a href="{{ route('orders.index', ['status' => 'completed']) }}" class="mini-stat mini-stat-link">
                <div class="mini-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">{{ $completedOrders }}</span>
                    <span class="mini-stat-label">Completed Orders</span>
                </div>
            </a>

            <a href="{{ route('admin.sales-report') }}" class="mini-stat mini-stat-link">
                <div class="mini-stat-icon"><i class="fas fa-calendar-week"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">₱{{ number_format($weeklySales ?: 0, 2) }}</span>
                    <span class="mini-stat-label">This Week</span>
                </div>
            </a>

            <a href="{{ route('admin.sales-report') }}" class="mini-stat mini-stat-link">
                <div class="mini-stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">₱{{ number_format($monthlySales ?: 0, 2) }}</span>
                    <span class="mini-stat-label">This Month</span>
                </div>
            </a>

            <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="mini-stat mini-stat-link warning">
                <div class="mini-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">{{ $lowStock }}</span>
                    <span class="mini-stat-label">Low Stock Items</span>
                </div>
            </a>

            <a href="{{ route('admin.products.index', ['stock' => 'out']) }}" class="mini-stat mini-stat-link danger">
                <div class="mini-stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">{{ $outOfStock }}</span>
                    <span class="mini-stat-label">Out of Stock</span>
                </div>
            </a>
        </div>

        {{-- ============================================ --}}
        {{-- CHARTS --}}
        {{-- ============================================ --}}
        <div class="section-label">
            <div class="section-label-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <span>Performance Overview</span>
            <div class="section-label-line"></div>
        </div>

        <div class="charts-section">
            <div class="dashboard-card chart-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-area"></i> Sales Trend (Last 7 Days)</h3>
                    <span class="card-badge">Weekly</span>
                </div>
                <div class="chart-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="dashboard-card side-stats-card">
                <div class="card-header">
                    <h3><i class="fas fa-bullseye"></i> Monthly Progress</h3>
                    <span class="card-badge">{{ date('F') }}</span>
                </div>
                <div class="target-body">
                    <div class="target-info">
                        <span class="target-label">Sales Target</span>
                        <span class="target-value">₱{{ number_format($monthlyTarget, 2) }}</span>
                    </div>
                    <div class="target-progress-wrapper">
                        <div class="target-progress-bar" style="width: {{ $targetProgress }}%"></div>
                    </div>
                    <p class="target-progress-text">
                        <strong>{{ number_format($targetProgress, 1) }}%</strong> achieved —
                        ₱{{ number_format($monthlySales ?: 0, 2) }} of ₱{{ number_format($monthlyTarget, 2) }}
                    </p>

                    <div class="divider"></div>

                    <h4 class="breakdown-title">Order Status Breakdown</h4>
                    <div class="breakdown-list">
                        <a href="{{ route('orders.index', ['status' => 'pending']) }}"
                            class="breakdown-item breakdown-link">
                            <span class="dot dot-pending"></span>
                            <span class="breakdown-label">Pending</span>
                            <span class="breakdown-value">{{ $orderStats['pending'] }}</span>
                        </a>
                        <a href="{{ route('orders.index', ['status' => 'completed']) }}"
                            class="breakdown-item breakdown-link">
                            <span class="dot dot-completed"></span>
                            <span class="breakdown-label">Completed</span>
                            <span class="breakdown-value">{{ $orderStats['completed'] }}</span>
                        </a>
                        <a href="{{ route('orders.index', ['status' => 'cancelled']) }}"
                            class="breakdown-item breakdown-link">
                            <span class="dot dot-cancelled"></span>
                            <span class="breakdown-label">Cancelled</span>
                            <span class="breakdown-value">{{ $orderStats['cancelled'] }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- BUSINESS PULSE --}}
        {{-- ============================================ --}}
        <div class="section-label">
            <div class="section-label-icon">
                <i class="fas fa-heartbeat"></i>
            </div>
            <span>Business Pulse</span>
            <div class="section-label-line"></div>
        </div>

        <div class="business-pulse-grid">
            <div class="pulse-card">
                <div class="pulse-icon pulse-peak"><i class="fas fa-clock"></i></div>
                <div class="pulse-content">
                    <span class="pulse-label">Peak Hour Today</span>
                    <span class="pulse-value">{{ $peakHour }}</span>
                    <span class="pulse-meta">{{ $peakHourCount }} order{{ $peakHourCount != 1 ? 's' : '' }}</span>
                </div>
            </div>

            <div class="pulse-card">
                <div class="pulse-icon pulse-avg"><i class="fas fa-calculator"></i></div>
                <div class="pulse-content">
                    <span class="pulse-label">Avg Order Value</span>
                    <span class="pulse-value">₱{{ number_format($avgOrderValue, 2) }}</span>
                    <span class="pulse-meta">This month</span>
                </div>
            </div>

            <div class="pulse-card">
                <div class="pulse-icon pulse-refund"><i class="fas fa-rotate-left"></i></div>
                <div class="pulse-content">
                    <span class="pulse-label">Today's Refunds</span>
                    <span class="pulse-value">{{ $todayRefunds }}</span>
                    <span class="pulse-meta">₱{{ number_format($todayRefundAmount, 2) }}</span>
                </div>
            </div>

            <div class="pulse-card">
                <div class="pulse-icon pulse-refund-month"><i class="fas fa-calendar-times"></i></div>
                <div class="pulse-content">
                    <span class="pulse-label">This Month Refunds</span>
                    <span class="pulse-value">{{ $monthlyRefunds }}</span>
                    <span class="pulse-meta">₱{{ number_format($monthlyRefundAmount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- PRODUCT PERFORMANCE --}}
        {{-- ============================================ --}}
        <div class="section-label">
            <div class="section-label-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <span>Product Performance</span>
            <div class="section-label-line"></div>
        </div>

        <div class="dashboard-two-columns">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-crown"></i> Top 5 Best Sellers</h3>
                    <span class="card-badge">All Time</span>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Product Name</th>
                                <th>Qty Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($best) > 0)
                                @foreach($best as $index => $row)
                                    @php $rank = $index + 1; @endphp
                                    <tr>
                                        <td><span class="rank-badge rank-{{ $rank }}">#{{ $rank }}</span></td>
                                        <td>{{ $row->name }}</td>
                                        <td>{{ number_format($row->sold) }} units</td>
                                        <td>₱{{ number_format($row->revenue, 2) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="empty-row">No sales data yet.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-hourglass-end"></i> Slow Moving Products</h3>
                    <span class="card-badge badge-warning">No sales in 30 days</span>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Stock Left</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($slowMoving) > 0)
                                @foreach($slowMoving as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td><strong>{{ $row->stock }}</strong> pcs</td>
                                        <td><span class="status-badge status-slow">Slow Moving</span></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="empty-row">All products are moving well! 👍</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- CUSTOMER INSIGHTS --}}
        {{-- ============================================ --}}
        <div class="section-label">
            <div class="section-label-icon">
                <i class="fas fa-users"></i>
            </div>
            <span>Customer Insights</span>
            <div class="section-label-line"></div>
        </div>

        <div class="dashboard-two-columns">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-user-friends"></i> Customer Summary</h3>
                    <span class="card-badge">All Time</span>
                </div>
                <div class="insights-body">
                    <div class="insight-item">
                        <div class="insight-icon"><i class="fas fa-user-friends"></i></div>
                        <div class="insight-info">
                            <span class="insight-value">{{ number_format($totalCustomers) }}</span>
                            <span class="insight-label">Total Unique Customers</span>
                        </div>
                    </div>
                    <div class="insight-item">
                        <div class="insight-icon repeat"><i class="fas fa-redo"></i></div>
                        <div class="insight-info">
                            <span class="insight-value">{{ number_format($repeatCustomers) }}</span>
                            <span class="insight-label">Repeat Customers</span>
                        </div>
                    </div>
                    <div class="insight-item">
                        <div class="insight-icon avg"><i class="fas fa-percentage"></i></div>
                        <div class="insight-info">
                            <span class="insight-value">
                                {{ $totalCustomers > 0 ? number_format(($repeatCustomers / $totalCustomers) * 100, 1) : 0 }}%
                            </span>
                            <span class="insight-label">Retention Rate</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-star"></i> Top 5 Customers</h3>
                    <span class="card-badge">By Spending</span>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Customer</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($topCustomers->count() > 0)
                                @foreach($topCustomers as $index => $customer)
                                    @php $rank = $index + 1; @endphp
                                    <tr>
                                        <td><span class="rank-badge rank-{{ $rank }}">#{{ $rank }}</span></td>
                                        <td>{{ ucwords($customer->customer_name) }}</td>
                                        <td>{{ $customer->order_count }} orders</td>
                                        <td>₱{{ number_format($customer->total_spent, 2) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="empty-row">No customer data yet.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- OPERATIONAL ALERTS --}}
        {{-- ============================================ --}}
        <div class="section-label">
            <div class="section-label-icon">
                <i class="fas fa-bell"></i>
            </div>
            <span>Operational Alerts</span>
            <div class="section-label-line"></div>
        </div>

        <div class="dashboard-card full-width-card">
            <div class="card-header">
                <h3><i class="fas fa-clock"></i> Recent Orders</h3>
                <a href="{{ route('orders.index') }}" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th style="text-align: center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($recentOrders->count() > 0)
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('orders.show', $order->id) }}" class="order-link">
                                            #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                        </a>
                                    </td>
                                    <td>{{ ucwords($order->customer_name) }}</td>
                                    <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                    <td>{{ $order->order_date->format('M d, Y g:i A') }}</td>
                                    <td class="col-status">
                                        <span class="status-badge status-{{ $order->status }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="empty-row">No orders yet.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-card full-width-card">
            <div class="card-header">
                <h3><i class="fas fa-boxes"></i> Inventory Alerts</h3>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.products.index') }}" class="view-all-link">Manage Inventory <i
                            class="fas fa-arrow-right"></i></a>
                @endif
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($lowStockItems->count() > 0)
                            @foreach($lowStockItems as $item)
                                @php
                                    $statusClass = $item->stock == 0 ? 'status-cancelled' : 'status-pending';
                                    $statusText = $item->stock == 0 ? 'Out of Stock' : 'Low Stock';
                                @endphp
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td><strong>{{ $item->stock }}</strong> pcs</td>
                                    <td><span class="status-badge {{ $statusClass }}">{{ $statusText }}</span></td>
                                    <td>
                                        @if(auth()->user()->isAdmin())
                                            <a href="{{ route('admin.products.index') }}" class="btn-small">Restock</a>
                                        @else
                                            <span class="text-muted">Notify Admin</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="empty-row">All products are well-stocked! ✅</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ============================================ --}}
    {{-- CHART SCRIPT + LIVE CLOCK --}}
    {{-- ============================================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Sales Chart
            const ctx = document.getElementById('salesChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($labels),
                        datasets: [{
                            label: 'Sales (₱)',
                            data: @json($salesTrend),
                            borderColor: '#576238',
                            backgroundColor: 'rgba(87, 98, 56, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#576238',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#2C2B26',
                                padding: 12,
                                callbacks: {
                                    label: function (ctx) {
                                        return '₱' + parseFloat(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: function (v) { return '₱' + v.toLocaleString(); }, color: '#9E9D97' },
                                grid: { color: '#F0EADC' }
                            },
                            x: {
                                ticks: { color: '#9E9D97' },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // Live Clock
            const clockEl = document.getElementById('liveClock');
            if (clockEl) {
                const clockSpan = clockEl.querySelector('span');
                function updateClock() {
                    const now = new Date();
                    let hours = now.getHours();
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12;
                    clockSpan.textContent = String(hours).padStart(2, '0') + ':' + minutes + ' ' + ampm;
                }
                updateClock();
                setInterval(updateClock, 1000);
            }
        });
    </script>

    <style>
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            padding-bottom: 2rem;
        }

        /* ============================================ */
        /* HERO HEADER                                  */
        /* ============================================ */
        .hero-header {
            border-radius: 1rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(87, 98, 56, 0.15);
            box-shadow: 0 4px 20px rgba(87, 98, 56, 0.08);
        }

        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.5) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.3) 0%, transparent 40%);
            pointer-events: none;
        }

        .hero-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .hero-left {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            flex: 1;
            min-width: 250px;
        }

        .greeting-icon {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.75);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.25rem;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .hero-text {
            min-width: 0;
        }

        .hero-greeting {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2C2B26;
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 0.85rem;
            color: #6B6A65;
            font-style: italic;
        }

        .hero-right {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-date,
        .hero-clock {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.7);
            padding: 0.55rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #576238;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .hero-date i,
        .hero-clock i {
            color: #D4A054;
        }

        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #576238;
            color: white;
            padding: 0.6rem 1.25rem;
            border-radius: 2rem;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.2);
        }

        .hero-cta:hover {
            background: #3E4A28;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(87, 98, 56, 0.3);
        }

        @media (max-width: 768px) {
            .hero-header {
                padding: 1.5rem 1.25rem;
            }

            .hero-greeting {
                font-size: 1.15rem;
            }

            .greeting-icon {
                width: 56px;
                height: 56px;
                font-size: 1.75rem;
            }

            .hero-right {
                width: 100%;
            }

            .hero-cta {
                flex: 1;
                justify-content: center;
            }
        }

        /* ============================================ */
        /* TODAY'S FOCUS                                */
        /* ============================================ */
        .focus-section {
            background: white;
            border: 2px solid #D4A054;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(212, 160, 84, 0.1);
            position: relative;
            overflow: hidden;
        }

        .focus-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 160, 84, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .focus-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            gap: 1rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .focus-title {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .focus-icon-wrap {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #D4A054, #B8893A);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(212, 160, 84, 0.3);
        }

        .focus-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0 0 0.15rem 0;
        }

        .focus-title p {
            font-size: 0.75rem;
            color: #9E9D97;
            margin: 0;
        }

        .focus-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: linear-gradient(135deg, #FEF5E8, #FDF0D5);
            color: #B8893A;
            padding: 0.4rem 0.875rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 700;
            border: 1px solid #F8E5C5;
        }

        .focus-badge i {
            color: #D4A054;
        }

        .focus-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.875rem;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 900px) {
            .focus-grid {
                grid-template-columns: 1fr;
            }
        }

        .focus-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #FDFBF7;
            border: 1px solid #F0EADC;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            transition: all 0.25s ease;
            text-decoration: none;
            color: inherit;
            position: relative;
            overflow: hidden;
        }

        .focus-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #D4A054;
            transition: width 0.25s ease;
        }

        .focus-card.focus-priority-high::before {
            background: #C5705A;
        }

        .focus-card.focus-priority-medium::before {
            background: #D4A054;
        }

        .focus-card.focus-priority-low::before {
            background: #576238;
        }

        .focus-card:hover {
            transform: translateX(6px);
            border-color: #D4A054;
            background: white;
            box-shadow: 0 8px 20px rgba(212, 160, 84, 0.15);
        }

        .focus-card:hover::before {
            width: 6px;
        }

        .focus-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .focus-card-content {
            flex: 1;
            min-width: 0;
        }

        .focus-card-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #2C2B26;
            line-height: 1.3;
            margin-bottom: 0.2rem;
        }

        .focus-card-description {
            font-size: 0.72rem;
            color: #9E9D97;
            line-height: 1.4;
        }

        .focus-card-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            font-weight: 700;
            color: #576238;
            padding: 0.5rem 0.875rem;
            border-radius: 2rem;
            background: #F0EADC;
            transition: all 0.2s ease;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .focus-card-action:hover {
            background: #576238;
            color: white;
        }

        .focus-card-action i {
            transition: transform 0.2s ease;
        }

        .focus-card:hover .focus-card-action i {
            transform: translateX(3px);
        }

        @media (max-width: 640px) {
            .focus-card {
                flex-wrap: wrap;
            }

            .focus-card-action {
                width: 100%;
                justify-content: center;
                margin-top: 0.5rem;
            }
        }

        /* ============================================ */
        /* SECTION LABEL                                */
        /* ============================================ */
        .section-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .section-label-icon {
            width: 30px;
            height: 30px;
            background: linear-gradient(135deg, #F0EADC, #E8E1D4);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #576238;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .section-label span {
            font-size: 0.72rem;
            font-weight: 800;
            color: #6B6A65;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .section-label-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, #E3DCD0, transparent);
        }

        /* ============================================ */
        /* STATS CARDS                                  */
        /* ============================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.875rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #576238, #7A8B4F);
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: #576238;
            box-shadow: 0 12px 28px rgba(87, 98, 56, 0.12);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-arrow {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #D4C9BD;
            font-size: 0.8rem;
            opacity: 0;
            transition: all 0.2s ease;
        }

        .stat-card-link:hover .stat-arrow {
            opacity: 1;
            color: #576238;
            transform: translateY(-50%) translateX(4px);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .pending-icon {
            background: linear-gradient(135deg, #FEF5E8, #FDF0D5);
            color: #D4A054;
        }

        .sales-icon {
            background: linear-gradient(135deg, #E8F0E3, #D8E5CF);
            color: #576238;
        }

        .orders-icon {
            background: linear-gradient(135deg, #F0EADC, #E8E1D4);
            color: #576238;
        }

        .products-icon {
            background: linear-gradient(135deg, #E8E1D4, #D4C9BD);
            color: #576238;
        }

        .stat-content {
            flex: 1;
        }

        .stat-content h3 {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #9E9D97;
            margin-bottom: 0.25rem;
            font-weight: 700;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: #2C2B26;
            margin-bottom: 0.25rem;
            line-height: 1.2;
            font-family: 'Inter', sans-serif;
            transition: color 0.2s ease;
        }

        .stat-card-link:hover .stat-number {
            color: #576238;
        }

        .growth-indicator {
            font-size: 0.7rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .growth-indicator.up {
            color: #576238;
        }

        .growth-indicator.down {
            color: #C5705A;
        }

        .growth-neutral {
            font-size: 0.7rem;
            color: #9E9D97;
            font-style: italic;
        }

        /* ============================================ */
        /* MINI STATS                                   */
        /* ============================================ */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
        }

        @media (max-width: 1024px) {
            .stats-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .mini-stat {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .mini-stat:hover {
            border-color: #576238;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(87, 98, 56, 0.08);
        }

        .mini-stat-link:hover {
            background: #FDFBF7;
        }

        .mini-stat-icon {
            width: 40px;
            height: 40px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #576238;
            flex-shrink: 0;
        }

        .mini-stat.warning .mini-stat-icon {
            background: #FEF5E8;
            color: #D4A054;
        }

        .mini-stat.danger .mini-stat-icon {
            background: #FEF0ED;
            color: #C5705A;
        }

        .mini-stat-info {
            flex: 1;
            min-width: 0;
        }

        .mini-stat-value {
            font-size: 0.92rem;
            font-weight: 700;
            color: #2C2B26;
            display: block;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mini-stat-label {
            font-size: 0.62rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        /* ============================================ */
        /* CHARTS SECTION                               */
        /* ============================================ */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 900px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        .chart-card .chart-body {
            padding: 1.25rem;
            height: 300px;
        }

        .target-body {
            padding: 1.25rem;
        }

        .target-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .target-label {
            font-size: 0.75rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .target-value {
            font-size: 0.85rem;
            font-weight: 800;
            color: #2C2B26;
        }

        .target-progress-wrapper {
            width: 100%;
            height: 12px;
            background: #F0EADC;
            border-radius: 2rem;
            overflow: hidden;
            margin-bottom: 0.75rem;
        }

        .target-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #576238, #7A8B4F, #D4A054);
            border-radius: 2rem;
            transition: width 0.8s ease;
        }

        .target-progress-text {
            font-size: 0.75rem;
            color: #7A7A75;
            margin-bottom: 0.5rem;
        }

        .divider {
            height: 1px;
            background: #F0EADC;
            margin: 1rem 0;
        }

        .breakdown-title {
            font-size: 0.8rem;
            color: #2C2B26;
            margin-bottom: 0.75rem;
            font-weight: 700;
        }

        .breakdown-list {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .breakdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            text-decoration: none;
            color: inherit;
            padding: 0.4rem 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .breakdown-link:hover {
            background: #FDF8F0;
        }

        .breakdown-link:hover .breakdown-value {
            color: #576238;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-pending {
            background: #D4A054;
            box-shadow: 0 0 6px rgba(212, 160, 84, 0.5);
        }

        .dot-completed {
            background: #576238;
            box-shadow: 0 0 6px rgba(87, 98, 56, 0.5);
        }

        .dot-cancelled {
            background: #C5705A;
            box-shadow: 0 0 6px rgba(197, 112, 90, 0.5);
        }

        .breakdown-label {
            flex: 1;
            color: #7A7A75;
        }

        .breakdown-value {
            font-weight: 800;
            color: #2C2B26;
            transition: color 0.2s ease;
        }

        /* ============================================ */
        /* BUSINESS PULSE                               */
        /* ============================================ */
        .business-pulse-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 900px) {
            .business-pulse-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            .business-pulse-grid {
                grid-template-columns: 1fr;
            }
        }

        .pulse-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
        }

        .pulse-card:hover {
            transform: translateY(-2px);
            border-color: #576238;
            box-shadow: 0 6px 16px rgba(87, 98, 56, 0.08);
        }

        .pulse-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .pulse-peak {
            background: linear-gradient(135deg, #FEF5E8, #FDF0D5);
            color: #D4A054;
        }

        .pulse-avg {
            background: linear-gradient(135deg, #E8F0E3, #D8E5CF);
            color: #576238;
        }

        .pulse-refund {
            background: linear-gradient(135deg, #FEF0ED, #FCE8E6);
            color: #C5705A;
        }

        .pulse-refund-month {
            background: linear-gradient(135deg, #F0EADC, #E8E1D4);
            color: #576238;
        }

        .pulse-content {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }

        .pulse-label {
            font-size: 0.6rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 700;
        }

        .pulse-value {
            font-size: 1rem;
            font-weight: 800;
            color: #2C2B26;
            font-family: 'Inter', sans-serif;
            line-height: 1.2;
        }

        .pulse-meta {
            font-size: 0.68rem;
            color: #9E9D97;
        }

        /* ============================================ */
        /* TWO COLUMNS                                  */
        /* ============================================ */
        .dashboard-two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .dashboard-two-columns {
                grid-template-columns: 1fr;
            }
        }

        /* ============================================ */
        /* CARDS                                        */
        /* ============================================ */
        .dashboard-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.875rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .full-width-card {
            width: 100%;
        }

        .card-header {
            background: linear-gradient(135deg, #FDF8F0 0%, #F7F0E4 100%);
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .card-header h3 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0;
        }

        .card-header h3 i {
            color: #D4A054;
            margin-right: 0.5rem;
        }

        .card-badge {
            background: #576238;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.62rem;
            font-weight: 700;
        }

        .badge-warning {
            background: #D4A054;
        }

        .view-all-link {
            background: #F0EADC;
            color: #576238;
            padding: 0.35rem 0.85rem;
            border-radius: 2rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .view-all-link:hover {
            background: #576238;
            color: white;
        }

        /* ============================================ */
        /* TABLES                                       */
        /* ============================================ */
        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .data-table th,
        .data-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #F0EADC;
        }

        .data-table th {
            background: #FDF8F0;
            color: #576238;
            font-size: 0.68rem;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .data-table tbody tr {
            transition: background 0.2s ease;
        }

        .data-table tbody tr:hover {
            background: #FDF8F0;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table td.col-status {
            text-align: center;
        }

        .order-link {
            color: #576238;
            font-weight: 700;
            text-decoration: none;
            font-family: 'SF Mono', monospace;
            font-size: 0.78rem;
        }

        .order-link:hover {
            text-decoration: underline;
        }

        /* ============================================ */
        /* RANK BADGES                                  */
        /* ============================================ */
        .rank-badge {
            display: inline-block;
            width: 26px;
            height: 26px;
            line-height: 26px;
            text-align: center;
            border-radius: 50%;
            font-size: 0.68rem;
            font-weight: 800;
            background: #F0EADC;
            color: #576238;
        }

        .rank-1 {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #6B4F00;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.4);
        }

        .rank-2 {
            background: linear-gradient(135deg, #E0E0E0, #C0C0C0);
            color: #3D3D3D;
        }

        .rank-3 {
            background: linear-gradient(135deg, #E8A57A, #CD7F32);
            color: #FFF;
        }

        /* ============================================ */
        /* STATUS BADGES                                */
        /* ============================================ */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.68rem;
            font-weight: 700;
            min-width: 85px;
            text-align: center;
        }

        .status-pending {
            background: #FEF5E8;
            color: #D4A054;
        }

        .status-completed {
            background: #E8F0E3;
            color: #576238;
        }

        .status-cancelled,
        .status-refunded {
            background: #FEF0ED;
            color: #C5705A;
        }

        .status-slow {
            background: #F0EADC;
            color: #8A8A85;
        }

        /* ============================================ */
        /* BUTTONS                                      */
        /* ============================================ */
        .btn-small {
            background: #576238;
            color: white;
            padding: 0.3rem 0.7rem;
            border-radius: 0.4rem;
            font-size: 0.65rem;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .btn-small:hover {
            background: #46502d;
            transform: translateY(-1px);
        }

        .text-muted {
            color: #9E9D97;
            font-size: 0.7rem;
            font-style: italic;
        }

        /* ============================================ */
        /* CUSTOMER INSIGHTS                            */
        /* ============================================ */
        .insights-body {
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .insight-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: linear-gradient(135deg, #FDF8F0 0%, #F7F0E4 100%);
            border-radius: 0.625rem;
            border-left: 4px solid #576238;
            transition: all 0.2s ease;
        }

        .insight-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.08);
        }

        .insight-icon {
            width: 44px;
            height: 44px;
            background: white;
            color: #576238;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        .insight-icon.repeat {
            background: #FEF5E8;
            color: #D4A054;
        }

        .insight-icon.avg {
            background: #E8E1D4;
            color: #576238;
        }

        .insight-info {
            display: flex;
            flex-direction: column;
        }

        .insight-value {
            font-size: 1rem;
            font-weight: 800;
            color: #2C2B26;
            line-height: 1.2;
            font-family: 'Inter', sans-serif;
        }

        .insight-label {
            font-size: 0.62rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 700;
        }

        .empty-row {
            text-align: center;
            color: #9E9D97;
            padding: 2rem !important;
        }

        /* ============================================ */
        /* RESPONSIVE                                   */
        /* ============================================ */
        @media (max-width: 640px) {
            .stat-number {
                font-size: 1.25rem;
            }

            .pulse-value {
                font-size: 0.92rem;
            }

            .insight-value {
                font-size: 0.88rem;
            }

            .data-table {
                font-size: 0.75rem;
            }

            .data-table th,
            .data-table td {
                padding: 0.6rem 0.7rem;
            }
        }
    </style>
@endsection