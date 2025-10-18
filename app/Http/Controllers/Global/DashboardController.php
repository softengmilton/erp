<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\StoreOrder;
use App\Models\StoreStockItem;
use App\Models\StoreExpense;
use App\Models\StoreProductType;
use App\Models\StoreOrderItem;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Date ranges
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        // SALES
        $todaySales = StoreOrder::whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('total_amount');

        $monthSales = StoreOrder::whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('total_amount');

        $totalDue = StoreOrder::where('due_amount', '>', 0)->sum('due_amount');

        // EXPENSES
        $expensesData = $this->getExpensesData($todayStart, $todayEnd, $monthStart, $monthEnd);

        // PRODUCT COUNT
        $productCount = StoreStockItem::where('quantity', '>', 0)
            ->distinct('store_product_id')
            ->count('store_product_id');

        // REVENUE
        $todayRevenue = $todaySales - ($expensesData['today'] ?? 0);
        $monthRevenue = $monthSales - ($expensesData['month'] ?? 0);

        // CHART DATA
        $monthlyChartData = $this->getMonthlyChartData();
        $salesByTypeData = $this->getSalesByTypeData($monthStart, $monthEnd);

        // FRONTEND DATA
        $widgets = [
            'sales' => [
                'today' => $todaySales,
                'month' => $monthSales
            ],
            'revenue' => [
                'today' => $todayRevenue,
                'month' => $monthRevenue
            ],
            'products' => $productCount,
            'due' => $totalDue,
            'sale_assets' => [
                'buy_price' => $this->availableAssetsBuyPrice(),
                'sale_price' => $this->availableAssetsSalePrice(),
            ],
        ];

        return Inertia::render('global/Dashboard', [
            'widgets' => $widgets,
            'monthlyData' => $monthlyChartData,
            'salesByType' => $salesByTypeData,
            'productTypes' => StoreProductType::all()
        ]);
    }

    /**
     * Get expenses data including operational and product costs
     */
    private function getExpensesData($todayStart, $todayEnd, $monthStart, $monthEnd)
    {
        // Operational expenses
        $todayOperational = StoreExpense::whereBetween('created_at', [$todayStart, $todayEnd])->sum('amount');
        $monthOperational = StoreExpense::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');

        // Product costs (COGS)
        $todayProductCosts = StoreOrderItem::whereHas('storeOrder', function ($q) use ($todayStart, $todayEnd) {
            $q->whereBetween('created_at', [$todayStart, $todayEnd]);
        })
            ->get()
            ->sum(fn($item) => $item->quantity * optional($item->stockItem)->unit_cost);

        $monthProductCosts = StoreOrderItem::whereHas('storeOrder', function ($q) use ($monthStart, $monthEnd) {
            $q->whereBetween('created_at', [$monthStart, $monthEnd]);
        })
            ->get()
            ->sum(fn($item) => $item->quantity * optional($item->stockItem)->unit_cost);

        return [
            'today' => $todayOperational + $todayProductCosts,
            'month' => $monthOperational + $monthProductCosts,
        ];
    }

    /**
     * Get monthly revenue and expenses for last 12 months
     */
    private function getMonthlyChartData()
    {
        $monthlyData = [
            'months' => [],
            'revenue' => [],
            'expenses' => []
        ];

        for ($i = 11; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();
            $monthName = $start->format('M');

            $sales = StoreOrder::whereBetween('created_at', [$start, $end])->sum('total_amount');
            $operational = StoreExpense::whereBetween('created_at', [$start, $end])->sum('amount');

            $productCosts = StoreOrderItem::whereHas('storeOrder', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
                ->get()
                ->sum(fn($item) => $item->quantity * optional($item->stockItem)->unit_cost);

            $totalExpenses = $operational + $productCosts;

            $monthlyData['months'][] = $monthName;
            $monthlyData['revenue'][] = $sales - $totalExpenses;
            $monthlyData['expenses'][] = $totalExpenses;
        }

        return $monthlyData;
    }

    /**
     * Get sales by product type
     */
    /**
     * Get sales by product type for the last 12 months
     */
    private function getSalesByTypeData()
    {
        $startWindow = now()->subMonths(11)->startOfMonth();
        $endWindow = now()->endOfMonth();

        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = now()->subMonths($i)->format('M');
        }

        $salesByType = [];

        $types = \App\Models\StoreProductType::all();

        foreach ($types as $type) {
            $productIds = \App\Models\StoreProduct::where('store_product_type_id', $type->id)
                ->pluck('id')
                ->toArray();

            $monthlySales = array_fill(0, 12, 0);

            $orderItems = \App\Models\StoreOrderItem::whereIn('store_product_id', $productIds)
                ->whereHas('storeOrder', function ($q) use ($startWindow, $endWindow) {
                    $q->whereBetween('created_at', [$startWindow, $endWindow]);
                })
                ->with('storeOrder:id,created_at')
                ->get();

            foreach ($orderItems as $item) {
                $orderDate = \Carbon\Carbon::parse($item->storeOrder->created_at);
                $monthIndex = $startWindow->diffInMonths($orderDate);
                if ($monthIndex >= 0 && $monthIndex < 12) {
                    $monthlySales[$monthIndex] += $item->sale_price * $item->quantity;
                }
            }

            $salesByType[] = [
                'name' => $type->name,
                'data' => $monthlySales
            ];
        }

        return [
            'months' => $months,
            'data' => $salesByType
        ];
    }

    /**
     * Calculate total available assets buy price
     */
    public function availableAssetsBuyPrice()
    {
        $totalBuyPrice = \App\Models\StoreProduct::with(['storeStockMovements.storeStockItem', 'storeStockMovements.storeStock'])
            ->get()
            ->flatMap(function ($product) {
                return $product->storeStockMovements->where('source_type', 'purchase')->map(function ($purchase) use ($product) {
                    $invoiceNumber = $purchase->storeStock->invoice_number ?? 'N/A';

                    // Calculate sold quantity for this invoice
                    $soldQty = $product->storeStockMovements
                        ->where('source_type', 'sale')
                        ->where('storeStock.invoice_number', $invoiceNumber)
                        ->sum('change_quantity');

                    $quantity = $purchase->change_quantity;
                    $sourceData = is_array($purchase->source_data) ? $purchase->source_data : json_decode($purchase->source_data ?? '{}', true);
                    $unitCost = $sourceData['unit_cost'] ?? 0;
                    $shipping = $sourceData['shipping'] ?? 0;
                    $fees = $sourceData['fees'] ?? 0;

                    $availableStock = max(0, $quantity - $soldQty);
                    $costPerProduct = $unitCost + $shipping + $fees;

                    return $availableStock * $costPerProduct;
                });
            })
            ->sum();

        return $totalBuyPrice;
    }

    /**
     * Calculate total available assets sale price
     */
    public function availableAssetsSalePrice()
    {
        $totalSalePrice = \App\Models\StoreProduct::with(['storeStockMovements.storeStockItem', 'storeStockMovements.storeStock'])
            ->get()
            ->flatMap(function ($product) {
                return $product->storeStockMovements->where('source_type', 'purchase')->map(function ($purchase) use ($product) {
                    $invoiceNumber = $purchase->storeStock->invoice_number ?? 'N/A';

                    // Calculate sold quantity for this invoice
                    $soldQty = $product->storeStockMovements
                        ->where('source_type', 'sale')
                        ->where('storeStock.invoice_number', $invoiceNumber)
                        ->sum('change_quantity');

                    $quantity = $purchase->change_quantity;
                    $availableStock = max(0, $quantity - $soldQty);

                    $salePrice = $purchase->storeStockItem->sale_price ?? 0;

                    return $availableStock * $salePrice;
                });
            })
            ->sum();

        return $totalSalePrice;
    }
}
