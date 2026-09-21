<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $lowStock = Product::where('stock', '<=', 20)->where('stock', '>', 0)->orderBy('stock', 'asc')->get();
        $outOfStock = Product::where('stock', '<=', 0)->orderBy('name', 'asc')->get();
        $products = Product::orderBy('stock', 'asc')->paginate(15);

        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');
        $lowStockCount = $lowStock->count();
        $outOfStockCount = $outOfStock->count();

        return view('admin.inventory', compact('products', 'lowStock', 'outOfStock', 'totalProducts', 'totalStock', 'lowStockCount', 'outOfStockCount'));
    }

    public function bulkUpdate(Request $request)
    {
        // Quick adjust
        if ($request->has('bulk_adjust')) {
            $ids = $request->selected_ids ?? [];
            $amount = (int)$request->adjust_amount;
            $mode = $request->adjust_mode;

            if (!empty($ids) && $amount > 0) {
                if ($mode === 'add') {
                    Product::whereIn('id', $ids)->increment('stock', $amount);
                } else {
                    foreach (Product::whereIn('id', $ids)->get() as $p) {
                        $p->stock = max(0, $p->stock - $amount);
                        $p->save();
                    }
                }
                return redirect()->route('admin.inventory', ['success' => 'bulk_adjusted', 'count' => count($ids)]);
            }
        }

        // Standard bulk update
        $updates = $request->stock ?? [];
        $count = 0;
        foreach ($updates as $id => $stock) {
            $stock = (int)$stock;
            $id = (int)$id;
            if ($stock >= 0) {
                Product::where('id', $id)->update(['stock' => $stock]);
                $count++;
            }
        }

        if ($count > 0) {
            return redirect()->route('admin.inventory', ['success' => 'bulk_updated', 'count' => $count]);
        }

        return redirect()->route('admin.inventory');
    }
}
