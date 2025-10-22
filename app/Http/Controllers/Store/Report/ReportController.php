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
     *
     *
     */
    public function index(Request $request)
    {
        // Date range
        $startDate = $request->startDate
            ? Carbon::parse($request->startDate)->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $endDate = $request->endDate
            ? Carbon::parse($request->endDate)->endOfDay()
            : now()->endOfMonth()->endOfDay();

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

        \Log::info('Fetching report', ['startDate' => $startDate, 'endDate' => $endDate]);

        // Category-wise sales & cost
        $categoryData = DB::table('store_order_items')
            ->join('store_orders', 'store_orders.id', '=', 'store_order_items.store_order_id')
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
                DB::raw('SUM(store_order_items.quantity * store_order_items.sale_price) as category_sales'),
                DB::raw('SUM(store_order_items.quantity * (store_stock_items.unit_cost + store_stock_items.shipping_cost_unit + store_stock_items.other_fees_unit)) as category_unit_cost')
            )
            ->whereBetween(DB::raw('DATE(store_order_items.created_at)'), [$startDate, $endDate])
            ->groupBy('order_date', 'store_product_types.id', 'store_product_types.name')
            ->get();

        // Payments
        $paymentData = DB::table('store_orders')
            ->select(
                DB::raw('DATE(created_at) as order_date'),
                DB::raw('SUM(CASE WHEN payment_method = "cash" THEN paid_amount ELSE 0 END) as total_cash_amount'),
                DB::raw('SUM(CASE WHEN payment_method = "bkash" THEN paid_amount ELSE 0 END) as total_bkash_amount'),
                DB::raw('SUM(CASE WHEN payment_method = "Nagad" THEN paid_amount ELSE 0 END) as total_nagad_amount'),
                DB::raw('SUM(CASE WHEN payment_method = "card" THEN paid_amount ELSE 0 END) as total_card_amount'),
                DB::raw('SUM(CASE WHEN payment_method = "bank" THEN paid_amount ELSE 0 END) as total_bank_amount'),
                DB::raw('SUM(due_amount) as total_due_amount')
            )
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->groupBy('order_date')
            ->get()
            ->keyBy('order_date');

        // Expenses (exclude "asset")
        $expenses = DB::table('store_expenses')
            ->join('store_expense_types', 'store_expenses.store_expense_type_id', '=', 'store_expense_types.id')
            ->where('store_expense_types.name', '<>', 'Assets')
            ->select(
                DB::raw('DATE(store_expenses.created_at) as order_date'),
                DB::raw('SUM(store_expenses.amount) as expense_amount')
            )
            ->groupBy('order_date')
            ->get()
            ->keyBy('order_date'); // keyBy date for easy lookup

        // Daily report & totals
        $dailyReport = [];
        $categoryTotals = [];
        $categoryProfits = [];
        $allCategorySales = 0;
        $allCategoryProfit = 0;

        foreach ($categoryData as $row) {
            $date = $row->order_date;

            if (!isset($dailyReport[$date])) {
                $dailyReport[$date] = [
                    'categories' => [],
                    'total_sales' => 0,
                    'total_cost' => 0,
                    'best_category' => null,
                    'payments' => [
                        'cash' => 0,
                        'bkash' => 0,
                        'nagad' => 0,
                        'card' => 0,
                        'bank' => 0,
                        'due' => 0,
                    ],
                    'total_expense' => 0, // new
                ];
            }

            // Category profit
            $profit = $row->category_sales - $row->category_unit_cost;

            $dailyReport[$date]['categories'][$row->category_name] = [
                'category_sales' => $row->category_sales,
                'category_unit_cost' => $row->category_unit_cost,
                'category_profit' => $profit,
            ];

            $dailyReport[$date]['total_sales'] += $row->category_sales;
            $dailyReport[$date]['total_cost']  += $row->category_unit_cost;

            // Daily best category (by sales)
            if (
                !$dailyReport[$date]['best_category'] ||
                $row->category_sales > $dailyReport[$date]['categories'][$dailyReport[$date]['best_category']]['category_sales']
            ) {
                $dailyReport[$date]['best_category'] = $row->category_name;
            }

            // Track totals
            $categoryTotals[$row->category_name] = ($categoryTotals[$row->category_name] ?? 0) + $row->category_sales;
            $categoryProfits[$row->category_name] = ($categoryProfits[$row->category_name] ?? 0) + $profit;

            $allCategorySales += $row->category_sales;
            $allCategoryProfit += $profit;
        }

        // Inject payments
        foreach ($paymentData as $date => $row) {
            if (!isset($dailyReport[$date])) {
                $dailyReport[$date] = [
                    'categories' => [],
                    'total_sales' => 0,
                    'total_cost' => 0,
                    'best_category' => null,
                    'payments' => [
                        'cash' => 0,
                        'bkash' => 0,
                        'nagad' => 0,
                        'card' => 0,
                        'bank' => 0,
                        'due' => 0,
                    ],
                    'total_expense' => 0,
                ];
            }

            $dailyReport[$date]['payments'] = [
                'cash' => $row->total_cash_amount,
                'bkash' => $row->total_bkash_amount,
                'nagad' => $row->total_nagad_amount,
                'card' => $row->total_card_amount,
                'bank' => $row->total_bank_amount,
                'due'  => $row->total_due_amount,
            ];
        }

        // Inject expenses
        foreach ($expenses as $date => $row) {
            if (!isset($dailyReport[$date])) {
                $dailyReport[$date] = [
                    'categories' => [],
                    'total_sales' => 0,
                    'total_cost' => 0,
                    'best_category' => null,
                    'payments' => [
                        'cash' => 0,
                        'bkash' => 0,
                        'nagad' => 0,
                        'card' => 0,
                        'bank' => 0,
                        'due' => 0,
                    ],
                    'total_expense' => 0,
                ];
            }

            $dailyReport[$date]['total_expense'] = $row->expense_amount;
        }

        // Best-selling category
        $bestSellingCategory = !empty($categoryTotals) ? [
            'name' => collect($categoryTotals)->sortDesc()->keys()->first(),
            'sales' => collect($categoryTotals)->sortDesc()->first()
        ] : null;

        // Best profitable category
        $bestProfitableCategory = !empty($categoryProfits) ? [
            'name' => collect($categoryProfits)->sortDesc()->keys()->first(),
            'profit' => collect($categoryProfits)->sortDesc()->first()
        ] : null;


        // dd($dailyReport);
        return Inertia::render('store/reports/Report', [
            'dateLabel' => $dateLabel,
            'dailyReport' => $dailyReport,
            'bestSellingCategory' => $bestSellingCategory,
            'bestProfitableCategory' => $bestProfitableCategory,
            'allCategorySales' => $allCategorySales,
            'allCategoryProfit' => $allCategoryProfit,
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
