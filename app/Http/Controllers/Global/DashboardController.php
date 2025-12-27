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

        $yearStart = Carbon::now()->startOfYear();
        $yearEnd = Carbon::now()->endOfYear();

        // SALES
        $todaySales = StoreOrder::whereBetween('created_at', [$todayStart, $todayEnd])->sum('total_amount');
        $monthSales = StoreOrder::whereBetween('created_at', [$monthStart, $monthEnd])->sum('total_amount');
        $yearSales = StoreOrder::whereBetween('created_at', [$yearStart, $yearEnd])->sum('total_amount');
        $allSales = StoreOrder::sum('total_amount');

        $totalDue = StoreOrder::where('due_amount', '>', 0)->sum('due_amount');

        // EXPENSES
        $expensesData = $this->getExpensesData($todayStart, $todayEnd, $monthStart, $monthEnd, $yearStart, $yearEnd);

        // REVENUE
        $todayRevenue = $this->calculateRevenue($todayStart, $todayEnd);
        $monthRevenue = $this->calculateRevenue($monthStart, $monthEnd);
        $yearRevenue = $this->calculateRevenue($yearStart, $yearEnd);
        $allRevenue = $this->calculateRevenue(); // no dates = all-time


        // PRODUCT COUNT
        $productCount = StoreStockItem::where('quantity', '>', 0)
            ->distinct('store_product_id')
            ->count('store_product_id');

        // CHART DATA
        $monthlyChartData = $this->getMonthlyChartData();
        $salesByTypeData = $this->getSalesByTypeData();

        // FRONTEND DATA
        $widgets = [
            'sales' => [
                'today' => $todaySales,
                'month' => $monthSales,
                'year'  => $yearSales,
                'all'   => $allSales,
            ],
            'revenue' => [
                'today' => $todayRevenue,
                'month' => $monthRevenue,
                'year'  => $yearRevenue,
                'all'   => $allRevenue,
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
            'productTypes' => StoreProductType::all(),
        ]);
    }

    /**
     * Get expenses data including operational and product costs
     */
    private function getExpensesData($todayStart, $todayEnd, $monthStart, $monthEnd, $yearStart, $yearEnd)
    {
        // Operational expenses
        $todayOperational = StoreExpense::whereBetween('created_at', [$todayStart, $todayEnd])->sum('amount');
        $monthOperational = StoreExpense::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
        $yearOperational  = StoreExpense::whereBetween('created_at', [$yearStart, $yearEnd])->sum('amount');

        // Product costs (COGS)
        $todayProductCosts = $this->getProductCosts($todayStart, $todayEnd);
        $monthProductCosts = $this->getProductCosts($monthStart, $monthEnd);
        $yearProductCosts  = $this->getProductCosts($yearStart, $yearEnd);

        return [
            'today' => $todayOperational + $todayProductCosts,
            'month' => $monthOperational + $monthProductCosts,
            'year'  => $yearOperational + $yearProductCosts,
        ];
    }

    /**
     * Calculate product costs (COGS) for a given period
     */
    private function getProductCosts($start, $end)
    {
        return StoreOrderItem::whereHas('storeOrder', function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->get()->sum(function ($item) {
            $stockItem = StoreStockItem::find($item->store_stock_item_id);
            if (!$stockItem) return 0;

            $unitCost = ($stockItem->unit_cost ?? 0)
                + ($stockItem->shipping_cost_unit ?? 0)
                + ($stockItem->other_fees_unit ?? 0);

            return $item->quantity * $unitCost;
        });
    }

    /**
     * Get all-time product costs (COGS for all orders)
     */
    private function getAllTimeProductCosts()
    {
        return StoreOrderItem::all()->sum(function ($item) {
            $stockItem = StoreStockItem::find($item->store_stock_item_id);
            if (!$stockItem) return 0;

            $unitCost = ($stockItem->unit_cost ?? 0)
                + ($stockItem->shipping_cost_unit ?? 0)
                + ($stockItem->other_fees_unit ?? 0);

            return $item->quantity * $unitCost;
        });
    }

    /**
     * Calculate all-time revenue
     */
    /**
     * Calculate revenue for a given period
     */
    private function calculateRevenue($start = null, $end = null)
    {
        // Orders in range
        $ordersQuery = StoreOrder::query();
        if ($start && $end) {
            $ordersQuery->whereBetween('created_at', [$start, $end]);
        }
        $orders = $ordersQuery->get();

        $totalSales = $orders->sum('total_amount');

        $totalCosts = 0;

        foreach ($orders as $order) {
            $orderItems = $order->storeOrderItems; // assuming relation StoreOrder->items()
            foreach ($orderItems as $item) {
                $stockItem = StoreStockItem::find($item->store_stock_item_id);
                if (!$stockItem) continue;

                $unitCost = ($stockItem->unit_cost ?? 0)
                    + ($stockItem->shipping_cost_unit ?? 0)
                    + ($stockItem->other_fees_unit ?? 0);

                $totalCosts += $item->quantity * $unitCost;
            }
        }

        // Operational expenses
        $expensesQuery = StoreExpense::query();
        if ($start && $end) {
            $expensesQuery->whereBetween('created_at', [$start, $end]);
        }
        $operationalExpenses = $expensesQuery->sum('amount');

        return $totalSales - ($totalCosts + $operationalExpenses);
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
        $types = StoreProductType::all();

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
                $orderDate = Carbon::parse($item->storeOrder->created_at);
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
                return $product->storeStockMovements
                    ->where('source_type', 'purchase')
                    ->map(function ($purchase) use ($product) {
                        $invoiceNumber = $purchase->storeStock->invoice_number ?? 'N/A';

                        $soldQty = $product->storeStockMovements
                            ->where('source_type', 'sale')
                            ->where('storeStock.invoice_number', $invoiceNumber)
                            ->sum('change_quantity');

                        $quantity = $purchase->change_quantity;
                        $sourceData = is_array($purchase->source_data)
                            ? $purchase->source_data
                            : json_decode($purchase->source_data ?? '{}', true);

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
        $totalSalePrice = collect();

        $products = \App\Models\StoreProduct::with(['storeStockMovements.storeStock'])->get();

        foreach ($products as $product) {
            $purchaseMovements = $product->storeStockMovements
                ->where('source_type', 'purchase');

            $soldPerInvoice = $product->storeStockMovements
                ->where('source_type', 'sale')
                ->groupBy(fn($item) => $item->storeStock->invoice_number ?? 'N/A')
                ->map(fn($group) => $group->sum('change_quantity'));

            foreach ($purchaseMovements as $purchase) {
                $invoiceNumber = $purchase->storeStock->invoice_number ?? 'N/A';
                $soldQty = $soldPerInvoice[$invoiceNumber] ?? 0;

                $quantity = $purchase->change_quantity;
                $sourceData = is_array($purchase->source_data)
                    ? $purchase->source_data
                    : json_decode($purchase->source_data ?? '{}', true);

                $unitCost = $sourceData['unit_cost'] ?? 0;
                $shipping = $sourceData['shipping'] ?? 0;
                $fees = $sourceData['fees'] ?? 0;

                $costPerProduct = $unitCost + $shipping + $fees;

                $stockItem = \App\Models\StoreStockItem::where('store_product_id', $product->id)
                    ->where('store_stock_id', $purchase->store_stock_id)
                    ->latest('id')
                    ->first();

                $salePrice = $stockItem->sale_price ?? 0;
                $remainingStock = max(0, $quantity - $soldQty);

                $totalSalePrice->push($remainingStock * $salePrice);
            }
        }

        return $totalSalePrice->sum();
    }
}
