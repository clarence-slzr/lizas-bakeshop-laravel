<?php

namespace App\Http\Controllers;

use App\Models\Order;

class ReceiptController extends Controller
{
    public function show($id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        return view('cashier.receipt', compact('order'));
    }
}
