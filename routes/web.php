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
        Route::resource('products', \App\Http\Controllers\Store\Product\ProductController::class);
        Route::resource('product-types', \App\Http\Controllers\Store\Product\TypeController::class);
        
        Route::resource('expenses',\App\Http\Controllers\Store\Expense\ExpenseController::class);
        Route::resource('expense-types',\App\Http\Controllers\Store\Expense\TypeController::class);
    });
});
