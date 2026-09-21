<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::where('is_available', 1)
            ->where('stock', '>', 0)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $cart = session()->get('cart', []);

        return view('cashier.pos', compact('products', 'cart'));
    }

    public function addToCart(Request $request)
    {
        $productId = $request->product_id;
        $product = Product::findOrFail($productId);

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'stock' => $product->stock,
            ];
        }

        session()->put('cart', $cart);
        return redirect()->route('cashier.pos')->with('success', 'Added to cart!');
    }

    public function removeFromCart(Request $request)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$request->product_id])) {
            unset($cart[$request->product_id]);
        }

        session()->put('cart', $cart);
        return redirect()->route('cashier.pos')->with('success', 'Removed from cart!');
    }

    public function clearCart()
    {
        session()->forget('cart');
        return redirect()->route('cashier.pos')->with('success', 'Cart cleared!');
    }

    /**
     * Place Order — returns JSON for AJAX/fetch
     */
    public function placeOrder(Request $request)
    {
        // Get JSON or form data
        $data = $request->isJson() ? $request->json()->all() : $request->all();

        $cart = $data['items'] ?? [];

        if (empty($cart)) {
            return response()->json(['success' => false, 'error' => 'Cart is empty!'], 400);
        }

        DB::beginTransaction();

        try {
            // Compute total from items
            $totalAmount = 0;
            foreach ($cart as $item) {
                $qty = $item['qty'] ?? $item['quantity'] ?? 0;
                $price = $item['price'] ?? 0;
                $totalAmount += $price * $qty;
            }

            // Apply discount if provided
            $discount = $data['discount'] ?? 0;
            $finalTotal = $data['total'] ?? ($totalAmount - $discount);

            // Create order — status = PENDING by default
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'contact_number' => $data['contact_number'] ?? null,
                'total_amount' => $finalTotal,
                'subtotal' => $totalAmount,
                'discount' => $discount,
                'order_type' => $data['order_type'] ?? 'pick-up',
                'delivery_address' => $data['delivery_address'] ?? null,
                'landmark' => $data['landmark'] ?? null,
                'status' => 'pending',        // ← DITO ANG FIX
                'order_date' => now(),
                'processed_by' => Auth::id(),
            ]);

            // Create order items
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

                // Decrement stock
                Product::where('id', $item['id'])->decrement('stock', $qty);
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
