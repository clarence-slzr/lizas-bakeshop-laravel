<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ChartsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;

// ============================================
// LANDING PAGE
// ============================================
Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// ============================================
// AUTH ROUTES (from Laravel Breeze)
// ============================================
require __DIR__ . '/auth.php';

// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // ============================================
    // ADMIN ROUTES
    // ============================================
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

        // Users
        Route::resource('users', UserController::class);

        // Products
        Route::resource('products', ProductController::class);
        Route::get('products/{id}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
        Route::post('products/quick-stock', [ProductController::class, 'quickStock'])->name('products.quick-stock');

        // Inventory
        Route::get('inventory', [InventoryController::class, 'index'])->name('inventory');
        Route::post('inventory/bulk-update', [InventoryController::class, 'bulkUpdate'])->name('inventory.bulk-update');

        // Analytics & Reports
        Route::get('charts', [ChartsController::class, 'index'])->name('charts');
        Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales-report');
    });

    // ============================================
    // ORDERS (shared - admin + cashier)
    // ============================================
    Route::resource('orders', OrderController::class);
    Route::get('orders/{id}/details', [OrderController::class, 'show'])->name('orders.details');
    Route::patch('orders/{id}/complete', [OrderController::class, 'markComplete'])->name('orders.complete');
    Route::patch('orders/{id}/cancel', [OrderController::class, 'markCancelled'])->name('orders.cancel');

    // ============================================
    // CASHIER ROUTES
    // ============================================
    Route::middleware(['cashier'])->prefix('cashier')->name('cashier.')->group(function () {
        Route::get('pos', [POSController::class, 'index'])->name('pos');
        Route::post('add-to-cart', [POSController::class, 'addToCart'])->name('add-to-cart');
        Route::post('remove-from-cart', [POSController::class, 'removeFromCart'])->name('remove-from-cart');
        Route::post('clear-cart', [POSController::class, 'clearCart'])->name('clear-cart');
        Route::post('place-order', [POSController::class, 'placeOrder'])->name('place-order');
        Route::get('receipt/{id}', [ReceiptController::class, 'show'])->name('receipt');
        Route::get('shift', [ShiftController::class, 'index'])->name('shift');
        Route::post('shift/start', [ShiftController::class, 'start'])->name('shift.start');
        Route::post('shift/end', [ShiftController::class, 'end'])->name('shift.end');
        Route::get('refund', [RefundController::class, 'index'])->name('refund');
        Route::post('refund/store', [RefundController::class, 'store'])->name('refund.store');
        Route::get('order-history', [OrderController::class, 'history'])->name('order-history');
    });
    // Logout confirmation page
    Route::get('/logout-confirm', function () {
        return view('auth.logout-confirm');
    })->middleware('auth')->name('logout.confirm');
});
