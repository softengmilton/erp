<?php

namespace App\Http\Controllers\Store\Stock;

use App\Http\Controllers\Controller;
use App\Models\StoreProduct;
use App\Models\StoreStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = StoreStock::query()
            ->with([
                'storeStockItems.storeProduct',
                'storeStockMovements'
            ])
            ->withCount([
                'storeStockItems as total_quantity' => fn($query) =>
                $query->select(DB::raw('COALESCE(SUM(quantity), 0)'))
            ])
            ->withCount([
                'storeStockMovements as total_movements' => fn($query) =>
                $query->where('source_type', 'sale')
                    ->select(DB::raw('COALESCE(SUM(change_quantity), 0)'))
            ])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return Inertia::render('store/stock/stock/Index', [
            'stocks' => $stocks,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = StoreProduct::query()
            ->with('primaryImage')
            ->select('id', 'name')
            ->orderBy('id', 'desc')
            ->get();
        return Inertia::render('store/stock/stock/Create', [
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
