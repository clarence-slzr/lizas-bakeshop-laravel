<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /**
     * Show the refund page with history and search.
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $query = Refund::with(['order', 'processedBy']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'LIKE', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('customer_name', 'LIKE', "%{$search}%")
                            ->orWhere('contact_number', 'LIKE', "%{$search}%");
                    })
                    ->orWhere('reason', 'LIKE', "%{$search}%");
            });
        }

        $refunds = $query->orderBy('refund_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        // Stats
        $todayRefunds = Refund::whereDate('refund_date', today())->count();
        $todayAmount = Refund::whereDate('refund_date', today())->sum('refund_amount');
        $totalRefunds = Refund::count();
        $totalAmount = Refund::sum('refund_amount');

        return view('cashier.refund', compact(
            'refunds',
            'todayRefunds',
            'todayAmount',
            'totalRefunds',
            'totalAmount',
            'search'
        ));
    }

    /**
     * STEP 1: Search for an order to refund.
     * Shows order details + reason form (STEP 2).
     */
    public function search(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
        ]);

        // Clean the order_id input (remove # and leading zeros)
        $orderId = ltrim(str_replace('#', '', trim($request->order_id)), '0');
        if ($orderId === '') {
            $orderId = '0';
        }

        $order = Order::with('items.product')->find($orderId);

        // Order not found
        if (!$order) {
            return redirect()->route('cashier.refund')
                ->with('error', "Order #{$request->order_id} not found.");
        }

        // Check if order is completed
        if ($order->status !== 'completed') {
            return redirect()->route('cashier.refund')
                ->with('error', "Order #{$order->id} cannot be refunded. Only completed orders can be refunded. Current status: " . ucfirst($order->status));
        }

        // Check if already refunded
        $existingRefund = Refund::where('order_id', $order->id)->first();
        if ($existingRefund) {
            return redirect()->route('cashier.refund')
                ->with('error', "Order #{$order->id} has already been refunded.");
        }

        // Prepare data for view
        $todayRefunds = Refund::whereDate('refund_date', today())->count();
        $todayAmount = Refund::whereDate('refund_date', today())->sum('refund_amount');
        $totalRefunds = Refund::count();
        $totalAmount = Refund::sum('refund_amount');
        $refunds = Refund::with(['order', 'processedBy'])
            ->orderBy('refund_date', 'desc')
            ->paginate(20);
        $search = '';

        return view('cashier.refund', compact(
            'refunds',
            'todayRefunds',
            'todayAmount',
            'totalRefunds',
            'totalAmount',
            'search',
            'order'
        ));
    }

    /**
     * STEP 3: Store a new refund (with reason).
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Please provide a reason for the refund.',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Check if order is completed
        if ($order->status !== 'completed') {
            return redirect()->route('cashier.refund')
                ->with('error', 'Only completed orders can be refunded.');
        }

        // Check if already refunded
        $existingRefund = Refund::where('order_id', $order->id)->first();
        if ($existingRefund) {
            return redirect()->route('cashier.refund')
                ->with('error', 'This order has already been refunded.');
        }

        // Create refund
        Refund::create([
            'order_id' => $order->id,
            'refund_amount' => $order->total_amount,
            'reason' => $request->reason,
            'refund_date' => now(),
            'processed_by' => Auth::id(),
            'status' => 'approved',
        ]);

        // Update order status
        $order->update(['status' => 'refunded']);

        return redirect()->route('cashier.refund')
            ->with('success', "Order #{$order->id} refunded successfully!");
    }
}
