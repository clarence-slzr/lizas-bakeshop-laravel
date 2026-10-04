<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display inventory management page.
     */
    public function index(Request $request)
    {
        // ============================================
        // FILTERS & SORTING
        // ============================================
        $search = trim($request->input('search', ''));
        $filterCategory = trim($request->input('category', ''));
        $filterStock = trim($request->input('stock', ''));
        $sortBy = $request->input('sort', 'id_asc');

        $query = Product::query();

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($filterCategory !== '') {
            $query->where('category', $filterCategory);
        }

        // Stock filter
        if ($filterStock === 'low') {
            $query->where('stock', '<', 10)->where('stock', '>', 0);
        } elseif ($filterStock === 'out') {
            $query->where('stock', 0);
        } elseif ($filterStock === 'good') {
            $query->where('stock', '>=', 10);
        } elseif ($filterStock === 'critical') {
            $query->where('stock', '<', 5)->where('stock', '>', 0);
        }

        // Sorting
        $sortOptions = [
            'id_asc' => ['id', 'ASC'],
            'id_desc' => ['id', 'DESC'],
            'name_asc' => ['name', 'ASC'],
            'name_desc' => ['name', 'DESC'],
            'stock_asc' => ['stock', 'ASC'],
            'stock_desc' => ['stock', 'DESC'],
            'value_asc' => [DB::raw('(price * stock)'), 'ASC'],
            'value_desc' => [DB::raw('(price * stock)'), 'DESC'],
        ];
        $sortOpt = $sortOptions[$sortBy] ?? ['id', 'ASC'];

        // Pagination
        $perPage = 15;
        $products = $query->orderBy($sortOpt[0], $sortOpt[1])->paginate($perPage);
        $products->appends($request->query());

        $totalProducts = $query->count();

        // ============================================
        // CATEGORIES FOR FILTER
        // ============================================
        $categories = Product::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category', 'asc')
            ->pluck('category');

        // ============================================
        // STATISTICS (computed sa controller — hindi sa view)
        // ============================================
        $totalProductsCount = Product::count();
        $lowStockCount = Product::where('stock', '>', 0)->where('stock', '<', 10)->count();
        $outOfStockCount = Product::where('stock', '<=', 0)->count();
        $criticalCount = Product::where('stock', '>', 0)->where('stock', '<', 5)->count();

        // ✅ Inventory Value — price × stock (completed lang)
        // Ginagamit ang DB::raw para accurate sa SQL
        $inventoryValue = Product::select(DB::raw('COALESCE(SUM(price * stock), 0) as total'))->value('total');

        return view('admin.inventory', compact(
            'products',
            'categories',
            'search',
            'filterCategory',
            'filterStock',
            'sortBy',
            'totalProducts',
            'totalProductsCount',
            'lowStockCount',
            'outOfStockCount',
            'criticalCount',
            'inventoryValue'
        ));
    }

    /**
     * Bulk update stocks (quick adjust o standard).
     */
    public function bulkUpdate(Request $request)
    {
        // Quick adjust mode (add/subtract)
        if ($request->has('bulk_adjust')) {
            $ids = $request->selected_ids ?? [];
            $amount = (int) $request->bulk_adjust_amount;
            $mode = $request->bulk_adjust_mode;

            if (empty($ids) || $amount <= 0) {
                return redirect()->route('admin.inventory')
                    ->with('error', 'Please select products and enter a valid amount.');
            }

            if ($mode === 'add') {
                Product::whereIn('id', $ids)->increment('stock', $amount);
            } elseif ($mode === 'subtract') {
                foreach (Product::whereIn('id', $ids)->get() as $p) {
                    $p->stock = max(0, $p->stock - $amount);
                    $p->save();
                }
            } else {
                return redirect()->route('admin.inventory')
                    ->with('error', 'Invalid adjustment mode.');
            }

            return redirect()->route('admin.inventory')
                ->with('success', 'bulk_adjusted')
                ->with('count', count($ids));
        }

        // Standard bulk update (per-row save)
        $updates = $request->stock ?? [];
        $count = 0;

        foreach ($updates as $id => $stock) {
            $stock = (int) $stock;
            $id = (int) $id;

            if ($stock >= 0) {
                Product::where('id', $id)->update(['stock' => $stock]);
                $count++;
            }
        }

        if ($count > 0) {
            return redirect()->route('admin.inventory')
                ->with('success', 'bulk_updated')
                ->with('count', $count);
        }

        return redirect()->route('admin.inventory');
    }
}
