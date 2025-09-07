<?php

namespace App\Http\Controllers\Store\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use DB;

class MovementLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $stock_movements = DB::table('store_stock_movements')
            ->join('store_stocks', 'store_stock_movements.store_stock_id', '=', 'store_stocks.id')
            ->join('store_products', 'store_stock_movements.store_product_id', '=', 'store_products.id')
            ->leftJoin('media', function($join){
                $join->on('media.media_id', '=', 'store_products.id')
                    ->where('media.media_type', '=', 'App\\Models\\StoreProduct');
            })
            ->select(
                'store_stock_movements.change_quantity as quantity',
                'store_stock_movements.source_type as source_type',
                'store_stock_movements.created_at as movement_date',
                'store_stocks.invoice_number as invoice_number',
                'store_products.name as product_name',
                'media.name as media_name',
                'media.path as media_path'
            )
            ->orderBy('movement_date', 'desc')
            ->get();

            // dd($stock_movements);

        return Inertia::render('store/stock/movement/MovementLogs', [
            'stock_movements' => $stock_movements,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
