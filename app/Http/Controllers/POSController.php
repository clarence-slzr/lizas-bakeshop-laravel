<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class POSController extends Controller
{
    public function index()
    {
        // This will now only pull available products with actual stock
        $products = Product::where('is_available', 1)
            ->where('stock', '>', 0)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $cart = session()->get('cart', []);

        return view('cashier.pos', compact('products', 'cart'));
    }

    // ... (Keep your addToCart, removeFromCart, clearCart methods as they are) ...

    /**
     * Place Order — returns JSON for AJAX/fetch
     */
    public function placeOrder(Request $request)
    {
        $data = $request->isJson() ? $request->json()->all() : $request->all();
        $cart = $data['items'] ?? [];

        if (empty($cart)) {
            return response()->json(['success' => false, 'error' => 'Cart is empty!'], 400);
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            foreach ($cart as $item) {
                $qty = $item['qty'] ?? $item['quantity'] ?? 0;
                $price = $item['price'] ?? 0;
                $totalAmount += $price * $qty;
            }

            $discount = $data['discount'] ?? 0;
            $finalTotal = $data['total'] ?? ($totalAmount - $discount);
            $orderDate = Carbon::now('Asia/Manila');

            // Create Order
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'contact_number' => $data['contact_number'] ?? null,
                'total_amount' => $finalTotal,
                'subtotal' => $totalAmount,
                'discount' => $discount,
                'order_type' => $data['order_type'] ?? 'pick-up',
                'delivery_address' => $data['delivery_address'] ?? null,
                'landmark' => $data['landmark'] ?? null,
                'status' => 'pending',
                'order_date' => $orderDate,
                'processed_by' => Auth::id(),
            ]);

            // Create Order Items & Update Stock
            foreach ($cart as $item) {
                $qty = $item['qty'] ?? $item['quantity'] ?? 0;
                $price = $item['price'] ?? 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $price * $qty,
                ]);

                // ✅ FIXED STOCK LOGIC HERE
                $product = Product::find($item['id']);
                if ($product) {
                    $newStock = $product->stock - $qty;

                    // Prevent negative stock just in case
                    if ($newStock < 0) {
                        throw new \Exception("Insufficient stock for " . $product->name);
                    }

                    // Update stock AND set to unavailable if 0
                    $product->update([
                        'stock' => $newStock,
                        'is_available' => $newStock > 0 ? 1 : 0
                    ]);
                }
            }

            DB::commit();
            session()->forget('cart');

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
