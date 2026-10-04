@extends('layouts.app')

@section('content')
    @php
        date_default_timezone_set('Asia/Manila');

        use App\Models\Order;
        use App\Models\Product;
        use Illuminate\Support\Facades\DB;

        // ============================================
        // HANDLE DATE RANGE (PRESET + CUSTOM)
        // ============================================
        $range = $_GET['range'] ?? 'month';
        $from_date = trim($_GET['from_date'] ?? '');
        $to_date = trim($_GET['to_date'] ?? '');

        $allowed_ranges = ['today', 'yesterday', 'week', 'month', 'last_month', 'year', 'custom'];
        if (!in_array($range, $allowed_ranges)) {
            $range = 'month';
        }

        // APPLY PRESETS
        if ($range !== 'custom') {
            switch ($range) {
                case 'today':
                    $from_date = date('Y-m-d');
                    $to_date = date('Y-m-d');
                    break;
                case 'yesterday':
                    $from_date = date('Y-m-d', strtotime('-1 day'));
                    $to_date = $from_date;
                    break;
                case 'week':
                    $from_date = date('Y-m-d', strtotime('-6 days'));
                    $to_date = date('Y-m-d');
                    break;
                case 'last_month':
                    $from_date = date('Y-m-01', strtotime('first day of last month'));
                    $to_date = date('Y-m-t', strtotime('last day of last month'));
                    break;
                case 'year':
                    $from_date = date('Y-01-01');
                    $to_date = date('Y-m-d');
                    break;
                case 'month':
                default:
                    $from_date = date('Y-m-01');
                    $to_date = date('Y-m-d');
                    break;
            }
        } else {
            if (empty($from_date))
                $from_date = date('Y-m-01');
            if (empty($to_date))
                $to_date = date('Y-m-d');
        }

        // VALIDATE DATES
        if (!strtotime($from_date))
            $from_date = date('Y-m-01');
        if (!strtotime($to_date))
            $to_date = date('Y-m-d');
        if (strtotime($from_date) > strtotime($to_date)) {
            $temp = $from_date;
            $from_date = $to_date;
            $to_date = $temp;
        }

        // PREVIOUS PERIOD
        $from_ts = strtotime($from_date);
        $to_ts = strtotime($to_date);
        $days_diff = max(1, round(($to_ts - $from_ts) / 86400) + 1);
        $prev_to = date('Y-m-d', strtotime($from_date . ' -1 day'));
        $prev_from = date('Y-m-d', strtotime($prev_to . ' -' . ($days_diff - 1) . ' days'));

        // DAILY SALES (completed lang — para sa chart at revenue)
        $daily = Order::select(
            DB::raw('DATE(order_date) as date'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total_amount) as total_sales')
        )
            ->where('status', 'completed')
            ->whereBetween(DB::raw('DATE(order_date)'), [$from_date, $to_date])
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();

        // BEST SELLING PRODUCTS
        $best = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'), DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'))
            ->where('orders.status', 'completed')
            ->whereBetween(DB::raw('DATE(orders.order_date)'), [$from_date, $to_date])
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        // ============================================
        // SUMMARY STATS (FIXED)
        // ============================================
        // Revenue — completed lang
        $totalRevenue = array_sum(array_column($daily, 'total_sales'));

        // ✅ Completed orders count (para sa avg order value)
        $completedCount = array_sum(array_column($daily, 'total_orders'));

        // ✅ Total Orders — LAHAT NG STATUS (completed + pending + cancelled)
        $totalOrders = Order::whereBetween(DB::raw('DATE(order_date)'), [$from_date, $to_date])->count();

        // ✅ Average Daily Sales — base sa completed orders (revenue ÷ days na may sales)
        $avgDailySales = count($daily) > 0 ? $totalRevenue / count($daily) : 0;

        // ✅ Average Order Value — revenue ÷ completed orders (hindi kasama cancelled/pending)
        $avgOrderValue = $completedCount > 0 ? $totalRevenue / $completedCount : 0;

        // ============================================
        // PREVIOUS PERIOD STATS (FIXED)
        // ============================================
        // Previous revenue — completed lang
        $prevRevenue = (float) Order::where('status', 'completed')
            ->whereBetween(DB::raw('DATE(order_date)'), [$prev_from, $prev_to])
            ->sum('total_amount');

        // ✅ Previous orders — lahat ng status
        $prevOrders = (int) Order::whereBetween(DB::raw('DATE(order_date)'), [$prev_from, $prev_to])
            ->count();

        $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
        $ordersGrowth = $prevOrders > 0 ? (($totalOrders - $prevOrders) / $prevOrders) * 100 : 0;

        // UNIQUE CUSTOMERS (completed lang)
        $uniqueCustomers = Order::where('status', 'completed')
            ->whereBetween(DB::raw('DATE(order_date)'), [$from_date, $to_date])
            ->distinct('customer_name')
            ->count('customer_name');

        // ORDER STATUS BREAKDOWN (lahat ng status)
        $statusBreakdown = Order::whereBetween(DB::raw('DATE(order_date)'), [$from_date, $to_date])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pendingCount = $statusBreakdown['pending'] ?? 0;
        $cancelledCount = $statusBreakdown['cancelled'] ?? 0;
        $refundedCount = $statusBreakdown['refunded'] ?? 0;

        // TOP CUSTOMERS
        $topCustomers = Order::where('status', 'completed')
            ->whereBetween(DB::raw('DATE(order_date)'), [$from_date, $to_date])
            ->select('customer_name', DB::raw('COUNT(*) as order_count'), DB::raw('COALESCE(SUM(total_amount), 0) as total_spent'))
            ->groupBy('customer_name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        // WEEKDAY ANALYSIS
        $weekdayData = Order::where('status', 'completed')
            ->whereBetween(DB::raw('DATE(order_date)'), [$from_date, $to_date])
            ->select(DB::raw('DAYNAME(order_date) as day_name'), DB::raw('COALESCE(SUM(total_amount), 0) as total'))
            ->groupBy(DB::raw('DAYNAME(order_date)'), DB::raw('DAYOFWEEK(order_date)'))
            ->orderBy(DB::raw('DAYOFWEEK(order_date)'))
            ->get()
            ->toArray();

        // CHART DATA
        $chartLabels = [];
        $chartValues = [];
        foreach (array_reverse($daily) as $d) {
            $chartLabels[] = date('M d', strtotime($d['date']));
            $chartValues[] = (float) $d['total_sales'];
        }

        // HELPER: Format growth
        function formatGrowth($value)
        {
            $class = $value >= 0 ? 'up' : 'down';
            $icon = $value >= 0 ? 'arrow-up' : 'arrow-down';
            $sign = $value >= 0 ? '+' : '';
            return "<span class='growth-indicator $class'><i class='fas fa-$icon'></i> $sign" . number_format($value, 1) . "% vs prev</span>";
        }
    @endphp

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <div class="report-container">

        <!-- ========== PAGE HEADER ========== -->
        <div class="page-header">
            <div class="page-header-left">
                <h1>Sales Report</h1>
                <p class="page-description">Analyze your bakeshop's sales performance</p>
            </div>
        </div>

        <!-- ========== PRESET DATE RANGES ========== -->
        <div class="preset-ranges">
            <a href="?range=today" class="preset-btn {{ $range === 'today' ? 'active' : '' }}">Today</a>
            <a href="?range=yesterday" class="preset-btn {{ $range === 'yesterday' ? 'active' : '' }}">Yesterday</a>
            <a href="?range=week" class="preset-btn {{ $range === 'week' ? 'active' : '' }}">Last 7 Days</a>
            <a href="?range=month" class="preset-btn {{ $range === 'month' ? 'active' : '' }}">This Month</a>
            <a href="?range=last_month" class="preset-btn {{ $range === 'last_month' ? 'active' : '' }}">Last Month</a>
            <a href="?range=year" class="preset-btn {{ $range === 'year' ? 'active' : '' }}">This Year</a>
            <a href="?range=custom" class="preset-btn {{ $range === 'custom' ? 'active' : '' }}"><i class="fas fa-cog"></i>
                Custom</a>
        </div>

        <!-- ========== CUSTOM DATE FILTER ========== -->
        @if($range === 'custom')
            <div class="filter-card">
                <form method="GET" action="{{ route('admin.sales-report') }}" class="date-filter-form">
                    <input type="hidden" name="range" value="custom">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label><i class="fas fa-calendar-alt"></i> From Date:</label>
                            <input type="date" name="from_date" value="{{ $from_date }}" class="date-input">
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-calendar-alt"></i> To Date:</label>
                            <input type="date" name="to_date" value="{{ $to_date }}" class="date-input">
                        </div>
                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i> Generate Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif

        <!-- ========== DATE RANGE INFO ========== -->
        <div class="date-range-info">
            <i class="fas fa-clock"></i>
            Showing report from
            <strong>{{ date('F d, Y', strtotime($from_date)) }}</strong>
            to
            <strong>{{ date('F d, Y', strtotime($to_date)) }}</strong>
            <span class="days-badge">{{ $days_diff }} day{{ $days_diff != 1 ? 's' : '' }}</span>
        </div>

        <!-- ========== SUMMARY CARDS ========== -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-peso-sign"></i></div>
                <div class="summary-info">
                    <h3>Total Revenue</h3>
                    <p class="summary-value">₱{{ number_format($totalRevenue, 2) }}</p>
                    @if($prevRevenue > 0)
                        {!! formatGrowth($revenueGrowth) !!}
                    @else
                        <small class="no-prev">No previous data</small>
                    @endif
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-receipt"></i></div>
                <div class="summary-info">
                    <h3>Total Orders</h3>
                    <p class="summary-value">{{ number_format($totalOrders) }}</p>
                    @if($prevOrders > 0)
                        {!! formatGrowth($ordersGrowth) !!}
                    @else
                        <small class="no-prev">No previous data</small>
                    @endif
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-chart-line"></i></div>
                <div class="summary-info">
                    <h3>Avg Daily Sales</h3>
                    <p class="summary-value">₱{{ number_format($avgDailySales, 2) }}</p>
                    <small class="summary-sub">Average per day</small>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-calculator"></i></div>
                <div class="summary-info">
                    <h3>Avg Order Value</h3>
                    <p class="summary-value">₱{{ number_format($avgOrderValue, 2) }}</p>
                    <small class="summary-sub">Per transaction</small>
                </div>
            </div>
        </div>

        <!-- ========== SECONDARY STATS ========== -->
        <div class="secondary-stats">
            <div class="mini-stat">
                <div class="mini-stat-icon"><i class="fas fa-users"></i></div>
                <div>
                    <span class="mini-stat-value">{{ number_format($uniqueCustomers) }}</span>
                    <span class="mini-stat-label">Unique Customers</span>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon success"><i class="fas fa-check-circle"></i></div>
                <div>
                    <span class="mini-stat-value">{{ number_format($completedCount) }}</span>
                    <span class="mini-stat-label">Completed</span>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon warning"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <span class="mini-stat-value">{{ number_format($pendingCount) }}</span>
                    <span class="mini-stat-label">Pending</span>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon danger"><i class="fas fa-times-circle"></i></div>
                <div>
                    <span class="mini-stat-value">{{ number_format($cancelledCount) }}</span>
                    <span class="mini-stat-label">Cancelled</span>
                </div>
            </div>
        </div>

        <!-- ========== SALES CHART ========== -->
        @if(!empty($chartValues))
            <div class="data-card">
                <div class="card-header">
                    <div class="header-left">
                        <h3><i class="fas fa-chart-line"></i> Sales Trend</h3>
                        <span class="record-count">{{ count($chartLabels) }} days</span>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        @endif

        <!-- ========== DAILY SALES TABLE ========== -->
        <div class="data-card">
            <div class="card-header">
                <div class="header-left">
                    <h3><i class="fas fa-calendar-alt"></i> Daily Sales Report</h3>
                    <span class="record-count">{{ count($daily) }} records</span>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-date">Date</th>
                            <th class="col-orders">Orders</th>
                            <th class="col-sales">Total Sales</th>
                            <th class="col-average">Average Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($daily) > 0)
                            @foreach($daily as $row)
                                @php
                                    $avgOrder = $row['total_orders'] > 0 ? $row['total_sales'] / $row['total_orders'] : 0;
                                @endphp
                                <tr>
                                    <td class="col-date">
                                        <span class="date-value">{{ date('F j, Y', strtotime($row['date'])) }}</span>
                                        <small class="date-day">{{ date('l', strtotime($row['date'])) }}</small>
                                    </td>
                                    <td class="col-orders">
                                        <span class="orders-value">{{ $row['total_orders'] }}</span>
                                        <span class="orders-label">order{{ $row['total_orders'] != 1 ? 's' : '' }}</span>
                                    </td>
                                    <td class="col-sales">
                                        <span class="sales-value">₱{{ number_format($row['total_sales'], 2) }}</span>
                                    </td>
                                    <td class="col-average">
                                        <span class="average-value">₱{{ number_format($avgOrder, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="empty-row">
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-chart-line"></i>
                                        <p>No completed orders found</p>
                                        <small>Try changing the date range or complete some orders</small>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    @if(count($daily) > 0)
                        <tfoot>
                            <tr class="footer-row">
                                <td class="col-date">Total</td>
                                <td class="col-orders">{{ number_format($completedCount) }}</td>
                                <td class="col-sales">₱{{ number_format($totalRevenue, 2) }}</td>
                                <td class="col-average">₱{{ number_format($avgOrderValue, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- ========== ROW: BEST SELLERS + TOP CUSTOMERS ========== -->
        <div class="two-columns">

            <!-- BEST SELLING PRODUCTS -->
            <div class="data-card">
                <div class="card-header">
                    <div class="header-left">
                        <h3><i class="fas fa-trophy"></i> Best Selling Products</h3>
                        <span class="record-count">Top {{ count($best) }}</span>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="data-table products-table">
                        <thead>
                            <tr>
                                <th class="col-rank">Rank</th>
                                <th class="col-product">Product</th>
                                <th class="col-qty">Qty Sold</th>
                                <th class="col-performance">Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($best) > 0)
                                @php
                                    $maxQty = max(array_column($best, 'total_sold'));
                                    $rank = 1;
                                @endphp
                                @foreach($best as $row)
                                    @php $percentage = ($row->total_sold / $maxQty) * 100; @endphp
                                    <tr>
                                        <td class="col-rank">
                                            @if($rank == 1)
                                                <span class="medal gold"><i class="fas fa-medal"></i> 1st</span>
                                            @elseif($rank == 2)
                                                <span class="medal silver"><i class="fas fa-medal"></i> 2nd</span>
                                            @elseif($rank == 3)
                                                <span class="medal bronze"><i class="fas fa-medal"></i> 3rd</span>
                                            @else
                                                <span class="rank-number">{{ $rank }}th</span>
                                            @endif
                                        </td>
                                        <td class="col-product">
                                            <span class="product-name">{{ $row->name }}</span>
                                            <small class="revenue-small">₱{{ number_format($row->total_revenue, 2) }}
                                                revenue</small>
                                        </td>
                                        <td class="col-qty">
                                            <span class="qty-value">{{ number_format($row->total_sold) }}</span>
                                            <span class="qty-unit">units</span>
                                        </td>
                                        <td class="col-performance">
                                            <div class="performance-bar">
                                                <div class="performance-fill" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $rank++; @endphp
                                @endforeach
                            @else
                                <tr class="empty-row">
                                    <td colspan="4">
                                        <div class="empty-state small">
                                            <i class="fas fa-chart-simple"></i>
                                            <p>No sales data</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TOP CUSTOMERS -->
            <div class="data-card">
                <div class="card-header">
                    <div class="header-left">
                        <h3><i class="fas fa-crown"></i> Top Customers</h3>
                        <span class="record-count">Top {{ count($topCustomers) }}</span>
                    </div>
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
                            @if(count($topCustomers) > 0)
                                @php $rank = 1; @endphp
                                @foreach($topCustomers as $c)
                                    <tr>
                                        <td>
                                            @if($rank <= 3)
                                                <span class="medal {{ $rank == 1 ? 'gold' : ($rank == 2 ? 'silver' : 'bronze') }}">
                                                    <i class="fas fa-medal"></i>
                                                </span>
                                            @else
                                                <span class="rank-number">{{ $rank }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="customer-name">{{ ucwords($c['customer_name']) }}</span>
                                        </td>
                                        <td>
                                            <span class="orders-count">{{ $c['order_count'] }}</span>
                                        </td>
                                        <td>
                                            <span class="amount-value">₱{{ number_format($c['total_spent'], 2) }}</span>
                                        </td>
                                    </tr>
                                    @php $rank++; @endphp
                                @endforeach
                            @else
                                <tr class="empty-row">
                                    <td colspan="4">
                                        <div class="empty-state small">
                                            <i class="fas fa-users"></i>
                                            <p>No customers in period</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========== WEEKDAY PERFORMANCE ========== -->
        @if(count($weekdayData) > 0)
            @php $maxWeekday = max(array_column($weekdayData, 'total')); @endphp
            <div class="data-card">
                <div class="card-header">
                    <div class="header-left">
                        <h3><i class="fas fa-calendar-week"></i> Sales by Day of Week</h3>
                        <span class="record-count">Performance</span>
                    </div>
                </div>
                <div class="weekday-grid">
                    @foreach($weekdayData as $w)
                        @php $width = $maxWeekday > 0 ? ($w['total'] / $maxWeekday) * 100 : 0; @endphp
                        <div class="weekday-item">
                            <div class="weekday-label">{{ substr($w['day_name'], 0, 3) }}</div>
                            <div class="weekday-bar-wrapper">
                                <div class="weekday-bar-fill" style="width: {{ $width }}%"></div>
                            </div>
                            <div class="weekday-value">₱{{ number_format($w['total'], 0) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- ========== CHART SCRIPT ========== -->
    @if(!empty($chartValues))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('salesChart');
                if (!ctx) return;

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Sales (₱)',
                            data: @json($chartValues),
                            borderColor: '#576238',
                            backgroundColor: 'rgba(87, 98, 56, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#576238',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
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
                                ticks: { callback: v => '₱' + v.toLocaleString(), color: '#9E9D97' },
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
    @endif

    <style>
        .report-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* PAGE HEADER */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .page-header-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.25rem;
        }

        .page-description {
            font-size: 0.8rem;
            color: #9E9D97;
        }

        /* PRESET RANGES */
        .preset-ranges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            background: white;
            padding: 0.75rem 1rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
        }

        .preset-btn {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #576238;
            background: #F0EADC;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .preset-btn:hover {
            background: #E3DCD0;
        }

        .preset-btn.active {
            background: #576238;
            color: white;
        }

        /* FILTER CARD */
        .filter-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1.25rem;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 1.25rem;
        }

        .filter-group {
            flex: 1;
            min-width: 180px;
        }

        .filter-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #576238;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .date-input {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            font-family: 'Inter', sans-serif;
        }

        .date-input:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 2px rgba(87, 98, 56, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .btn-filter {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            background: #576238;
            color: white;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-filter:hover {
            background: #3E4A28;
        }

        /* DATE RANGE INFO */
        .date-range-info {
            background: #FDF8F0;
            padding: 0.75rem 1rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.8rem;
            color: #2C2B26;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .date-range-info i {
            color: #576238;
        }

        .date-range-info strong {
            color: #576238;
        }

        .days-badge {
            background: #E8F0E3;
            color: #576238;
            padding: 0.15rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            margin-left: auto;
        }

        /* SUMMARY CARDS */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
        }

        @media (max-width: 1024px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
        }

        .summary-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            border-color: #576238;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.08);
        }

        .summary-icon {
            width: 52px;
            height: 52px;
            background: #F0EADC;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .summary-icon i {
            color: #576238;
            font-size: 1.25rem;
        }

        .summary-info {
            flex: 1;
        }

        .summary-info h3 {
            font-size: 0.7rem;
            color: #9E9D97;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0 0 0.25rem 0;
            line-height: 1.2;
        }

        .summary-sub {
            font-size: 0.65rem;
            color: #9E9D97;
        }

        .no-prev {
            font-size: 0.65rem;
            color: #9E9D97;
            font-style: italic;
        }

        .growth-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .growth-indicator.up {
            color: #576238;
        }

        .growth-indicator.down {
            color: #C5705A;
        }

        /* SECONDARY STATS */
        .secondary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .secondary-stats {
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

        .mini-stat-icon.success {
            background: #E8F0E3;
            color: #576238;
        }

        .mini-stat-icon.warning {
            background: #FEF5E8;
            color: #D4A054;
        }

        .mini-stat-icon.danger {
            background: #FEF0ED;
            color: #C5705A;
        }

        .mini-stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2C2B26;
            display: block;
            line-height: 1.3;
        }

        .mini-stat-label {
            font-size: 0.65rem;
            color: #9E9D97;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* DATA CARD */
        .data-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .card-header {
            background: #FDF8F0;
            padding: 1rem 1.25rem;
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
            gap: 0.75rem;
        }

        .header-left h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .header-left h3 i {
            color: #576238;
            margin-right: 0.5rem;
        }

        .record-count {
            background: #F0EADC;
            color: #576238;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        /* CHART BODY */
        .chart-body {
            padding: 1.25rem;
            height: 320px;
            position: relative;
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
            padding: 0.875rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #576238;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 0.875rem 1rem;
            text-align: left;
            color: #2C2B26;
            border-bottom: 1px solid #F0EADC;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background: #FDF8F0;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .footer-row td {
            background: #FDF8F0;
            font-weight: 600;
            border-top: 1px solid #E3DCD0;
            color: #2C2B26;
        }

        /* COLUMN WIDTHS */
        .col-date {
            width: 160px;
        }

        .col-orders {
            width: 110px;
        }

        .col-sales {
            width: 140px;
        }

        .col-average {
            width: 130px;
        }

        .col-rank {
            width: 80px;
        }

        .col-qty {
            width: 110px;
        }

        .col-performance {
            width: 140px;
        }

        /* VALUES */
        .date-value {
            font-weight: 500;
            color: #2C2B26;
            display: block;
        }

        .date-day {
            font-size: 0.65rem;
            color: #9E9D97;
        }

        .orders-value {
            font-weight: 600;
            color: #576238;
        }

        .orders-label {
            font-size: 0.65rem;
            color: #9E9D97;
            margin-left: 0.25rem;
        }

        .sales-value {
            font-weight: 600;
            color: #576238;
        }

        .average-value {
            font-weight: 500;
            color: #2C2B26;
        }

        .product-name {
            font-weight: 500;
            color: #2C2B26;
            display: block;
        }

        .revenue-small {
            font-size: 0.65rem;
            color: #9E9D97;
        }

        .qty-value {
            font-weight: 600;
            color: #576238;
        }

        .qty-unit {
            font-size: 0.65rem;
            color: #9E9D97;
            margin-left: 0.25rem;
        }

        .customer-name {
            font-weight: 500;
            color: #2C2B26;
        }

        .orders-count {
            font-weight: 600;
            color: #576238;
        }

        .amount-value {
            font-weight: 600;
            color: #576238;
        }

        /* MEDALS */
        .medal {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .medal.gold {
            color: #D4A054;
        }

        .medal.silver {
            color: #9CA3AF;
        }

        .medal.bronze {
            color: #CD7F32;
        }

        .rank-number {
            font-size: 0.75rem;
            color: #9E9D97;
        }

        /* PERFORMANCE BAR */
        .performance-bar {
            position: relative;
            background: #F0EADC;
            border-radius: 1rem;
            height: 8px;
            width: 100%;
            overflow: hidden;
        }

        .performance-fill {
            background: linear-gradient(90deg, #576238, #7A8B4F);
            height: 100%;
            border-radius: 1rem;
            transition: width 0.3s ease;
        }

        /* TWO COLUMNS */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 900px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
        }

        /* WEEKDAY GRID */
        .weekday-grid {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .weekday-item {
            display: grid;
            grid-template-columns: 50px 1fr 100px;
            align-items: center;
            gap: 1rem;
        }

        .weekday-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #576238;
            text-transform: uppercase;
        }

        .weekday-bar-wrapper {
            background: #F0EADC;
            height: 24px;
            border-radius: 0.375rem;
            overflow: hidden;
        }

        .weekday-bar-fill {
            background: linear-gradient(90deg, #576238, #7A8B4F);
            height: 100%;
            border-radius: 0.375rem;
            transition: width 0.5s ease;
            min-width: 2px;
        }

        .weekday-value {
            font-size: 0.75rem;
            font-weight: 600;
            color: #576238;
            text-align: right;
        }

        /* EMPTY STATE */
        .empty-row td {
            padding: 0 !important;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
        }

        .empty-state.small {
            padding: 2rem 1rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #D4C9BD;
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state.small i {
            font-size: 2rem;
        }

        .empty-state p {
            color: #9E9D97;
            margin-bottom: 0.5rem;
        }

        .empty-state small {
            color: #C4C3BC;
            font-size: 0.75rem;
        }
    </style>
@endsection