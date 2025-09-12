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
    public function index(Request $request)
    {
        // Categories
        $allCategory = DB::table('store_product_types')
            ->select('id', 'name')
            ->get();

        // Pick product type from all-time sales
        $productTypeId = DB::table('store_order_items')
            ->join('store_products', 'store_order_items.store_product_id', '=', 'store_products.id')
            ->value('store_products.store_product_type_id');

        // Category id
        $category_id = $request->category_id ? $request->category_id : $productTypeId;

        // Stock items (all-time, no date filter)
        $stockItems = DB::table('store_stock_items')
            ->select('store_product_id', DB::raw('SUM(quantity) as total_items'))
            ->groupBy('store_product_id');

        // Stock movements (all-time, no date filter)
        $stockMovements = DB::table('store_stock_movements')
            ->select('store_product_id', DB::raw('SUM(change_quantity) as total_movements'))
            ->where('source_type', 'sale')
            ->groupBy('store_product_id');

        // Stock left = stock in − stock out (all-time)
        $stockLeft = DB::table('store_products')
            ->leftJoinSub($stockItems, 'si', function ($join) {
                $join->on('store_products.id', '=', 'si.store_product_id');
            })
            ->leftJoinSub($stockMovements, 'sm', function ($join) {
                $join->on('store_products.id', '=', 'sm.store_product_id');
            })
            ->select(
                'store_products.id',
                'store_products.name',
                DB::raw('COALESCE(si.total_items, 0) - COALESCE(sm.total_movements, 0) as totalStockLeft')
            )
            ->where('store_products.store_product_type_id', $category_id)
            ->get();

        // Transform into category-wise columns
        $stockReport = [];
        foreach ($stockLeft as $item) {
            $stockReport['1']['categories'][$item->name] = [
                'totalStockLeft' => $item->totalStockLeft,
            ];
        }

        return Inertia::render('store/reports/StockReport', [
            'allCategory' => $allCategory,
            'stockReport' => $stockReport,
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
