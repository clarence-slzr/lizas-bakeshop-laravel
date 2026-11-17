@extends('layouts.app')

{{-- ============================================ --}}
{{-- CHART.JS LIBRARY --}}
{{-- ============================================ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@section('content')
    <div class="dashboard-container">

        {{-- ============================================ --}}
        {{-- DATA FETCHING & LOGIC --}}
        {{-- ============================================ --}}
        @php
            use App\Models\Order;
            use App\Models\Refund;
            use Illuminate\Support\Facades\DB;
            use Carbon\Carbon;

            // 1. SALES TREND
            $salesTrend = [];
            $labels = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('M d');
                $salesTrend[] = (float) Order::whereDate('order_date', $date->toDateString())
                    ->where('status', 'completed')
                    ->sum('total_amount');
            }

            // 2. REPEAT CUSTOMERS & RETENTION
            $totalCustomers = Order::where('status', 'completed')
                ->select(DB::raw('TRIM(LOWER(customer_name)) as name'))
                ->groupBy(DB::raw('TRIM(LOWER(customer_name))'))
                ->get()
                ->count();

            $repeatCustomers = Order::where('status', 'completed')
                ->select(DB::raw('TRIM(LOWER(customer_name)) as name'))
                ->groupBy(DB::raw('TRIM(LOWER(customer_name))'))
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->count();

            $retentionRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;

            // 3. REFUNDS
            $allRefunds = Refund::count();
            $allRefundsAmount = Refund::sum('refund_amount');

            $todayRefunds = Refund::whereHas('order', function ($q) {
                $q->whereDate('order_date', Carbon::today());
            })->count();

            $todayRefundAmount = Refund::whereHas('order', function ($q) {
                $q->whereDate('order_date', Carbon::today());
            })->sum('refund_amount');
        @endphp

        {{-- ============================================ --}}
        {{-- HERO HEADER --}}
        {{-- ============================================ --}}
        @php
            $hour = (int) now()->format('G');
            $name = auth()->user()->full_name ?? auth()->user()->username;
            $firstName = explode(' ', trim($name))[0];

            if ($hour < 12) {
                $greeting = 'Rise and shine, ' . $firstName . '!';
                $greetingMessage = 'Fresh bakes, fresh start. Let\'s make today\'s batch a success.';
                $greetingBg = 'linear-gradient(135deg, #FEF5E8, #FDF0D5)';
                $greetingColor = '#D4A054';
            } elseif ($hour < 18) {
                $greeting = 'Keeping the oven hot, ' . $firstName . '?';
                $greetingMessage = 'The aroma of success is in the air. Let\'s keep those orders coming!';
                $greetingBg = 'linear-gradient(135deg, #E8F0E3, #D8E5CF)';
                $greetingColor = '#576238';
            } else {
                $greeting = 'Great job today, ' . $firstName . '!';
                $greetingMessage = 'Time to wrap up. Let\'s review the day\'s blessings and prepare for tomorrow.';
                $greetingBg = 'linear-gradient(135deg, #E8E1D4, #D4C9BD)';
                $greetingColor = '#6B4F00';
            }
        @endphp

        <div class="hero-header" style="background: {{ $greetingBg }};">
            <div class="hero-content">
                <div class="hero-left">
                    <div class="greeting-icon" style="color: {{ $greetingColor }};">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="hero-text">
                        <div class="hero-greeting">{{ $greeting }}</div>
                        <div class="hero-subtitle">{{ $greetingMessage }}</div>
                    </div>
                </div>
                <div class="hero-right">
                    <div class="hero-status">
                        <span class="status-dot"></span>
                        Store is Open
                    </div>
                    {{-- Admin lang may CTA, cashier wala --}}
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.sales-report') }}" class="hero-cta">
                            <i class="fas fa-chart-line"></i> Reports
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- TODAY'S FOCUS --}}
        {{-- ============================================ --}}
        @php
            $focusItems = [];

            if ($pending > 0) {
                $focusItems[] = [
                    'icon' => 'fa-hourglass-half',
                    'color' => '#D4A054',
                    'bg' => '#FEF5E8',
                    'label' => $pending . ' Pending Order' . ($pending > 1 ? 's' : ''),
                    'description' => 'Waiting for confirmation — customer is waiting',
                    'action_text' => 'Confirm Now',
                    'action_url' => route('orders.index', ['status' => 'pending']),
                ];
            }

            if ($lowStock > 0) {
                $focusItems[] = [
                    'icon' => 'fa-exclamation-triangle',
                    'color' => '#C5705A',
                    'bg' => '#FEF0ED',
                    'label' => $lowStock . ' Low Stock Item' . ($lowStock > 1 ? 's' : ''),
                    'description' => 'Running low on inventory — restock soon',
                    'action_text' => 'Manage Inventory',
                    'action_url' => route('admin.products.index', ['stock' => 'low']),
                ];
            }

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
                ];
            }

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
                </div>
                <div class="focus-grid">
                    @foreach($focusItems as $item)
                        {{-- Kung Admin, clickable. Kung Cashier, plain div lang (for viewing) --}}
                        @if(auth()->user()->isAdmin())
                            <a href="{{ $item['action_url'] }}" class="focus-card">
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
                        @else
                            <div class="focus-card" style="cursor: default;">
                                <div class="focus-card-icon" style="background: {{ $item['bg'] }}; color: {{ $item['color'] }};">
                                    <i class="fas {{ $item['icon'] }}"></i>
                                </div>
                                <div class="focus-card-content">
                                    <div class="focus-card-label">{{ $item['label'] }}</div>
                                    <div class="focus-card-description">{{ $item['description'] }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ============================================ --}}
        {{-- QUICK STATS --}}
        {{-- ============================================ --}}
        <div class="section-label">
            <div class="section-label-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <span>Quick Stats</span>
        </div>

        <div class="stats-row">
            {{-- Completed Orders --}}
            @if(auth()->user()->isAdmin())
                <a href="{{ route('orders.index', ['status' => 'completed']) }}" class="mini-stat mini-stat-link">
            @else
                    <div class="mini-stat">
                @endif
                    <div class="mini-stat-icon" style="background: #E8F0E3; color: #576238;"><i
                            class="fas fa-check-circle"></i></div>
                    <div class="mini-stat-info">
                        <span class="mini-stat-value">{{ $completedOrders }}</span>
                        <span class="mini-stat-label">Completed Orders</span>
                    </div>
                    @if(auth()->user()->isAdmin())
                    </a> @else
            </div> @endif

        {{-- This Week --}}
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.sales-report') }}" class="mini-stat mini-stat-link">
        @else
                <div class="mini-stat">
            @endif
                <div class="mini-stat-icon" style="background: #F0EADC; color: #576238;"><i
                        class="fas fa-calendar-week"></i></div>
                <div class="mini-stat-info">
                    <span class="mini-stat-value">₱{{ number_format($weeklySales ?: 0, 2) }}</span>
                    <span class="mini-stat-label">This Week</span>
                </div>
                @if(auth()->user()->isAdmin())
                </a> @else
        </div> @endif

    {{-- This Month --}}
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.sales-report') }}" class="mini-stat mini-stat-link">
    @else
            <div class="mini-stat">
        @endif
            <div class="mini-stat-icon" style="background: #F0EADC; color: #576238;"><i class="fas fa-calendar-alt"></i>
            </div>
            <div class="mini-stat-info">
                <span class="mini-stat-value">₱{{ number_format($monthlySales ?: 0, 2) }}</span>
                <span class="mini-stat-label">This Month</span>
            </div>
            @if(auth()->user()->isAdmin())
            </a> @else </div> @endif

    {{-- Low Stock --}}
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="mini-stat mini-stat-link">
    @else
            <div class="mini-stat">
        @endif
            <div class="mini-stat-icon" style="background: #FEF5E8; color: #D4A054;"><i
                    class="fas fa-exclamation-triangle"></i></div>
            <div class="mini-stat-info">
                <span class="mini-stat-value">{{ $lowStock }}</span>
                <span class="mini-stat-label">Low Stock Items</span>
            </div>
            @if(auth()->user()->isAdmin())
            </a> @else </div> @endif

    {{-- Out of Stock --}}
    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.products.index', ['stock' => 'out']) }}" class="mini-stat mini-stat-link">
    @else
            <div class="mini-stat">
        @endif
            <div class="mini-stat-icon" style="background: #FEF0ED; color: #C5705A;"><i class="fas fa-times-circle"></i>
            </div>
            <div class="mini-stat-info">
                <span class="mini-stat-value">{{ $outOfStock }}</span>
                <span class="mini-stat-label">Out of Stock</span>
            </div>
            @if(auth()->user()->isAdmin())
            </a> @else </div> @endif
    </div>

    {{-- ============================================ --}}
    {{-- PERFORMANCE OVERVIEW (Charts) --}}
    {{-- ============================================ --}}
    <div class="section-label">
        <div class="section-label-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <span>Performance Overview</span>
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
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('orders.index', ['status' => 'pending']) }}" class="breakdown-item breakdown-link">
                    @else
                            <div class="breakdown-item">
                        @endif
                            <span class="dot dot-pending"></span>
                            <span class="breakdown-label">Pending</span>
                            <span class="breakdown-value">{{ $orderStats['pending'] }}</span>
                            @if(auth()->user()->isAdmin())
                            </a> @else
                    </div> @endif

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('orders.index', ['status' => 'completed']) }}" class="breakdown-item breakdown-link">
                @else
                        <div class="breakdown-item">
                    @endif
                        <span class="dot dot-completed"></span>
                        <span class="breakdown-label">Completed</span>
                        <span class="breakdown-value">{{ $orderStats['completed'] }}</span>
                        @if(auth()->user()->isAdmin())
                        </a> @else
                </div> @endif

            @if(auth()->user()->isAdmin())
                <a href="{{ route('orders.index', ['status' => 'cancelled']) }}" class="breakdown-item breakdown-link">
            @else
                    <div class="breakdown-item">
                @endif
                    <span class="dot dot-cancelled"></span>
                    <span class="breakdown-label">Cancelled</span>
                    <span class="breakdown-value">{{ $orderStats['cancelled'] }}</span>
                    @if(auth()->user()->isAdmin())
                    </a> @else
            </div> @endif
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
    </div>

    <div class="business-pulse-grid">
        <div class="pulse-card">
            <div class="pulse-icon" style="background: #FEF5E8; color: #D4A054;"><i class="fas fa-clock"></i></div>
            <div class="pulse-content">
                <span class="pulse-label">Peak Hour Today</span>
                <span class="pulse-value">{{ $peakHour }}</span>
                <span class="pulse-meta">{{ $peakHourCount }} order{{ $peakHourCount != 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="pulse-card">
            <div class="pulse-icon" style="background: #E8F0E3; color: #576238;"><i class="fas fa-calculator"></i></div>
            <div class="pulse-content">
                <span class="pulse-label">Avg Order Value</span>
                <span class="pulse-value">₱{{ number_format($avgOrderValue, 2) }}</span>
                <span class="pulse-meta">All time</span>
            </div>
        </div>

        <div class="pulse-card">
            <div class="pulse-icon" style="background: #FEF0ED; color: #C5705A;"><i class="fas fa-rotate-left"></i></div>
            <div class="pulse-content">
                <span class="pulse-label">Total Refunds</span>
                <span class="pulse-value">{{ $allRefunds }}</span>
                <span class="pulse-meta">₱{{ number_format($allRefundsAmount, 2) }}</span>
            </div>
        </div>

        <div class="pulse-card">
            <div class="pulse-icon" style="background: #F0EADC; color: #576238;"><i class="fas fa-calendar-times"></i></div>
            <div class="pulse-content">
                <span class="pulse-label">Today's Refunds</span>
                <span class="pulse-value">{{ $todayRefunds }}</span>
                <span class="pulse-meta">₱{{ number_format($todayRefundAmount, 2) }}</span>
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
                            <th class="col-rank">Rank</th>
                            <th class="col-product">Product Name</th>
                            <th class="col-qty text-center">Qty Sold</th>
                            <th class="col-amount text-center">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($best) > 0)
                            @foreach($best as $index => $row)
                                @php $rank = $index + 1; @endphp
                                <tr>
                                    <td class="col-rank"><span class="rank-badge rank-{{ $rank }}">#{{ $rank }}</span></td>
                                    <td class="col-product">{{ $row->name }}</td>
                                    <td class="col-qty text-center">{{ number_format($row->sold) }}</td>
                                    <td class="col-amount text-center">₱{{ number_format($row->revenue, 2) }}</td>
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
                            <th class="col-product">Product Name</th>
                            <th class="col-qty text-center">Stock Left</th>
                            <th class="col-status text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($slowMoving) > 0)
                            @foreach($slowMoving as $row)
                                <tr>
                                    <td class="col-product">{{ $row->name }}</td>
                                    <td class="col-qty text-center"><strong>{{ $row->stock }}</strong> pcs</td>
                                    <td class="col-status text-center"><span class="status-badge status-slow">Slow Moving</span>
                                    </td>
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
    </div>

    <div class="dashboard-two-columns">
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-user-friends"></i> Customer Summary</h3>
                <span class="card-badge">All Time</span>
            </div>
            <div class="insights-body">
                <div class="insight-item">
                    <div class="insight-icon" style="background: #F0EADC; color: #576238;"><i
                            class="fas fa-user-friends"></i></div>
                    <div class="insight-info">
                        <span class="insight-value">{{ number_format($totalCustomers) }}</span>
                        <span class="insight-label">Total Unique Customers</span>
                    </div>
                </div>
                <div class="insight-item">
                    <div class="insight-icon" style="background: #FEF5E8; color: #D4A054;"><i class="fas fa-redo"></i></div>
                    <div class="insight-info">
                        <span class="insight-value">{{ number_format($repeatCustomers) }}</span>
                        <span class="insight-label">Repeat Customers</span>
                    </div>
                </div>
                <div class="insight-item">
                    <div class="insight-icon" style="background: #E8E1D4; color: #576238;"><i class="fas fa-percentage"></i>
                    </div>
                    <div class="insight-info">
                        <span class="insight-value">{{ number_format($retentionRate, 1) }}%</span>
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
                            <th class="col-rank">Rank</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-orders text-center">Orders</th>
                            <th class="col-amount text-center">Total Spent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($topCustomers->count() > 0)
                            @foreach($topCustomers as $index => $customer)
                                @php $rank = $index + 1; @endphp
                                <tr>
                                    <td class="col-rank"><span class="rank-badge rank-{{ $rank }}">#{{ $rank }}</span></td>
                                    <td class="col-customer">{{ ucwords($customer->customer_name) }}</td>
                                    <td class="col-orders text-center">{{ $customer->order_count }}</td>
                                    <td class="col-amount text-center">₱{{ number_format($customer->total_spent, 2) }}</td>
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
    </div>

    {{-- RECENT ORDERS --}}
    <div class="dashboard-card full-width-card">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> Recent Orders</h3>
            {{-- View All button: Admin may makikita, Cashier wala --}}
            @if(auth()->user()->isAdmin())
                <a href="{{ route('orders.index') }}" class="view-all-link">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            @endif
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-order">Order No.</th>
                        <th class="col-customer">Customer</th>
                        <th class="col-amount text-center">Amount</th>
                        <th class="col-date text-center">Date</th>
                        <th class="col-status text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if($recentOrders->count() > 0)
                        @foreach($recentOrders as $order)
                            @if(auth()->user()->isAdmin())
                                <tr class="clickable-row" data-href="{{ route('orders.show', $order->id) }}">
                            @else
                                    <tr>
                                @endif
                                <td class="col-order">
                                    <span class="order-link">
                                        #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="col-customer" style="text-transform: capitalize;">
                                    {{ strtolower($order->customer_name) }}
                                </td>
                                <td class="col-amount text-center">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td class="col-date text-center">{{ $order->order_date->format('M d, Y g:i A') }}</td>
                                <td class="col-status text-center">
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
    </div>

    {{-- ============================================ --}}
    {{-- CHART SCRIPT & CLICKABLE ROWS --}}
    {{-- ============================================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 1. Clickable Rows Logic (Pang-Admin lang)
            document.querySelectorAll('.clickable-row').forEach(function (row) {
                row.addEventListener('click', function () {
                    window.location.href = this.dataset.href;
                });
            });

            // 2. Chart.js Logic
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
        });
    </script>

    <style>
        .dashboard-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding-bottom: 2rem;
        }

        /* HERO HEADER */
        .hero-header {
            border-radius: 1rem;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            border: 1px solid #E3DCD0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
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

        .hero-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.7);
            padding: 0.55rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #2C2B26;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #4ADE80;
            border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(74, 222, 128, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(74, 222, 128, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0);
            }
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
            box-shadow: 0 2px 8px rgba(87, 98, 56, 0.2);
        }

        .hero-cta:hover {
            background: #3E4A28;
            transform: translateY(-2px);
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

            .hero-cta,
            .hero-status {
                flex: 1;
                justify-content: center;
            }
        }

        /* TODAY'S FOCUS */
        .focus-section {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .focus-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .focus-title {
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .focus-icon-wrap {
            width: 44px;
            height: 44px;
            background: #FDF8F0;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #576238;
            font-size: 1.2rem;
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

        .focus-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.875rem;
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
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .focus-card:hover {
            border-color: #D4A054;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
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

        /* SECTION LABEL */
        .section-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .section-label-icon {
            width: 30px;
            height: 30px;
            background: #FDF8F0;
            border: 1px solid #E3DCD0;
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

        /* MINI STATS */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1rem;
        }

        @media (max-width: 1200px) {
            .stats-row {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 900px) {
            .stats-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 400px) {
            .stats-row {
                grid-template-columns: 1fr;
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
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.06);
        }

        .mini-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
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
            background: #576238;
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
            font-weight: 800;
            color: #2C2B26;
            transition: color 0.2s ease;
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
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.06);
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

        /* TWO COLUMNS & TABLES */
        .dashboard-two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 900px) {
            .dashboard-two-columns {
                grid-template-columns: 1fr;
            }
        }

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
        /* TABLE ALIGNMENTS (Dito ang inayos natin)     */
        /* ============================================ */
        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            table-layout: fixed;
        }

        .data-table th,
        .data-table td {
            padding: 0.875rem 1rem;
            text-align: left;
            border-bottom: 1px solid #F0EADC;
            vertical-align: middle;
        }

        /* ✅ Center alignment utility */
        .data-table th.text-center,
        .data-table td.text-center {
            text-align: center !important;
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

        .data-table th:nth-child(1),
        .data-table td:nth-child(1) {
            padding-left: 1.25rem;
        }

        .data-table th:last-child,
        .data-table td:last-child {
            padding-right: 1.25rem;
        }

        /* ✅ Clickable Row Styles */
        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover {
            background: #FDFBF7;
        }

        .clickable-row td {
            user-select: none;
        }

        /* ✅ Responsive Column Widths */
        .col-rank {
            width: 15%;
        }

        .col-product {
            width: 45%;
        }

        .col-customer {
            width: 35%;
        }

        .col-qty {
            width: 20%;
        }

        .col-amount {
            width: 20%;
        }

        .col-orders {
            width: 20%;
        }

        .col-order {
            width: 15%;
        }

        .col-date {
            width: 20%;
        }

        .col-status {
            width: 15%;
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
            background: #FFD700;
            color: #6B4F00;
        }

        .rank-2 {
            background: #E0E0E0;
            color: #3D3D3D;
        }

        .rank-3 {
            background: #E8A57A;
            color: #FFF;
        }

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
            border-radius: 0.625rem;
            transition: all 0.2s ease;
        }

        .insight-item:hover {
            transform: translateX(4px);
        }

        .insight-icon {
            width: 44px;
            height: 44px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
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

        /* Responsive table adj */
        @media (max-width: 640px) {
            .insight-value {
                font-size: 0.88rem;
            }

            .pulse-value {
                font-size: 0.92rem;
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