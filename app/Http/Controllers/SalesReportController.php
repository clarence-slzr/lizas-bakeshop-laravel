<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index()
    {
        $totalSales = Order::whereIn('status', ['completed', 'pending'])->sum('total_amount');
        $todaySales = Order::whereIn('status', ['completed', 'pending'])->whereDate('order_date', today())->sum('total_amount');
        $weekSales = Order::whereIn('status', ['completed', 'pending'])->whereBetween('order_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_amount');
        $monthSales = Order::whereIn('status', ['completed', 'pending'])->whereMonth('order_date', now()->month)->sum('total_amount');
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('order_date', today())->count();

        $recentOrders = Order::with('processedBy')->orderBy('order_date', 'desc')->paginate(20);

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->limit(10)
            ->get();

        return view('admin.sales-report', compact(
            'totalSales',
            'todaySales',
            'weekSales',
            'monthSales',
            'totalOrders',
            'todayOrders',
            'recentOrders',
            'topProducts'
        ));
    }
}
