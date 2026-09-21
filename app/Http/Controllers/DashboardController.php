<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ============================================
        // CORE STATS
        // ============================================
        $pending = Order::where('status', 'pending')->count();
        $today = Order::whereDate('order_date', today())->where('status', 'completed')->sum('total_amount');
        $totalOrders = Order::where('status', '!=', 'refunded')->count();
        $totalProducts = Product::count();

        $completedOrders = Order::where('status', 'completed')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();
        $lowStock = Product::where('stock', '<', 10)->where('stock', '>', 0)->count();
        $outOfStock = Product::where('stock', 0)->count();

        // ============================================
        // SALES DATA
        // ============================================
        $monthlySales = Order::where('status', 'completed')
            ->whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year)
            ->sum('total_amount');

        $weeklySales = Order::where('status', 'completed')
            ->where('order_date', '>=', now()->startOfWeek())
            ->sum('total_amount');

        $yesterdaySales = Order::where('status', 'completed')
            ->whereDate('order_date', now()->subDay())
            ->sum('total_amount');

        $growth = 0;
        if ($yesterdaySales > 0) {
            $growth = (($today - $yesterdaySales) / $yesterdaySales) * 100;
        }

        // ============================================
        // 7-DAY SALES TREND
        // ============================================
        $salesTrend = [];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
            $salesTrend[] = Order::whereDate('order_date', $date->toDateString())
                ->where('status', 'completed')
                ->sum('total_amount') ?: 0;
        }

        // ============================================
        // BEST SELLERS
        // ============================================
        $best = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as sold'), DB::raw('SUM(order_items.subtotal) as revenue'))
            ->where('orders.status', 'completed')
            ->groupBy('products.id', 'products.name')
            ->orderBy('sold', 'desc')
            ->limit(5)
            ->get();

        // ============================================
        // SLOW MOVING PRODUCTS (no sales in 30 days)
        // ============================================
        $slowMoving = DB::table('products')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', '=', 'completed')
                    ->where('orders.order_date', '>=', now()->subDays(30));
            })
            ->select('products.name', 'products.stock', DB::raw('COALESCE(SUM(order_items.quantity), 0) as sold'))
            ->where('products.stock', '>', 0)
            ->groupBy('products.id', 'products.name', 'products.stock')
            ->havingRaw('COALESCE(SUM(order_items.quantity), 0) = 0')
            ->orderBy('products.stock', 'desc')
            ->limit(5)
            ->get();

        // ============================================
        // PEAK HOUR (today's busiest hour)
        // ============================================
        $peakHourData = Order::select(
            DB::raw('HOUR(order_date) as hour'),
            DB::raw('COUNT(*) as order_count')
        )
            ->whereDate('order_date', today())
            ->where('status', 'completed')
            ->groupBy(DB::raw('HOUR(order_date)'))
            ->orderBy('order_count', 'desc')
            ->first();

        $peakHour = $peakHourData
            ? date('g A', strtotime($peakHourData->hour . ':00'))
            : '—';
        $peakHourCount = $peakHourData->order_count ?? 0;

        // ============================================
        // REFUND STATS
        // ============================================
        $todayRefunds = Refund::whereDate('refund_date', today())->count();
        $todayRefundAmount = Refund::whereDate('refund_date', today())->sum('refund_amount');
        $monthlyRefunds = Refund::whereMonth('refund_date', now()->month)
            ->whereYear('refund_date', now()->year)
            ->count();
        $monthlyRefundAmount = Refund::whereMonth('refund_date', now()->month)
            ->whereYear('refund_date', now()->year)
            ->sum('refund_amount');

        // ============================================
        // AVERAGE ORDER VALUE (this month)
        // ============================================
        $monthlyOrderCount = Order::where('status', 'completed')
            ->whereMonth('order_date', now()->month)
            ->whereYear('order_date', now()->year)
            ->count();
        $avgOrderValue = $monthlyOrderCount > 0 ? $monthlySales / $monthlyOrderCount : 0;

        // ============================================
        // RECENT ORDERS
        // ============================================
        $recentOrders = Order::where('status', '!=', 'refunded')
            ->orderBy('order_date', 'desc')
            ->limit(5)
            ->get();

        // ============================================
        // LOW STOCK ITEMS
        // ============================================
        $lowStockItems = Product::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        // ============================================
        // CUSTOMER INSIGHTS
        // ============================================
        $totalCustomers = Order::where('status', 'completed')->distinct('customer_name')->count('customer_name');

        $repeatCustomers = DB::table('orders')
            ->select('customer_name', DB::raw('COUNT(*) as order_count'))
            ->where('status', 'completed')
            ->groupBy('customer_name')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        // Top 5 customers by total spending
        $topCustomers = Order::select(
            'customer_name',
            DB::raw('COUNT(*) as order_count'),
            DB::raw('COALESCE(SUM(total_amount), 0) as total_spent')
        )
            ->where('status', 'completed')
            ->groupBy('customer_name')
            ->orderBy('total_spent', 'desc')
            ->limit(5)
            ->get();

        // ============================================
        // MONTHLY TARGET
        // ============================================
        $monthlyTarget = 50000;
        $targetProgress = $monthlySales > 0 ? min(($monthlySales / $monthlyTarget) * 100, 100) : 0;

        $orderStats = [
            'pending' => $pending,
            'completed' => $completedOrders,
            'cancelled' => $cancelledOrders,
            'total' => $totalOrders
        ];

        return view('dashboard', compact(
            'pending',
            'today',
            'totalOrders',
            'totalProducts',
            'completedOrders',
            'cancelledOrders',
            'lowStock',
            'outOfStock',
            'monthlySales',
            'weeklySales',
            'growth',
            'labels',
            'salesTrend',
            'best',
            'slowMoving',
            'peakHour',
            'peakHourCount',
            'todayRefunds',
            'todayRefundAmount',
            'monthlyRefunds',
            'monthlyRefundAmount',
            'avgOrderValue',
            'recentOrders',
            'lowStockItems',
            'totalCustomers',
            'repeatCustomers',
            'topCustomers',
            'monthlyTarget',
            'targetProgress',
            'orderStats'
        ));
    }
}
