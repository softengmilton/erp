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

        $startDate =  now()->startOfMonth()->startOfDay();
        $endDate   =  now()->endOfMonth()->endOfDay();
        // dd($monthName);

        $month = $request->month ? (int) $request->month : now()->month;

        $year  = now()->year;

        // Categories
        $allCategory = DB::table('store_product_types')
            ->select('store_product_types.id', 'store_product_types.name')->get();

        // Pick product type from today's sales
        $productTypeId = DB::table('store_order_items')
            ->join('store_products', 'store_order_items.store_product_id', '=', 'store_products.id')
            ->whereBetween('store_order_items.created_at', [$startDate, $endDate])
            ->value('store_products.store_product_type_id');

        //Category id
        $category_id = $request->category_id ? $request->category_id : $productTypeId;

        $stockItems = DB::table('store_stock_items')
            ->select('store_product_id', DB::raw('SUM(quantity) as total_items'))
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy('store_product_id');

        $stockMovements = DB::table('store_stock_movements')
            ->select('store_product_id', DB::raw('SUM(change_quantity) as total_movements'))
            ->where('source_type', 'sale')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy('store_product_id');

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

        // dd($stockReport);
        return Inertia::render('store/reports/StockReport',[
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
