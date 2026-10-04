<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Best Products of the Day — TOP 4 best sellers lang
        $bestSellers = Product::where('is_available', true)
            ->where('stock', '>', 0)
            ->where('is_best_seller', true)
            ->orderBy('updated_at', 'desc')
            ->limit(4)
            ->get();

        // Kung walang best sellers, kunin ang unang 4 na available products
        if ($bestSellers->isEmpty()) {
            $bestSellers = Product::where('is_available', true)
                ->where('stock', '>', 0)
                ->orderBy('updated_at', 'desc')
                ->limit(4)
                ->get();
        }

        return view('landing', compact('bestSellers'));
    }
}
