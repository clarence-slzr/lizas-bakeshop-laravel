<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChartsController extends Controller
{
    public function index()
    {
        $salesTrend = [];
        $salesLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $salesLabels[] = $date->format('M d');
            $salesTrend[] = Order::whereIn('status', ['completed', 'pending'])
                ->whereDate('order_date', $date->toDateString())
                ->sum('total_amount');
        }

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')->orderBy('total_qty', 'desc')->limit(5)->get();

        $topProductLabels = [];
        $topProductData = [];
        foreach ($topProducts as $item) {
            $product = Product::find($item->product_id);
            $topProductLabels[] = $product?->name ?? 'N/A';
            $topProductData[] = $item->total_qty;
        }

        $statusData = [
            Order::where('status', 'completed')->count(),
            Order::where('status', 'pending')->count(),
            Order::where('status', 'cancelled')->count(),
            Order::where('status', 'refunded')->count(),
        ];

        $totalRevenue = Order::whereIn('status', ['completed', 'pending'])->sum('total_amount');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalUsers = User::count();

        return view('admin.charts', compact(
            'salesLabels',
            'salesTrend',
            'topProductLabels',
            'topProductData',
            'statusData',
            'totalRevenue',
            'totalOrders',
            'totalProducts',
            'totalUsers'
        ));
    }
}
