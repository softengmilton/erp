<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreOrderItem;
use App\Models\StoreProduct;
use App\Models\StoreProductType;
use App\Models\StoreStock;
use App\Models\StoreStockItem;
use App\Models\StoreStockMovement;
use App\Services\Store\StockAdjustmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use DB;
use Carbon\Carbon;

class StockReportController extends Controller
{
    /**
     * Generate full stock report grouped by Month → Product.
     */
    public function index(Request $request)
    {
        // --- Default category (first category from order items)
        $defaultCategoryId = StoreOrderItem::with('storeProduct')
            ->orderBy('id')
            ->first()?->storeProduct?->store_product_type_id ?? null;

        // --- Get filters from request
        $selectedMonth = $request->input('month');         
        $selectedCategoryId = $request->input('category_id') ?? $defaultCategoryId;

        // --- Query months
        $query = StoreOrderItem::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month");

        if ($selectedMonth) {
            $query->whereMonth('created_at', $selectedMonth);
        }

        $months = $query->groupBy('month')->orderBy('month')->pluck('month');

        $report = [];

        foreach ($months as $month) {
            $monthName = date("F", strtotime($month . "-01"));

            // --- Products for this month
            $productQuery = StoreOrderItem::select('store_product_id')
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);

            if ($selectedCategoryId) {
                // Filter order items by product category
                $productQuery->whereHas('storeProduct', function ($q) use ($selectedCategoryId) {
                    $q->where('store_product_type_id', $selectedCategoryId);
                });
            }

            $products = $productQuery->groupBy('store_product_id')->pluck('store_product_id');
            $report[$monthName] = [];

            // --- Month boundaries
            $monthStart = \Carbon\Carbon::parse($month . '-01')->startOfMonth()->startOfDay();
            $monthEnd   = \Carbon\Carbon::parse($month . '-01')->endOfMonth()->endOfDay();

            foreach ($products as $productId) {
                $product = StoreProduct::find($productId);
                $lines = [];

                // --- Orders for this product
                $orders = StoreOrderItem::with('storeStock')
                    ->where('store_product_id', $productId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->orderBy('created_at')
                    ->get();

                // --- Group orders by invoice (store_stock_id)
                $ordersByStock = $orders->groupBy('store_stock_id');

                foreach ($ordersByStock as $stockId => $invoiceOrders) {
                    $stockNumber = $invoiceOrders->first()->storeStock->invoice_number ?? 'INV-XXXX';

                    // --- Load purchased qty for this stock
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

                    // --- Group sales by sale_price within this invoice
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

                    // --- Invoice header row
                    $lines[] = [
                        'stock_number' => $stockNumber,
                        'sale' => '',
                        'stock' => '',
                    ];

                    // --- Print grouped sales
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
                } // end foreach invoice

                // --- Adjustment rows (if any)
                $adjustment = StoreStockMovement::where('store_product_id', $productId)
                    ->where('source_type', 'adjustment')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->latest()
                    ->first();

                if ($adjustment) {
                    $lines[] = [
                        'stock_number' => 'Adjusted',
                        'sale' => '0',
                        'stock' => "adjusted Product={$this->getAvailableStock($product)}",
                    ];
                }

                // --- Summary row
                $totalSale = $orders->sum(fn($o) => $o->sale_price * $o->quantity);
                $totalQty = $orders->sum('quantity');
                $availableStock = $this->getAvailableStock($product);

                $lines[] = [
                    'stock_number' => 'Total Stock = ' . $orders->count(),
                    'sale' => "sale=\${$totalSale} | quantity={$totalQty}",
                    'stock' => "available stock={$availableStock}",
                ];

                $report[$monthName][$product->name] = $lines;
            }
        }

        // --- Categories for filter dropdown
        $allCategory = StoreProductType::select('id', 'name')->get();

        return Inertia::render('store/reports/StockReport', [
            'report' => $report,
            'allCategory' => $allCategory,
            'filters' => [
                'month' => $selectedMonth,
                'category_id' => $selectedCategoryId,
            ],
        ]);
    }



    private function getAvailableStock(StoreProduct $product): int
    {
        $purchased = StoreStockItem::where('store_product_id', $product->id)->sum('quantity');
        $moved = StoreStockMovement::where('store_product_id', $product->id)->sum('change_quantity');
        return $purchased - $moved;
    }

    private function getRemainingStock(int $stockId, int $productId): int
    {
        $item = StoreStockItem::where('store_stock_id', $stockId)
            ->where('store_product_id', $productId)
            ->first();

        if (!$item) return 0;

        $sold = StoreOrderItem::where('store_stock_id', $stockId)
            ->where('store_product_id', $productId)
            ->sum('quantity');

        return $item->quantity - $sold;
    }

    function triggerAdjustments()
    {
        $service = new StockAdjustmentService();

        $products = StoreProduct::all();
        $stocks = StoreStock::all();

        foreach ($stocks as $stock) {
            foreach ($products as $product) {
                $service->adjustStock($product, $stock);
            }
        }
    }

}
