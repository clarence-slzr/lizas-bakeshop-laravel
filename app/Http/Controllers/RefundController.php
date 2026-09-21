<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function index()
    {
        $currentUserId = Auth::id();
        $isAdminUser = Auth::user()->isAdmin();

        $message = '';
        $message_type = '';
        $order = null;
        $items = [];
        $alreadyRefunded = 0;
        $refundedQty = [];

        // ============================================
        // SEARCH ORDER
        // ============================================
        if (request()->has('search_order')) {
            $order_id = (int) request('order_id');

            $order = Order::where('id', $order_id)
                ->where('status', 'completed')
                ->first();

            if ($order) {
                $refundedQty = DB::table('refund_items')
                    ->where('order_id', $order_id)
                    ->select('order_item_id', DB::raw('SUM(quantity) as total_refunded'))
                    ->groupBy('order_item_id')
                    ->pluck('total_refunded', 'order_item_id')
                    ->toArray();

                $items = OrderItem::with('product')
                    ->where('order_id', $order_id)
                    ->get();

                $alreadyRefunded = (float) Refund::where('order_id', $order_id)
                    ->sum('refund_amount');
            } else {
                $message = "Order #$order_id not found or is not eligible for refund.";
                $message_type = 'error';
            }
        }

        // ============================================
        // REFUND HISTORY (WITH SEARCH)
        // ============================================
        $historySearch = trim(request('history_search', ''));

        $refundQuery = Refund::with('order', 'processedBy');

        if ($historySearch !== '') {
            $refundQuery->whereHas('order', function ($q) use ($historySearch) {
                $q->where('customer_name', 'LIKE', "%$historySearch%")
                    ->orWhere('id', 'LIKE', "%$historySearch%");
            });
        }

        $refunds = $refundQuery->orderBy('refund_date', 'desc')->limit(20)->get();

        // ============================================
        // STATS (today + all time)
        // ============================================
        $todayRefundCount = Refund::whereDate('refund_date', today())->count();
        $todayRefundAmount = (float) Refund::whereDate('refund_date', today())->sum('refund_amount');
        $totalRefunds = Refund::count();
        $totalRefundAmount = (float) Refund::sum('refund_amount');

        $availableRefund = $order ? $order->total_amount : 0;

        return view('cashier.refund', compact(
            'message',
            'message_type',
            'order',
            'items',
            'alreadyRefunded',
            'refundedQty',
            'refunds',
            'historySearch',
            'todayRefundCount',
            'todayRefundAmount',
            'totalRefunds',
            'totalRefundAmount',
            'availableRefund'
        ));
    }

    public function store(Request $request)
    {
        $currentUserId = Auth::id();

        $order_id = (int) $request->order_id;
        $refund_reason = trim($request->refund_reason);
        $refund_notes = trim($request->refund_notes ?? '');
        $refund_items = $request->refund_items ?? [];

        if (empty($refund_items)) {
            return redirect()->route('cashier.refund')->with('message', 'Please select at least one item to refund.')->with('message_type', 'error');
        }

        if (empty($refund_reason)) {
            return redirect()->route('cashier.refund')->with('message', 'Please provide a reason for the refund.')->with('message_type', 'error');
        }

        DB::beginTransaction();

        try {
            $orderData = Order::where('id', $order_id)->where('status', 'completed')->first();

            if (!$orderData) {
                throw new \Exception("Order not found or not eligible.");
            }

            $refund_amount = 0;
            $refundedItemsLog = [];

            foreach ($refund_items as $item_id => $item_data) {
                $item_id = (int) $item_id;
                $qty_to_refund = (int) ($item_data['qty'] ?? 0);

                if ($qty_to_refund <= 0) continue;

                $item = OrderItem::where('id', $item_id)
                    ->where('order_id', $order_id)
                    ->first();

                if (!$item) {
                    throw new \Exception("Invalid item in refund.");
                }

                $alreadyRefundedQty = (int) DB::table('refund_items')
                    ->where('order_item_id', $item_id)
                    ->where('order_id', $order_id)
                    ->sum('quantity');

                $remainingQty = $item->quantity - $alreadyRefundedQty;

                if ($qty_to_refund > $remainingQty) {
                    throw new \Exception("Refund quantity exceeds remaining quantity for this item.");
                }

                $original_qty = $item->quantity + $alreadyRefundedQty;
                $item_price = $original_qty > 0 ? $item->subtotal / $original_qty : 0;
                $refund_amount += $item_price * $qty_to_refund;

                $refundedItemsLog[] = [
                    'item_id' => $item_id,
                    'qty' => $qty_to_refund,
                    'product_id' => $item->product_id
                ];

                $new_qty = $item->quantity - $qty_to_refund;
                if ($new_qty > 0) {
                    $new_subtotal = $new_qty * $item_price;
                    $item->update(['quantity' => $new_qty, 'subtotal' => $new_subtotal]);
                } else {
                    $item->delete();
                }

                Product::where('id', $item->product_id)->increment('stock', $qty_to_refund);
            }

            if ($refund_amount <= 0) {
                throw new \Exception("Refund amount is zero. Please select items to refund.");
            }

            $remaining_items = OrderItem::where('order_id', $order_id)->count();

            if ($remaining_items == 0) {
                $orderData->update(['status' => 'refunded', 'total_amount' => 0]);
            } else {
                $new_total = OrderItem::where('order_id', $order_id)->sum('subtotal');
                $orderData->update(['total_amount' => $new_total]);
            }

            $refund = Refund::create([
                'order_id' => $order_id,
                'refund_amount' => $refund_amount,
                'reason' => $refund_reason,
                'notes' => $refund_notes,
                'processed_by' => $currentUserId,
                'refund_date' => now(),
                'status' => 'approved',
            ]);

            foreach ($refundedItemsLog as $log) {
                DB::table('refund_items')->insert([
                    'refund_id' => $refund->id,
                    'order_id' => $order_id,
                    'order_item_id' => $log['item_id'],
                    'product_id' => $log['product_id'],
                    'quantity' => $log['qty'],
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('cashier.refund')
                ->with('message', 'Refund processed successfully! Amount refunded: ₱' . number_format($refund_amount, 2))
                ->with('message_type', 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cashier.refund')
                ->with('message', 'Error processing refund: ' . $e->getMessage())
                ->with('message_type', 'error');
        }
    }
}
