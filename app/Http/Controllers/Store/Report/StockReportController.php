<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use DB;
use Carbon\Carbon;

class StockReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // $startDate =  now()->startOfMonth()->startOfDay();
        // $endDate   =  now()->endOfMonth()->endOfDay();
        // $startDate = $request->startDate ? Carbon::parse($request->startDate)->startOfDay() : now()->startOfMonth()->startOfDay();
        // $endDate   = $request->endDate   ? Carbon::parse($request->endDate)->endOfDay() : now()->endOfMonth()->endOfDay();
        // dd($monthName);

        $month = now()->month;  // current month number (e.g. 9)
        $year  = now()->year;   // current year (e.g. 2025)

        // Categories
        $allCategory = DB::table('store_product_types')
            ->select('store_product_types.id', 'store_product_types.name')->get();
        // dd($allCategory);
        $category_id = '1';
        $stockLeft = DB::table('store_products')
            ->leftJoin('store_stock_items', function ($join) use ($month, $year) {
                $join->on('store_products.id', '=', 'store_stock_items.store_product_id')
                    ->whereMonth('store_stock_items.created_at', $month)
                    ->whereYear('store_stock_items.created_at', $year);
            })
            ->leftJoin('store_stock_movements', function ($join) use ($month, $year) {
                $join->on('store_products.id', '=', 'store_stock_movements.store_product_id')
                    ->where('store_stock_movements.source_type', 'sale')
                    ->whereMonth('store_stock_movements.created_at', $month)
                    ->whereYear('store_stock_movements.created_at', $year);
            })
            ->select(
                'store_products.id',
                'store_products.name',
                DB::raw('COALESCE(SUM(store_stock_items.quantity), 0) - COALESCE(SUM(store_stock_movements.change_quantity), 0) as totalStockLeft')
            )
            ->where('store_products.store_product_type_id', $category_id)
            ->groupBy('store_products.id', 'store_products.name')
            ->get();

        dd($stockLeft);
            
        return Inertia::render('store/reports/StockReport',[
            'allCategory' => $allCategory,
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
