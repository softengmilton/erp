<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\StoreProductType;
use DB;

class ProductReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        // Date range
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->startOfDay() : now()->startOfMonth()->startOfDay();
        $endDate   = $request->endDate   ? Carbon::parse($request->endDate)->endOfDay() : now()->endOfMonth()->endOfDay();
        if ($request->startDate && $request->endDate) {
            $start = Carbon::parse($request->startDate);
            $end   = Carbon::parse($request->endDate);

            $dateLabel = $start->format('M j, Y') . ' – ' . $end->format('M j, Y');
        } elseif ($request->startDate) {
            $dateLabel = Carbon::parse($request->startDate)->format('M j, Y');
        } elseif ($request->endDate) {
            $dateLabel = Carbon::parse($request->endDate)->format('M j, Y');
        } else {
            $dateLabel = now()->format('F Y');
        }

        // Pick product type from today's sales
        $productTypeId = DB::table('store_order_items')
            ->join('store_products', 'store_order_items.store_product_id', '=', 'store_products.id')
            ->whereBetween('store_order_items.created_at', [$startDate, $endDate])
            ->value('store_products.store_product_type_id');

        //Category id
        $category_id = $request->category_id ? $request->category_id : $productTypeId;



        // Categories
        $allCategory = DB::table('store_product_types')
            ->select('store_product_types.id', 'store_product_types.name')->get();
        // dd($allCategory);

        $dailyReport = [];
        $total_product_sales = 0;
        $total_product_cost = 0;
        $total_profit = 0;
        $categoryTotals = [];
        $categoryProfits = [];


        if ($category_id) {
$ProductReports = DB::table('store_order_items')
    ->join('store_products', 'store_order_items.store_product_id', '=', 'store_products.id')
    ->join('store_stock_items as stock', function($join) {
        $join->on('store_order_items.store_stock_id', '=', 'stock.store_stock_id')
             ->on('store_order_items.store_product_id', '=', 'stock.store_product_id');
    })
    ->select(
        DB::raw('DATE(store_order_items.created_at) as order_date'),
        'store_products.name',
        'store_products.id',
        DB::raw('SUM(store_order_items.quantity) as product_quantity'),
        DB::raw('SUM(store_order_items.quantity * store_order_items.sale_price) as product_sales'),
        DB::raw('SUM(store_order_items.quantity * (stock.unit_cost + stock.shipping_cost_unit + stock.other_fees_unit)) as product_cost')
    )
    ->whereBetween('store_order_items.created_at', [$startDate, $endDate])
    ->where('store_products.store_product_type_id', $category_id)
    ->groupBy('order_date', 'store_products.name', 'store_products.id')
    ->orderBy('order_date')
    ->paginate(30);


                // dd($ProductReports);

       
            foreach ($ProductReports as $item) {

                // Initialize dailyReport for the date if not exists
                if (!isset($dailyReport[$item->order_date])) {
                    $dailyReport[$item->order_date] = [
                        'categories' => [],
                        'total_sales' => 0,
                        'total_cost' => 0,
                    ];
                }


                $dailyReport[$item->order_date]['categories'][$item->name] = [
                    'order_date' => $item->order_date,
                    'product_quantity' => $item->product_quantity,
                    // 'product_sale_price' => $item->product_sale_price,
                    'product_sales' => $item->product_sales,
                    'product_cost' => $item->product_cost,
                ];

                // Update daily sales and cost
                $dailyReport[$item->order_date]['total_sales'] += $item->product_sales;
                $dailyReport[$item->order_date]['total_cost'] += $item->product_cost;

                // Total product sales and cost
                $total_product_sales += $item->product_sales;
                $total_product_cost += $item->product_cost;

                // Track category totals and profits
                $categoryTotals[$item->name] = ($categoryTotals[$item->name] ?? 0) + $item->product_sales;
                $categoryProfits[$item->name] = ($categoryProfits[$item->name] ?? 0) + ($item->product_sales - $item->product_cost);

            }

            // Total Profit
            $total_profit = $total_product_sales - $total_product_cost;

            // dd($dailyReport);

            // best-selling category
            $bestSellingCategory = !empty($categoryTotals) ? [
                'name' => collect($categoryTotals)->sortDesc()->keys()->first(),
                'sales' => collect($categoryTotals)->sortDesc()->first()
            ] : null;


            // best profitable category
            $bestProfitableCategory = !empty($categoryProfits) ? [
                'name' => collect($categoryProfits)->sortDesc()->keys()->first(),
                'profit' => collect($categoryProfits)->sortDesc()->first()
            ] : null;
        }



        return Inertia::render('store/reports/ProductReport', [
            'dateLabel' => $dateLabel,
            'allCategory' => $allCategory,
            'dailyReport' => $dailyReport,
            'total_product_sales' => $total_product_sales ?? 0,
            'total_profit' => $total_profit ?? 0,
            'bestSellingCategory' => $bestSellingCategory ?? 0,
            'bestProfitableCategory' => $bestProfitableCategory ?? 0,
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
