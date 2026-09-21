<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(20);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|max:50',
            'stock' => 'required|integer|min:0',
        ]);

        $validated['is_available'] = true;
        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'added');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|max:100',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|max:50',
            'stock' => 'required|integer|min:0',
        ]);

        $validated['is_available'] = $request->has('is_available') ? 1 : 0;
        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'updated');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'deleted');
    }

    public function toggle($id)
    {
        $product = Product::findOrFail($id);
        $product->is_available = !$product->is_available;
        $product->save();
        return redirect()->route('admin.products.index')->with('success', 'toggled');
    }

    public function quickStock(Request $request)
    {
        $product = Product::findOrFail($request->quick_stock_id);
        $product->stock = (int)$request->quick_stock_value;
        $product->save();
        return redirect()->route('admin.products.index')->with('success', 'stock_updated');
    }
}
