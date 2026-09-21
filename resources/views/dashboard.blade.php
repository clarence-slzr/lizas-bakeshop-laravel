@extends('layouts.app')

@section('content')
    <div class="dashboard-container">

        <!-- ================= WELCOME HEADER ================= -->
        <div class="welcome-header">
            <div>
                <h1>Welcome back, {{ auth()->user()->full_name ?? auth()->user()->username }}! 👋</h1>
                <p class="welcome-subtitle">
                    {{ date('l, F j, Y') }} • Here's what's happening at Liza's Bakeshop today
                </p>
            </div>
            @if(auth()->user()->isAdmin())
                <div class="header-actions">
                    <a href="{{ route('admin.sales-report') }}" class="btn-action btn-primary">
                        <i class="fas fa-chart-line"></i> Reports
                    </a>
                </div>
            @else
                <div class="header-actions">
                    <a href="{{ route('cashier.pos') }}" class="btn-action btn-primary">
                        <i class="fas fa-cash-register"></i> POS
                    </a>
                </div>
            @endif
        </div>

        <!-- ================= SECTION 1: KPI CARDS ================= -->
        <div class="section-label">
            <i class="fas fa-bolt"></i> Real-Time Status
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon pending-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-content">
                    <h3>Pending Orders</h3>
                    <p class="stat-number">{{ $pending }}</p>
                    <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="stat-link">View Orders <i
                            class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="stat-card">
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
            </div>
            <div class="stat-card">
                <div class="stat-icon orders-icon"><i class="fas fa-receipt"></i></div>
                <div class="stat-content">
                    <h3>Total Orders</h3>
                    <p class="stat-number">{{ $totalOrders }}</p>
                    <a href="{{ route('orders.index') }}" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon products-icon"><i class="fas fa-cake-candles"></i></div>
                <div class="stat-content">
                    <h3>Active Products</h3>
                    <p class="stat-number">{{ $totalProducts }}</p>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.products.index') }}" class="stat-link">Manage <i
                                class="fas fa-arrow-right"></i></a>
                    @endif
                </div>
            </div>
        </div>

        <!-- ================= SECTION 2: MINI STATS ================= -->
        <div class="stats-row">
            <div class="mini-stat">
                <div class="mini-stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">{{ $completedOrders }}</span>
                    <span class="mini-stat-label">Completed Orders</span>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon"><i class="fas fa-calendar-week"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">₱{{ number_format($weeklySales ?: 0, 2) }}</span>
                    <span class="mini-stat-label">This Week</span>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">₱{{ number_format($monthlySales ?: 0, 2) }}</span>
                    <span class="mini-stat-label">This Month</span>
                </div>
            </div>
            <div class="mini-stat warning">
                <div class="mini-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">{{ $lowStock }}</span>
                    <span class="mini-stat-label">Low Stock Items</span>
                </div>
            </div>
            <div class="mini-stat danger">
                <div class="mini-stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">{{ $outOfStock }}</span>
                    <span class="mini-stat-label">Out of Stock</span>
                </div>
            </div>
        </div>

        <!-- ================= SECTION 3: CHARTS ================= -->
        <div class="section-label">
            <i class="fas fa-chart-line"></i> Performance Overview
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
                        <div class="breakdown-item">
                            <span class="dot dot-pending"></span>
                            <span class="breakdown-label">Pending</span>
                            <span class="breakdown-value">{{ $orderStats['pending'] }}</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="dot dot-completed"></span>
                            <span class="breakdown-label">Completed</span>
                            <span class="breakdown-value">{{ $orderStats['completed'] }}</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="dot dot-cancelled"></span>
                            <span class="breakdown-label">Cancelled</span>
                            <span class="breakdown-value">{{ $orderStats['cancelled'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= SECTION 4: BUSINESS PULSE ================= -->
        <div class="section-label">
            <i class="fas fa-heartbeat"></i> Business Pulse
        </div>
        <div class="business-pulse-grid">
            <div class="pulse-card">
                <div class="pulse-icon pulse-peak">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="pulse-content">
                    <span class="pulse-label">Peak Hour Today</span>
                    <span class="pulse-value">{{ $peakHour }}</span>
                    <span class="pulse-meta">{{ $peakHourCount }} order{{ $peakHourCount != 1 ? 's' : '' }}</span>
                </div>
            </div>

            <div class="pulse-card">
                <div class="pulse-icon pulse-avg">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="pulse-content">
                    <span class="pulse-label">Avg Order Value</span>
                    <span class="pulse-value">₱{{ number_format($avgOrderValue, 2) }}</span>
                    <span class="pulse-meta">This month</span>
                </div>
            </div>

            <div class="pulse-card">
                <div class="pulse-icon pulse-refund">
                    <i class="fas fa-rotate-left"></i>
                </div>
                <div class="pulse-content">
                    <span class="pulse-label">Today's Refunds</span>
                    <span class="pulse-value">{{ $todayRefunds }}</span>
                    <span class="pulse-meta">₱{{ number_format($todayRefundAmount, 2) }}</span>
                </div>
            </div>

            <div class="pulse-card">
                <div class="pulse-icon pulse-refund-month">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div class="pulse-content">
                    <span class="pulse-label">This Month Refunds</span>
                    <span class="pulse-value">{{ $monthlyRefunds }}</span>
                    <span class="pulse-meta">₱{{ number_format($monthlyRefundAmount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- ================= SECTION 5: PRODUCT PERFORMANCE ================= -->
        <div class="section-label">
            <i class="fas fa-trophy"></i> Product Performance
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

        <!-- ================= SECTION 6: CUSTOMER INSIGHTS ================= -->
        <div class="section-label">
            <i class="fas fa-users"></i> Customer Insights
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

        <!-- ================= SECTION 7: OPERATIONAL ALERTS ================= -->
        <div class="section-label">
            <i class="fas fa-bell"></i> Operational Alerts
        </div>

        <!-- RECENT ORDERS -->
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

        <!-- INVENTORY ALERTS -->
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

    <!-- ================= CHART SCRIPT ================= -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;

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
        });
    </script>

    <style>
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            padding-bottom: 2rem;
        }

        /* ============ WELCOME HEADER ============ */
        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .welcome-header h1 {
            font-family: 'Inter', sans-serif;
            font-size: 1.35rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.3rem;
        }

        .welcome-subtitle {
            font-size: 0.78rem;
            color: #9E9D97;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* ============ SECTION LABEL ============ */
        .section-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.68rem;
            font-weight: 700;
            color: #6B6A65;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #E3DCD0;
            margin-top: 0.25rem;
        }

        .section-label i {
            color: #576238;
            font-size: 0.8rem;
        }

        /* QUICK ACTIONS */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.1rem;
            border-radius: 0.5rem;
            font-size: 0.78rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            font-family: inherit;
        }

        .btn-primary {
            background: #576238;
            color: white;
        }

        .btn-primary:hover {
            background: #46502d;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(87, 98, 56, 0.2);
        }

        /* STATS CARDS */
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
            border-radius: 0.75rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
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

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: #576238;
            box-shadow: 0 8px 24px rgba(87, 98, 56, 0.1);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .pending-icon {
            background: #FEF5E8;
            color: #D4A054;
        }

        .sales-icon {
            background: #E8F0E3;
            color: #576238;
        }

        .orders-icon {
            background: #F0EADC;
            color: #576238;
        }

        .products-icon {
            background: #E8E1D4;
            color: #576238;
        }

        .stat-content {
            flex: 1;
        }

        .stat-content h3 {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9E9D97;
            margin-bottom: 0.2rem;
            font-weight: 600;
        }

        .stat-number {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2C2B26;
            margin-bottom: 0.25rem;
            line-height: 1.2;
            font-family: 'Inter', sans-serif;
        }

        .stat-link {
            font-size: 0.7rem;
            color: #576238;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-link:hover {
            text-decoration: underline;
        }

        .growth-indicator {
            font-size: 0.7rem;
            font-weight: 600;
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

        /* MINI STATS */
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
            border-radius: 0.5rem;
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }

        .mini-stat:hover {
            border-color: #576238;
            transform: translateY(-2px);
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
            font-weight: 600;
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
            font-weight: 600;
        }

        /* CHARTS SECTION */
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
            font-weight: 600;
        }

        .target-value {
            font-size: 0.85rem;
            font-weight: 700;
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
            background: linear-gradient(90deg, #576238, #7A8B4F);
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
            font-weight: 600;
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
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-pending {
            background: #D4A054;
        }

        .dot-completed {
            background: #576238;
        }

        .dot-cancelled {
            background: #C5705A;
        }

        .breakdown-label {
            flex: 1;
            color: #7A7A75;
        }

        .breakdown-value {
            font-weight: 700;
            color: #2C2B26;
        }

        /* BUSINESS PULSE */
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
            background: #FEF5E8;
            color: #D4A054;
        }

        .pulse-avg {
            background: #E8F0E3;
            color: #576238;
        }

        .pulse-refund {
            background: #FEF0ED;
            color: #C5705A;
        }

        .pulse-refund-month {
            background: #F0EADC;
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
            font-weight: 600;
        }

        .pulse-value {
            font-size: 1rem;
            font-weight: 700;
            color: #2C2B26;
            font-family: 'Inter', sans-serif;
            line-height: 1.2;
        }

        .pulse-meta {
            font-size: 0.68rem;
            color: #9E9D97;
        }

        /* TWO COLUMNS */
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

        /* CARDS */
        .dashboard-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .full-width-card {
            width: 100%;
        }

        .card-header {
            background: #FDF8F0;
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
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .card-header h3 i {
            color: #576238;
            margin-right: 0.5rem;
        }

        .card-badge {
            background: #576238;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.62rem;
            font-weight: 600;
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
            font-weight: 600;
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

        /* TABLES */
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
            font-weight: 600;
            letter-spacing: 0.5px;
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
            font-weight: 600;
            text-decoration: none;
            font-family: 'SF Mono', monospace;
            font-size: 0.78rem;
        }

        .order-link:hover {
            text-decoration: underline;
        }

        /* RANK BADGES */
        .rank-badge {
            display: inline-block;
            width: 26px;
            height: 26px;
            line-height: 26px;
            text-align: center;
            border-radius: 50%;
            font-size: 0.68rem;
            font-weight: 700;
            background: #F0EADC;
            color: #576238;
        }

        .rank-1 {
            background: #FFD700;
            color: #6B4F00;
        }

        .rank-2 {
            background: #C0C0C0;
            color: #3D3D3D;
        }

        .rank-3 {
            background: #CD7F32;
            color: #FFF;
        }

        /* STATUS BADGES */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.68rem;
            font-weight: 600;
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

        /* BUTTONS */
        .btn-small {
            background: #576238;
            color: white;
            padding: 0.3rem 0.7rem;
            border-radius: 0.25rem;
            font-size: 0.65rem;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-small:hover {
            background: #46502d;
        }

        .text-muted {
            color: #9E9D97;
            font-size: 0.7rem;
            font-style: italic;
        }

        /* CUSTOMER INSIGHTS */
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
            background: #FDF8F0;
            border-radius: 0.5rem;
            border-left: 3px solid #576238;
        }

        .insight-icon {
            width: 40px;
            height: 40px;
            background: #E8F0E3;
            color: #576238;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
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
            font-size: 0.95rem;
            font-weight: 700;
            color: #2C2B26;
            line-height: 1.2;
            font-family: 'Inter', sans-serif;
        }

        .insight-label {
            font-size: 0.62rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
        }

        .empty-row {
            text-align: center;
            color: #9E9D97;
            padding: 2rem !important;
        }

        /* RESPONSIVE */
        @media (max-width: 640px) {
            .welcome-header h1 {
                font-size: 1.15rem;
            }

            .section-label {
                font-size: 0.62rem;
            }

            .stat-number {
                font-size: 1.2rem;
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