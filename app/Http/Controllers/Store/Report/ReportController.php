<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use DB;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display summary report.
     */
    public function index(Request $request)
    {

        // $today = now()->subDay()->toDateString();
        // $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        // Product types report - category column wise
        $categorySales = DB::table('store_order_items')
            ->join('store_products', 'store_order_items.store_product_id', '=', 'store_products.id')
            ->join('store_stock_items', function($join){
                $join->on ('store_products.id', '=', 'store_stock_items.store_product_id')
                    ->on ('store_order_items.store_stock_id', '=', 'store_stock_items.store_stock_id');
            })
            ->join('store_product_types', 'store_products.store_product_type_id', '=', 'store_product_types.id')
            ->join('store_orders', 'store_order_items.store_order_id', '=', 'store_orders.id')
            ->select(
                DB::raw('DATE(store_order_items.created_at) as order_date'),
                'store_product_types.id as category_id',
                'store_product_types.name as category_name',
                DB::raw('SUM(store_order_items.quantity * store_order_items.sale_price) as category_sales'),
                DB::raw('SUM( store_order_items.quantity * (store_stock_items.unit_cost + store_stock_items.shipping_cost_unit + store_stock_items.other_fees_unit) ) as category_unit_cost'),
              
            )
            ->whereBetween('store_order_items.created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('order_date','store_product_types.id', 'store_product_types.name')
            ->orderByDesc('order_date')
            ->get();


            // Payment Details
            $payment = DB::table('store_orders')
                ->select(
                    DB::raw('DATE(created_at) as order_date'),
                    DB::raw('SUM(CASE WHEN store_orders.payment_method = "cash" THEN store_orders.paid_amount ELSE 0 END) as total_cash_amount'),
                    DB::raw('SUM(CASE WHEN store_orders.payment_method = "bkash" THEN store_orders.paid_amount ELSE 0 END) as total_bkash_amount'),
                    DB::raw('SUM(CASE WHEN store_orders.payment_method = "Nagad" THEN store_orders.paid_amount ELSE 0 END) as total_nagad_amount'),
                    DB::raw('SUM(due_amount) as total_due_amount')
                )
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get();


            // Build categoryData with both sale & profit
        $dailyCategoryData = [];
        $dailyTotalSales = [];
        $dailyTotalCost = [];

        foreach ($categorySales as $item) {
            $date = $item->order_date;

            if (!isset($dailyCategoryData[$date])) {
                $dailyCategoryData[$date] = [];
                $dailyTotalSales[$date] = 0;
                $dailyTotalCost[$date] = 0;
            }

            $dailyCategoryData[$date][$item->category_name] = [
                'category_sales' => $item->category_sales,
                'category_unit_cost' => $item->category_unit_cost,
            ];

            $dailyTotalSales[$date] += $item->category_sales;
            $dailyTotalCost[$date] += $item->category_unit_cost;
        }


            // dd($categorySales);
            // dd($categorySales. $payment);
            // dd($categoryData);

            return Inertia::render('store/reports/Report', [
                'month' => now()->format('F Y'), // Example: August 2025
                'dailyCategoryData' => $dailyCategoryData,
                'dailyTotalSales' => $dailyTotalSales,
                'dailyTotalCost' => $dailyTotalCost,
                'payments' => $payment,
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
