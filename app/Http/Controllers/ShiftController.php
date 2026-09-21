<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function index()
    {
        $currentUserId = Auth::id();

        // GET ACTIVE SHIFT
        $activeShift = CashierShift::where('user_id', $currentUserId)
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->first();

        // GET SHIFT HISTORY
        $shifts = CashierShift::where('user_id', $currentUserId)
            ->orderBy('shift_start', 'desc')
            ->limit(10)
            ->get();

        // GET LIVE SALES FOR ACTIVE SHIFT
        $liveTotal = 0;
        $liveOrders = 0;
        $avgOrderValue = 0;
        $expectedCash = 0;

        if ($activeShift) {
            $liveTotal = Order::where('processed_by', $currentUserId)
                ->where('status', 'completed')
                ->whereBetween('order_date', [$activeShift->shift_start, now()])
                ->sum('total_amount') ?? 0;

            $liveOrders = Order::where('processed_by', $currentUserId)
                ->where('status', 'completed')
                ->whereBetween('order_date', [$activeShift->shift_start, now()])
                ->count();

            $avgOrderValue = $liveOrders > 0 ? $liveTotal / $liveOrders : 0;
            $expectedCash = $activeShift->starting_cash + $liveTotal;
        }

        // SHIFT STATISTICS
        $totalShifts = $shifts->count();
        $totalSalesAll = $shifts->sum('total_sales');
        $avgSalesPerShift = $totalShifts > 0 ? $totalSalesAll / $totalShifts : 0;
        $bestShift = $shifts->max('total_sales') ?? 0;

        return view('cashier.shift', compact(
            'activeShift',
            'shifts',
            'liveTotal',
            'liveOrders',
            'avgOrderValue',
            'expectedCash',
            'totalShifts',
            'totalSalesAll',
            'avgSalesPerShift',
            'bestShift'
        ));
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'starting_cash' => 'required|numeric|min:0',
        ]);

        $currentUserId = Auth::id();

        // Double-check walang active shift
        $existing = CashierShift::where('user_id', $currentUserId)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return redirect()->route('cashier.shift')->with('error', 'already_active');
        }

        CashierShift::create([
            'user_id' => $currentUserId,
            'shift_start' => now(),
            'starting_cash' => $validated['starting_cash'],
            'ending_cash' => 0,
            'total_sales' => 0,
            'status' => 'active',
            'order_count' => 0,
        ]);

        return redirect()->route('cashier.shift')->with('success', 'started');
    }

    public function end(Request $request)
    {
        $validated = $request->validate([
            'ending_cash' => 'required|numeric|min:0',
            'shift_notes' => 'nullable',
        ]);

        $currentUserId = Auth::id();

        $activeShift = CashierShift::where('user_id', $currentUserId)
            ->where('status', 'active')
            ->first();

        if (!$activeShift) {
            return redirect()->route('cashier.shift')->with('error', 'no_active');
        }

        // Get sales ONLY for this shift's timeframe
        $total_sales = Order::where('processed_by', $currentUserId)
            ->where('status', 'completed')
            ->whereBetween('order_date', [$activeShift->shift_start, now()])
            ->sum('total_amount');

        $order_count = Order::where('processed_by', $currentUserId)
            ->where('status', 'completed')
            ->whereBetween('order_date', [$activeShift->shift_start, now()])
            ->count();

        $activeShift->update([
            'shift_end' => now(),
            'ending_cash' => $validated['ending_cash'],
            'total_sales' => $total_sales,
            'order_count' => $order_count,
            'notes' => $validated['shift_notes'] ?? null,
            'status' => 'closed',
        ]);

        return redirect()->route('cashier.shift')->with('success', 'ended');
    }
}
