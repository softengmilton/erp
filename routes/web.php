<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';



Route::middleware(['auth', 'verified'])->group(function () {
    Route::name('store.')->prefix('store')->group(function () {
        // Resource routes
        Route::resource('products', \App\Http\Controllers\Store\Product\ProductController::class);
        Route::resource('product-types', \App\Http\Controllers\Store\Product\TypeController::class);
        Route::resource('stocks', \App\Http\Controllers\Store\Stock\StockController::class);
        Route::resource('expenses', \App\Http\Controllers\Store\Expense\ExpenseController::class);
        Route::resource('expense-types', \App\Http\Controllers\Store\Expense\TypeController::class);
        Route::resource('orders', \App\Http\Controllers\Store\Order\OrderController::class);
        Route::resource('pos', \App\Http\Controllers\Store\Order\PosController::class)->only(['index', 'store']);

        // Static routes
        Route::post('stocks/{stock}/update-price/{product}', [\App\Http\Controllers\Store\Stock\StockController::class, 'updateStockProductPrice'])->name('stocks.update-price');
        // stock adjustment
        Route::post('stocks/{stock}/adjustment/{product}', [\App\Http\Controllers\Store\Stock\StockController::class, 'adjustStockProduct'])->name('stocks.adjustment');
    });
});
