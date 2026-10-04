@extends('layouts.app')

@section('content')
    @php
        use App\Models\Order;
        use App\Models\Product;
        use App\Models\OrderItem;
        use Illuminate\Support\Facades\DB;

        // ============================================
        // MONTHLY SALES DATA
        // ============================================
        $monthly = Order::select(
            DB::raw("DATE_FORMAT(order_date, '%Y-%m') as month"),
            DB::raw('COALESCE(SUM(total_amount), 0) as total'),
            DB::raw('COUNT(*) as order_count')
        )
            ->where('status', 'completed')
            ->groupBy(DB::raw("DATE_FORMAT(order_date, '%Y-%m')"))
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get()
            ->toArray();

        $months = [];
        $sales = [];
        $orderCounts = [];
        foreach (array_reverse($monthly) as $row) {
            $months[] = date('M Y', strtotime($row['month'] . '-01'));
            $sales[] = (float) $row['total'];
            $orderCounts[] = (int) $row['order_count'];
        }

        // ============================================
        // TOP PRODUCTS (DB::table → stdClass object)
        // ============================================
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as sold'))
            ->where('orders.status', 'completed')
            ->groupBy('products.id', 'products.name')
            ->orderBy('sold', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        $productNames = [];
        $productSales = [];
        foreach ($topProducts as $p) {
            $productNames[] = $p->name;
            $productSales[] = (int) $p->sold;
        }

        // ============================================
        // SUMMARY STATS
        // ============================================
        $totalSales = Order::where('status', 'completed')->sum('total_amount');
        $totalOrders = Order::where('status', 'completed')->count();
        $avgOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        $pendingOrders = Order::where('status', 'pending')->count();

        // ============================================
        // SALES BY CATEGORY (DB::table → stdClass object)
        // ============================================
        $categoryData = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('products.category as category', DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as revenue'))
            ->where('orders.status', 'completed')
            ->whereNotNull('products.category')
            ->groupBy('products.category')
            ->orderBy('revenue', 'desc')
            ->get()
            ->toArray();

        $catLabels = [];
        $catValues = [];
        foreach ($categoryData as $c) {
            $catLabels[] = $c->category;
            $catValues[] = (float) $c->revenue;
        }

        // ============================================
        // PAYMENT METHOD BREAKDOWN
        // ============================================
        $paymentLabels = ['Cash'];
        $paymentValues = [Order::where('status', 'completed')->count()];

        // ============================================
        // HOURLY SALES PATTERN
        // ============================================
        $hourlyData = Order::select(
            DB::raw('HOUR(order_date) as hour'),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total')
        )
            ->where('status', 'completed')
            ->where('order_date', '>=', now()->subDays(30))
            ->groupBy(DB::raw('HOUR(order_date)'))
            ->orderBy('hour', 'asc')
            ->get()
            ->toArray();

        $hourLabels = [];
        $hourValues = [];
        $hourCounts = [];
        foreach ($hourlyData as $h) {
            $hourLabels[] = date('g A', strtotime($h['hour'] . ':00'));
            $hourValues[] = (float) $h['total'];
            $hourCounts[] = (int) $h['order_count'];
        }

        // ============================================
        // DAILY SALES - THIS WEEK
        // ============================================
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyData[] = [
                'label' => $date->format('D'),
                'date' => $date->format('M d'),
                'value' => (float) Order::whereDate('order_date', $date->toDateString())
                    ->where('status', 'completed')
                    ->sum('total_amount')
            ];
        }

        // ============================================
        // ORDER STATUS DISTRIBUTION
        // ============================================
        $statusData = Order::select('status', DB::raw('COUNT(*) as count'))
            ->where('status', '!=', 'refunded')
            ->groupBy('status')
            ->get()
            ->toArray();

        $statusLabels = [];
        $statusValues = [];
        $statusColors = [
            'pending' => '#D4A054',
            'completed' => '#576238',
            'cancelled' => '#C5705A',
            'refunded' => '#9E9D97'
        ];
        foreach ($statusData as $s) {
            $statusLabels[] = ucfirst($s['status']);
            $statusValues[] = (int) $s['count'];
        }

        // ============================================
        // TOP CUSTOMERS
        // ============================================
        $topCustomers = Order::select(
            'customer_name',
            DB::raw('COUNT(*) as order_count'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total_spent')
        )
            ->where('status', 'completed')
            ->groupBy('customer_name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        $customerNames = [];
        $customerSpent = [];
        foreach ($topCustomers as $c) {
            $customerNames[] = $c['customer_name'];
            $customerSpent[] = (float) $c['total_spent'];
        }

        // ============================================
        // SAFE MAX VALUES
        // ============================================
        $max_sales = !empty($sales) ? max($sales) : 1;
        $max_sold = !empty($productSales) ? max($productSales) : 1;
        $grandTotal = !empty($sales) ? array_sum($sales) : 0;
    @endphp

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <div class="analytics-container">

        <!-- ============ SUMMARY CARDS ============ -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-peso-sign"></i></div>
                <div>
                    <h3>Total Sales</h3>
                    <p>₱{{ number_format($totalSales, 2) }}</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <h3>Completed Orders</h3>
                    <p>{{ number_format($totalOrders) }}</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-hourglass-half"></i></div>
                <div>
                    <h3>Pending Orders</h3>
                    <p>{{ number_format($pendingOrders) }}</p>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon"><i class="fas fa-chart-line"></i></div>
                <div>
                    <h3>Average Order Value</h3>
                    <p>₱{{ number_format($avgOrder, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- ============ ROW 1: MONTHLY SALES + ORDER STATUS ============ -->
        <div class="analytics-row-2col">
            <!-- MONTHLY SALES -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-line"></i> Monthly Sales Trend</h3>
                    <span class="chart-badge">Last 6 Months</span>
                </div>
                <div class="chart-body">
                    @if(!empty($months))
                        <div class="chart-canvas-wrapper">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <p>No sales data yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- ORDER STATUS DONUT -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-chart-pie"></i> Order Status Distribution</h3>
                    <span class="chart-badge">All Time</span>
                </div>
                <div class="chart-body">
                    @if(!empty($statusValues) && array_sum($statusValues) > 0)
                        <div class="chart-canvas-wrapper donut-wrapper">
                            <canvas id="statusChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-chart-pie"></i>
                            <p>No orders yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ============ ROW 2: SALES BY CATEGORY + PAYMENT METHOD ============ -->
        <div class="analytics-row-2col">
            <!-- SALES BY CATEGORY -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-tags"></i> Sales by Category</h3>
                    <span class="chart-badge">By Revenue</span>
                </div>
                <div class="chart-body">
                    @if(!empty($catValues) && array_sum($catValues) > 0)
                        <div class="chart-canvas-wrapper donut-wrapper">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-tags"></i>
                            <p>No category data</p>
                            <small>Requires categories table with products</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PAYMENT METHOD -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-credit-card"></i> Payment Methods</h3>
                    <span class="chart-badge">By Order Count</span>
                </div>
                <div class="chart-body">
                    @if(!empty($paymentValues))
                        <div class="chart-canvas-wrapper donut-wrapper">
                            <canvas id="paymentChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-credit-card"></i>
                            <p>No payment data</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ============ ROW 3: HOURLY SALES PATTERN (Full Width) ============ -->
        <div class="chart-card full-width">
            <div class="chart-header">
                <h3><i class="fas fa-clock"></i> Hourly Sales Pattern</h3>
                <span class="chart-badge">Last 30 Days</span>
            </div>
            <div class="chart-body">
                @if(!empty($hourValues))
                    <div class="chart-canvas-wrapper tall-wrapper">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <p>No hourly data yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- ============ ROW 4: DAILY SALES + TOP CUSTOMERS ============ -->
        <div class="analytics-row-2col">
            <!-- DAILY SALES THIS WEEK -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-calendar-week"></i> This Week's Sales</h3>
                    <span class="chart-badge">Daily</span>
                </div>
                <div class="chart-body">
                    <div class="chart-canvas-wrapper">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- TOP CUSTOMERS -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3><i class="fas fa-crown"></i> Top Customers</h3>
                    <span class="chart-badge">By Spending</span>
                </div>
                <div class="chart-body">
                    @if(!empty($customerNames))
                        <div class="chart-canvas-wrapper">
                            <canvas id="customerChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-crown"></i>
                            <p>No customer data yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ============ TOP SELLING PRODUCTS ============ -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-trophy"></i> Top Selling Products</h3>
                <span class="chart-badge">Best Sellers</span>
            </div>
            <div class="chart-body">
                @if(!empty($productNames))
                    <div class="products-list">
                        @foreach($productNames as $index => $name)
                            @php $width = $max_sold > 0 ? ($productSales[$index] / $max_sold) * 100 : 0; @endphp
                            <div class="product-list-item">
                                <div class="product-rank">
                                    @if($index == 0) <i class="fa-solid fa-medal"></i>
                                    @elseif($index == 1) <i class="fa-solid fa-medal"></i>
                                    @elseif($index == 2) <i class="fa-solid fa-medal"></i>
                                    @else {{ $index + 1 }}th
                                    @endif
                                </div>
                                <div class="product-name">{{ $name }}</div>
                                <div class="product-sales">{{ $productSales[$index] }} units</div>
                                <div class="product-bar">
                                    <div class="product-bar-fill" style="width: {{ $width }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-trophy"></i>
                        <p>No product sales data yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- ============ MONTHLY SALES TABLE ============ -->
        <div class="data-card">
            <div class="card-header">
                <h3><i class="fas fa-table"></i> Monthly Sales Details</h3>
                <span class="record-count">{{ count($monthly) }} months</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Orders</th>
                        <th>Total Sales</th>
                        <th>% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($monthly))
                        @foreach($monthly as $row)
                            @php
                                $monthName = date('F Y', strtotime($row['month'] . '-01'));
                                $percentage = $grandTotal > 0 ? ($row['total'] / $grandTotal) * 100 : 0;
                            @endphp
                            <tr>
                                <td>{{ $monthName }}</td>
                                <td>{{ number_format($row['order_count']) }} order{{ $row['order_count'] != 1 ? 's' : '' }}</td>
                                <td>₱{{ number_format($row['total'], 2) }}</td>
                                <td>
                                    <div class="percentage-bar">
                                        <div class="percentage-fill" style="width: {{ $percentage }}%"></div>
                                        <span>{{ number_format($percentage, 1) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="empty-row">
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="fas fa-table"></i>
                                    <p>No monthly sales data yet</p>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
                @if(!empty($monthly))
                    <tfoot>
                        <tr class="footer-row">
                            <td>Total</td>
                            <td>{{ number_format($totalOrders) }}</td>
                            <td>₱{{ number_format($grandTotal, 2) }}</td>
                            <td>100%</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- ============ CHART.JS SCRIPTS ============ -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const greenColor = '#576238';
            const accentColor = '#D4A054';
            const chartFont = { family: 'inherit', size: 11 };
            const tooltipStyle = {
                backgroundColor: '#2C2B26',
                padding: 12,
                titleFont: { size: 12, weight: 'bold' },
                bodyFont: { size: 12 },
                cornerRadius: 6
            };

            // ========== 1. MONTHLY SALES (Bar) ==========
            const monthlyCtx = document.getElementById('monthlyChart');
            if (monthlyCtx) {
                new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($months),
                        datasets: [{
                            label: 'Sales (₱)',
                            data: @json($sales),
                            backgroundColor: greenColor,
                            borderRadius: 6,
                            maxBarThickness: 60
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: {
                                    label: ctx => '₱' + parseFloat(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: v => '₱' + v.toLocaleString(), color: '#9E9D97', font: chartFont },
                                grid: { color: '#F0EADC' }
                            },
                            x: { ticks: { color: '#9E9D97', font: chartFont }, grid: { display: false } }
                        }
                    }
                });
            }

            // ========== 2. ORDER STATUS (Donut) ==========
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($statusLabels),
                        datasets: [{
                            data: @json($statusValues),
                            backgroundColor: @json(array_values($statusColors)),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 15, font: chartFont, boxWidth: 12 }
                            },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: { label: ctx => ctx.label + ': ' + ctx.raw + ' orders' }
                            }
                        }
                    }
                });
            }

            // ========== 3. SALES BY CATEGORY (Donut) ==========
            const categoryCtx = document.getElementById('categoryChart');
            if (categoryCtx) {
                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($catLabels),
                        datasets: [{
                            data: @json($catValues),
                            backgroundColor: ['#576238', '#D4A054', '#C5705A', '#7A8B4F', '#B8AE97', '#9E9D97'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 15, font: chartFont, boxWidth: 12 }
                            },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: {
                                    label: ctx => ctx.label + ': ₱' + parseFloat(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                                }
                            }
                        }
                    }
                });
            }

            // ========== 4. PAYMENT METHODS (Donut) ==========
            const paymentCtx = document.getElementById('paymentChart');
            if (paymentCtx) {
                new Chart(paymentCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($paymentLabels),
                        datasets: [{
                            data: @json($paymentValues),
                            backgroundColor: ['#576238', '#D4A054', '#7A8B4F', '#C5705A'],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { padding: 15, font: chartFont, boxWidth: 12 }
                            },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: { label: ctx => ctx.label + ': ' + ctx.raw + ' orders' }
                            }
                        }
                    }
                });
            }

            // ========== 5. HOURLY SALES (Line) ==========
            const hourlyCtx = document.getElementById('hourlyChart');
            if (hourlyCtx) {
                new Chart(hourlyCtx, {
                    type: 'line',
                    data: {
                        labels: @json($hourLabels),
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: @json($hourValues),
                            borderColor: greenColor,
                            backgroundColor: 'rgba(87, 98, 56, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: greenColor,
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
                                ...tooltipStyle,
                                callbacks: {
                                    label: ctx => '₱' + parseFloat(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: v => '₱' + v.toLocaleString(), color: '#9E9D97', font: chartFont },
                                grid: { color: '#F0EADC' }
                            },
                            x: { ticks: { color: '#9E9D97', font: chartFont }, grid: { display: false } }
                        }
                    }
                });
            }

            // ========== 6. WEEKLY SALES (Bar) ==========
            const weeklyCtx = document.getElementById('weeklyChart');
            if (weeklyCtx) {
                new Chart(weeklyCtx, {
                    type: 'bar',
                    data: {
                        labels: @json(array_column($weeklyData, 'label')),
                        datasets: [{
                            label: 'Sales (₱)',
                            data: @json(array_column($weeklyData, 'value')),
                            backgroundColor: accentColor,
                            borderRadius: 6,
                            maxBarThickness: 50
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: {
                                    label: ctx => '₱' + parseFloat(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: v => '₱' + v.toLocaleString(), color: '#9E9D97', font: chartFont },
                                grid: { color: '#F0EADC' }
                            },
                            x: { ticks: { color: '#9E9D97', font: chartFont }, grid: { display: false } }
                        }
                    }
                });
            }

            // ========== 7. TOP CUSTOMERS (Horizontal Bar) ==========
            const customerCtx = document.getElementById('customerChart');
            if (customerCtx) {
                new Chart(customerCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($customerNames),
                        datasets: [{
                            label: 'Total Spent (₱)',
                            data: @json($customerSpent),
                            backgroundColor: greenColor,
                            borderRadius: 6,
                            maxBarThickness: 28
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                ...tooltipStyle,
                                callbacks: {
                                    label: ctx => '₱' + parseFloat(ctx.raw).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { callback: v => '₱' + v.toLocaleString(), color: '#9E9D97', font: chartFont },
                                grid: { color: '#F0EADC' }
                            },
                            y: { ticks: { color: '#9E9D97', font: chartFont }, grid: { display: false } }
                        }
                    }
                });
            }
        });
    </script>

    <style>
        .analytics-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* ============ SUMMARY CARDS ============ */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
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
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            border-color: #576238;
            box-shadow: 0 4px 12px rgba(87, 98, 56, 0.08);
        }

        .summary-icon {
            width: 48px;
            height: 48px;
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

        .summary-card h3 {
            font-size: 0.65rem;
            color: #9E9D97;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card p {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2C2B26;
            margin: 0;
        }

        /* ============ CHART CARDS ============ */
        .chart-card {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .chart-card.full-width {
            width: 100%;
        }

        .chart-header {
            background: #FDF8F0;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #E3DCD0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .chart-header h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
            margin: 0;
        }

        .chart-header h3 i {
            color: #576238;
            margin-right: 0.5rem;
        }

        .chart-badge {
            background: #F0EADC;
            color: #576238;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.65rem;
            font-weight: 500;
        }

        .chart-body {
            padding: 1.25rem;
        }

        .chart-canvas-wrapper {
            position: relative;
            height: 280px;
            width: 100%;
        }

        .chart-canvas-wrapper.donut-wrapper {
            height: 280px;
        }

        .chart-canvas-wrapper.tall-wrapper {
            height: 320px;
        }

        /* ============ 2-COLUMN ROW ============ */
        .analytics-row-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 900px) {
            .analytics-row-2col {
                grid-template-columns: 1fr;
            }
        }

        /* ============ PRODUCTS LIST ============ */
        .products-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .product-list-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-rank {
            width: 45px;
            font-size: 1.2rem;
            font-weight: 600;
            color: #D4A054;
        }

        .product-name {
            flex: 1;
            font-size: 0.8rem;
            font-weight: 500;
            color: #2C2B26;
        }

        .product-sales {
            width: 70px;
            font-size: 0.7rem;
            color: #576238;
            font-weight: 600;
            text-align: right;
        }

        .product-bar {
            width: 150px;
            background: #F0EADC;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
        }

        .product-bar-fill {
            background: #576238;
            height: 100%;
            border-radius: 4px;
        }

        /* ============ DATA TABLE ============ */
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
        }

        .record-count {
            background: #F0EADC;
            color: #576238;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 0.875rem 1rem;
            text-align: left;
            border-bottom: 1px solid #F0EADC;
        }

        .data-table th {
            background: #FDF8F0;
            color: #576238;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .data-table tbody tr:hover {
            background: #FDF8F0;
        }

        .footer-row {
            background: #FDF8F0;
            font-weight: 600;
            border-top: 1px solid #E3DCD0;
        }

        /* ============ PERCENTAGE BAR ============ */
        .percentage-bar {
            position: relative;
            background: #F0EADC;
            border-radius: 1rem;
            height: 0.5rem;
            width: 100%;
            max-width: 160px;
            overflow: hidden;
            display: inline-block;
        }

        .percentage-fill {
            background: #576238;
            height: 100%;
            border-radius: 1rem;
        }

        .percentage-bar span {
            margin-left: 0.5rem;
            font-size: 0.7rem;
            color: #9E9D97;
        }

        /* ============ EMPTY STATE ============ */
        .empty-state {
            text-align: center;
            padding: 2rem;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #D4C9BD;
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state p {
            color: #9E9D97;
            margin-bottom: 0.5rem;
        }

        .empty-state small {
            color: #C4C3BC;
            font-size: 0.7rem;
        }

        .empty-row td {
            padding: 0 !important;
        }
    </style>
@endsection