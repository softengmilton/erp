<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use DB;
use Carbon\Carbon;

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
    // Normalize date range
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->startOfDay() : null;
        $endDate   = $request->endDate   ? Carbon::parse($request->endDate)->endOfDay() : null;


        \Log::info('Problem fetching data', ['startDate' => $startDate, 'endDate' => $endDate]);

    // Single query: category sales + payments
    $reportData = DB::table('store_orders')
        ->join('store_order_items', 'store_orders.id', '=', 'store_order_items.store_order_id')
        ->join('store_products', 'store_order_items.store_product_id', '=', 'store_products.id')
        ->join('store_stock_items', function ($join) {
            $join->on('store_products.id', '=', 'store_stock_items.store_product_id')
                 ->on('store_order_items.store_stock_id', '=', 'store_stock_items.store_stock_id');
        })
        ->join('store_product_types', 'store_products.store_product_type_id', '=', 'store_product_types.id')
        ->select(
            DB::raw('DATE(store_order_items.created_at) as order_date'),
            'store_product_types.id as category_id',
            'store_product_types.name as category_name',

            // Category
            DB::raw('SUM(store_order_items.quantity * store_order_items.sale_price) as category_sales'),
            DB::raw('SUM(store_order_items.quantity * (store_stock_items.unit_cost + store_stock_items.shipping_cost_unit + store_stock_items.other_fees_unit)) as category_unit_cost'),

            // Payments
            DB::raw('SUM(CASE WHEN store_orders.payment_method = "cash" THEN store_orders.paid_amount ELSE 0 END) as total_cash_amount'),
            DB::raw('SUM(CASE WHEN store_orders.payment_method = "bkash" THEN store_orders.paid_amount ELSE 0 END) as total_bkash_amount'),
            DB::raw('SUM(CASE WHEN store_orders.payment_method = "Nagad" THEN store_orders.paid_amount ELSE 0 END) as total_nagad_amount'),
            DB::raw('SUM(store_orders.due_amount) as total_due_amount')
        )
        ->when($startDate && $endDate, function($query) use ($startDate, $endDate) {
            return $query->whereBetween(DB::raw('DATE(store_order_items.created_at)'), [$startDate, $endDate]);
        })
        ->when($startDate && !$endDate, function($query) use ($startDate) {
            return $query->whereDate(DB::raw('DATE(store_order_items.created_at)'), $startDate);
        })
        ->when(!$startDate && !$endDate, function($query) {
            $from = now()->startOfMonth();
            $to   = now()->endOfMonth();
            return $query->whereBetween(DB::raw('DATE(store_order_items.created_at)'), [$from, $to]);
        })
        ->groupBy('order_date', 'store_product_types.id', 'store_product_types.name')
        ->orderByDesc('order_date')
        ->get();

    // Re-shape into nested daily data
    $dailyReport = [];

    foreach ($reportData as $row) {
        $date = $row->order_date;

        if (!isset($dailyReport[$date])) {
            $dailyReport[$date] = [
                'categories' => [],
                'total_sales' => 0,
                'total_cost' => 0,
                'payments' => [
                    'cash' => 0,
                    'bkash' => 0,
                    'nagad' => 0,
                    'due' => 0,
                ],
            ];
        }

        // Category breakdown
        $dailyReport[$date]['categories'][$row->category_name] = [
            'category_sales' => $row->category_sales,
            'category_unit_cost' => $row->category_unit_cost,
        ];

        $dailyReport[$date]['total_sales'] += $row->category_sales;
        $dailyReport[$date]['total_cost'] += $row->category_unit_cost;

        // Payments (same for each category row → sum only once per date)
        $dailyReport[$date]['payments']['cash'] = $row->total_cash_amount;
        $dailyReport[$date]['payments']['bkash'] = $row->total_bkash_amount;
        $dailyReport[$date]['payments']['nagad'] = $row->total_nagad_amount;
        $dailyReport[$date]['payments']['due'] = $row->total_due_amount;
    }

    // dd($dailyReport);
    return Inertia::render('store/reports/Report', [
        'month' => now()->format('F Y'),
        'dailyReport' => $dailyReport,
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //         $startOfMonth = now()->startOfMonth()->toDateString();
        // $endOfMonth = now()->endOfMonth()->toDateString();

        // // Product types report - category column wise
        // $categorySales = DB::table('store_order_items')
        //     ->join('store_products', 'store_order_items.store_product_id', '=', 'store_products.id')
        //     ->join('store_stock_items', function($join){
        //         $join->on ('store_products.id', '=', 'store_stock_items.store_product_id')
        //             ->on ('store_order_items.store_stock_id', '=', 'store_stock_items.store_stock_id');
        //     })
        //     ->join('store_product_types', 'store_products.store_product_type_id', '=', 'store_product_types.id')
        //     ->join('store_orders', 'store_order_items.store_order_id', '=', 'store_orders.id')
        //     ->select(
        //         DB::raw('DATE(store_order_items.created_at) as order_date'),
        //         'store_product_types.id as category_id',
        //         'store_product_types.name as category_name',
        //         DB::raw('SUM(store_order_items.quantity * store_order_items.sale_price) as category_sales'),
        //         DB::raw('SUM( store_order_items.quantity * (store_stock_items.unit_cost + store_stock_items.shipping_cost_unit + store_stock_items.other_fees_unit) ) as category_unit_cost'),
        //     )
        //     ->whereBetween('store_order_items.created_at', [$startOfMonth, $endOfMonth])
        //     ->groupBy('order_date','store_product_types.id', 'store_product_types.name')
        //     ->orderByDesc('order_date')
        //     ->get();


        //     // Payment Details
        //     $payment = DB::table('store_orders')
        //         ->select(
        //             DB::raw('DATE(created_at) as order_date'),
        //             DB::raw('SUM(CASE WHEN store_orders.payment_method = "cash" THEN store_orders.paid_amount ELSE 0 END) as total_cash_amount'),
        //             DB::raw('SUM(CASE WHEN store_orders.payment_method = "bkash" THEN store_orders.paid_amount ELSE 0 END) as total_bkash_amount'),
        //             DB::raw('SUM(CASE WHEN store_orders.payment_method = "Nagad" THEN store_orders.paid_amount ELSE 0 END) as total_nagad_amount'),
        //             DB::raw('SUM(due_amount) as total_due_amount')
        //         )
        //         ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        //         ->groupBy(DB::raw('DATE(created_at)'))
        //         ->get();

        // // dd($categorySales);

        // // Build categoryData with both sale & profit
        // $dailyCategoryData = [];
        // $dailyTotalSales = [];
        // $dailyTotalCost = [];

        // foreach ($categorySales as $item) {
        //     // dd($item);
        //     $date = $item->order_date;

        //     if (!isset($dailyCategoryData[$date])) {
        //         $dailyCategoryData[$date] = [];
        //         $dailyTotalSales[$date] = 0;
        //         $dailyTotalCost[$date] = 0;
        //     }

        //     $dailyCategoryData[$date][$item->category_name] = [
        //         'category_sales' => $item->category_sales,
        //         'category_unit_cost' => $item->category_unit_cost,
        //     ];

        //     $dailyTotalSales[$date] += $item->category_sales;
        //     $dailyTotalCost[$date] += $item->category_unit_cost;
        // }

        // // dd($dailyCategoryData);

        //     return Inertia::render('store/reports/Report', [
        //         'month' => now()->format('F Y'), // Example: August 2025
        //         'dailyCategoryData' => $dailyCategoryData,
        //         'dailyTotalSales' => $dailyTotalSales,
        //         'dailyTotalCost' => $dailyTotalCost,
        //         'payments' => $payment,
        //     ]);
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
