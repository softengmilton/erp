<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreOrderItem;
use App\Models\StoreProduct;
use App\Models\StoreProductType;
use App\Models\StoreStock;
use App\Models\StoreStockItem;
use App\Models\StoreStockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class StockReportController extends Controller
{
    /**
     * Generate stock report for multiple months grouped by Month → Product.
     */
    public function index(Request $request)
    {
        // --- Default category
        $defaultCategoryId = StoreOrderItem::with('storeProduct')
            ->orderBy('id')
            ->first()?->storeProduct?->store_product_type_id ?? null;

        // --- Get filters
        $selectedMonth = $request->input('month');
        $selectedCategoryId = $request->input('category_id') ?? $defaultCategoryId;
        $monthsBack = $request->input('months_back', 1); // include how many months back, default 1

        // --- Get all distinct months from orders
        $allMonths = StoreOrderItem::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->pluck('month');

        if ($selectedMonth) {
            $current = Carbon::createFromFormat('m', $selectedMonth)->startOfMonth();
        } else {
            $current = now()->startOfMonth();
        }

        // --- Take current + previous months
        $targetMonths = collect();
        for ($i = $monthsBack; $i >= 0; $i--) {
            $targetMonths->push($current->copy()->subMonths($i)->format('Y-m'));
        }

        $report = [];

        foreach ($targetMonths as $month) {
            $monthName = date("F", strtotime($month . "-01"));

            // --- Product query
            $productQuery = StoreOrderItem::select('store_product_id')
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);

            if ($selectedCategoryId) {
                $productQuery->whereHas('storeProduct', function ($q) use ($selectedCategoryId) {
                    $q->where('store_product_type_id', $selectedCategoryId);
                });
            }

            $products = $productQuery->groupBy('store_product_id')->pluck('store_product_id');
            $report[$monthName] = [];

            // --- Month boundaries
            $monthStart = Carbon::parse($month . '-01')->startOfMonth()->startOfDay();
            $monthEnd   = Carbon::parse($month . '-01')->endOfMonth()->endOfDay();

            foreach ($products as $productId) {
                $product = StoreProduct::find($productId);
                $lines = [];

                // --- Orders for this product
                $orders = StoreOrderItem::with('storeStock')
                    ->where('store_product_id', $productId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->orderBy('created_at')
                    ->get();

                // --- Group by invoice
                $ordersByStock = $orders->groupBy('store_stock_id');

                foreach ($ordersByStock as $stockId => $invoiceOrders) {
                    $stockNumber = $invoiceOrders->first()->storeStock->invoice_number ?? 'INV-XXXX';

                    // --- Purchased qty
                    $stockItem = StoreStockItem::where('store_stock_id', $stockId)
                        ->where('store_product_id', $productId)
                        ->first();

                    $purchasedQty = $stockItem ? (int) $stockItem->quantity : 0;

                    // --- Sales before this month
                    $soldBefore = StoreOrderItem::where('store_stock_id', $stockId)
                        ->where('store_product_id', $productId)
                        ->where('created_at', '<', $monthStart)
                        ->sum('quantity');

                    $runningStock = max(0, $purchasedQty - $soldBefore);

                    // --- Group sales by sale_price
                    $groupedSales = $invoiceOrders
                        ->groupBy('sale_price')
                        ->map(function ($rows, $price) {
                            $qty = $rows->sum('quantity');
                            return [
                                'price' => $price,
                                'quantity' => $qty,
                                'total' => $price * $qty,
                            ];
                        });

                    $lines[] = [
                        'stock_number' => $stockNumber,
                        'sale' => '',
                        'stock' => '',
                    ];

                    foreach ($groupedSales as $group) {
                        $stockBefore = $runningStock;
                        $stockAfter = $stockBefore - $group['quantity'];
                        $runningStock = $stockAfter;

                        $lines[] = [
                            'stock_number' => '',
                            'sale' => "{$group['price']}x{$group['quantity']} = \${$group['total']}",
                            'stock' => "{$stockBefore}-{$group['quantity']}={$stockAfter}",
                        ];
                    }
                }

                // --- Adjustment rows
                $adjustment = StoreStockMovement::where('store_product_id', $productId)
                    ->where('source_type', 'adjustment')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->latest()
                    ->first();

                if ($adjustment) {
                    $lines[] = [
                        'stock_number' => 'Adjusted',
                        'sale' => '0',
                        'stock' => "adjusted Product={$this->getAdjustmentStock($product)}",
                    ];
                }

                // --- Summary
                $totalSale = $orders->sum(fn($o) => $o->sale_price * $o->quantity);
                $totalQty = $orders->sum('quantity');
                $availableStock = $this->getAvailableStock($product);

                $lines[] = [
                    'stock_number' => 'Total',
                    'sale' => "sale=\${$totalSale} | quantity={$totalQty}",
                    'stock' => "available stock={$availableStock}",
                ];

                $report[$monthName][$product->name] = $lines;
            }
        }

        // --- Categories for dropdown
        $allCategory = StoreProductType::select('id', 'name')->get();

        return Inertia::render('store/reports/StockReport', [
            'report' => $report,
            'allCategory' => $allCategory,
            'filters' => [
                'month' => $selectedMonth,
                'category_id' => $selectedCategoryId,
                'months_back' => $monthsBack,
            ],
        ]);
    }

    private function getAvailableStock(StoreProduct $product): int
    {
        $purchased = StoreStockItem::where('store_product_id', $product->id)->sum('quantity');
        $moved = StoreStockMovement::where('store_product_id', $product->id)
            ->whereNotIn('source_type', ['purchase', 'adjustment'])
            ->sum('change_quantity');
        return $purchased - $moved;
    }

    private function getAdjustmentStock(StoreProduct $product)
    {
        $moved = StoreStockMovement::where('store_product_id', $product->id)
            ->where('source_type', 'adjustment')
            ->sum('change_quantity');

        return $moved;
    }
}
