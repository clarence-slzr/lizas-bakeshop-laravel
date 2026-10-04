<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('processedBy');

        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                    ->orWhere('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('contact_number', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $allowedStatus = ['pending', 'completed', 'cancelled', 'refunded'];
            if (in_array($request->status, $allowedStatus)) {
                $query->where('status', $request->status);
            }
        }

        // Filter by date
        if ($request->filled('date')) {
            switch ($request->date) {
                case 'today':
                    $query->whereDate('order_date', today());
                    break;
                case 'week':
                    $query->where('order_date', '>=', now()->subDays(7));
                    break;
                case 'month':
                    $query->whereMonth('order_date', now()->month)
                        ->whereYear('order_date', now()->year);
                    break;
            }
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate(10);

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'refunded' => Order::where('status', 'refunded')->count(),
            'revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        return view('orders.index', compact('orders', 'stats'));
    }

    public function show($id)
    {
        $order = Order::with('items.product', 'processedBy')->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    public function history(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $statusFilter = $request->input('status', 'all');
        $allowedStatus = ['all', 'pending', 'completed', 'cancelled'];
        if (!in_array($statusFilter, $allowedStatus)) {
            $statusFilter = 'all';
        }

        $dateFilter = $request->input('date', 'all');
        $allowedDates = ['all', 'today', 'week', 'month'];
        if (!in_array($dateFilter, $allowedDates)) {
            $dateFilter = 'all';
        }

        $search = trim($request->input('search', ''));
        $sortBy = $request->input('sort', 'newest');

        // ✅ WALANG FILTER SA processed_by — lahat ng orders (pantay sa admin)
        $query = Order::where('status', '!=', 'refunded');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($dateFilter === 'today') {
            $query->whereDate('order_date', today());
        } elseif ($dateFilter === 'week') {
            $query->where('order_date', '>=', now()->subDays(7));
        } elseif ($dateFilter === 'month') {
            $query->whereMonth('order_date', now()->month)
                ->whereYear('order_date', now()->year);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%$search%")
                    ->orWhere('id', 'LIKE', "%$search%")
                    ->orWhere('contact_number', 'LIKE', "%$search%");
            });
        }

        $sortOptions = [
            'newest' => ['id', 'DESC'],
            'oldest' => ['id', 'ASC'],
            'amount_high' => ['total_amount', 'DESC'],
            'amount_low' => ['total_amount', 'ASC'],
        ];
        $sort = $sortOptions[$sortBy] ?? ['id', 'DESC'];

        $totalCount = $query->count();
        $totalPages = ceil($totalCount / $limit);

        $orders = $query->orderBy($sort[0], $sort[1])
            ->skip($offset)
            ->take($limit)
            ->get();

        // ✅ Base query — lahat ng orders (pantay sa admin)
        $baseQuery = Order::where('status', '!=', 'refunded');

        $totalSales = (clone $baseQuery)->where('status', 'completed')->sum('total_amount');
        $totalOrders = (clone $baseQuery)->count();
        $completedOrders = (clone $baseQuery)->where('status', 'completed')->count();
        $pendingOrders = (clone $baseQuery)->where('status', 'pending')->count();
        $cancelledOrders = (clone $baseQuery)->where('status', 'cancelled')->count();
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $todaySales = Order::where('status', 'completed')
            ->whereDate('order_date', today())
            ->sum('total_amount');

        $todayOrders = Order::where('status', 'completed')
            ->whereDate('order_date', today())
            ->count();

        return view('cashier.order-history', compact(
            'orders',
            'totalCount',
            'totalPages',
            'page',
            'offset',
            'limit',
            'statusFilter',
            'dateFilter',
            'search',
            'sortBy',
            'totalSales',
            'totalOrders',
            'completedOrders',
            'pendingOrders',
            'cancelledOrders',
            'avgOrderValue',
            'todaySales',
            'todayOrders'
        ));
    }

    /**
     * Mark order as completed.
     */
    public function markComplete($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status === 'completed') {
            return back()->with('info', 'Order #' . $order->id . ' is already completed.');
        }

        if ($order->status === 'cancelled') {
            return back()->with('error', 'Cannot complete Order #' . $order->id . ' — it was already cancelled.');
        }

        if ($order->status === 'refunded') {
            return back()->with('error', 'Cannot complete Order #' . $order->id . ' — it was already refunded.');
        }

        $order->update(['status' => 'completed']);

        return back()->with('success', 'Order #' . $order->id . ' marked as completed successfully!');
    }

    /**
     * Mark order as cancelled (restores stock).
     */
    public function markCancelled($id)
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->status === 'cancelled') {
            return back()->with('info', 'Order #' . $order->id . ' is already cancelled.');
        }

        if ($order->status === 'completed') {
            return back()->with('error', 'Cannot cancel Order #' . $order->id . ' — it was already completed.');
        }

        if ($order->status === 'refunded') {
            return back()->with('error', 'Cannot cancel Order #' . $order->id . ' — it was already refunded.');
        }

        DB::beginTransaction();

        try {
            $restoredItems = 0;
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                $restoredItems++;
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return back()->with('success', 'Order #' . $order->id . ' cancelled successfully! ' . $restoredItems . ' item(s) stock restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel Order #' . $order->id . ': ' . $e->getMessage());
        }
    }
}
