<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';



Route::middleware(['auth', 'verified'])->group(function () {

    // Global
    Route::get('/dashboard', [\App\Http\Controllers\Global\DashboardController::class, 'index'])->name('dashboard');


    Route::name('store.')->prefix('store')->group(function () {
        // Product management routes
        Route::resource('products', \App\Http\Controllers\Store\Product\ProductController::class);
        Route::resource('product-types', \App\Http\Controllers\Store\Product\TypeController::class);

        // Stock management routes
        Route::resource('stocks', \App\Http\Controllers\Store\Stock\StockController::class);
        Route::post('stocks/{stock}/update-price/{product}', [\App\Http\Controllers\Store\Stock\StockController::class, 'updateStockProductPrice'])->name('stocks.update-price');
        Route::post('stocks/{stock}/adjustment/{product}', [\App\Http\Controllers\Store\Stock\StockController::class, 'adjustStockProduct'])->name('stocks.adjustment');

        Route::resource('stock-lists', \App\Http\Controllers\Store\Stock\MovementLogsController::class);

        // Expense management routes
        Route::resource('expenses', \App\Http\Controllers\Store\Expense\ExpenseController::class);
        Route::resource('expense-types', \App\Http\Controllers\Store\Expense\TypeController::class);

        // Order management routes
        Route::resource('orders', \App\Http\Controllers\Store\Order\OrderController::class);

        // POS routes
        Route::resource('pos', \App\Http\Controllers\Store\Order\PosController::class)->only(['index', 'store']);

        // customer management routes
        Route::resource('customers', \App\Http\Controllers\Customer::class);

        //  Sales Report management routes
        Route::resource('reports', \App\Http\Controllers\Store\Report\ReportController::class);

        // Product reports management routes
        Route::resource('products-reports', \App\Http\Controllers\Store\Report\ProductReportController::class);

        // Settings
        Route::resource('settings', \App\Http\Controllers\Store\Settings\SettingsController::class);
    });
});
