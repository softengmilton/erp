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
        // Get filters from request (default = null)
        $selectedMonth = $request->input('month');       // 1–12 (January=1)
        $selectedCategory = $request->input('category_id'); // product id

        $query = StoreOrderItem::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month");

        // Apply filters
        if ($selectedMonth) {
            $query->whereMonth('created_at', $selectedMonth);
        }
        if ($selectedCategory) {
            $query->where('store_product_id', $selectedCategory);
        }

        $months = $query->groupBy('month')->orderBy('month')->pluck('month');

        $report = [];

        foreach ($months as $month) {
            $monthName = date("F", strtotime($month . "-01"));

            $productQuery = StoreOrderItem::with('product')
                ->select('store_product_id')
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month]);

            if ($selectedCategory) {
                $productQuery->where('store_product_id', $selectedCategory);
            }

            $products = $productQuery->groupBy('store_product_id')->pluck('store_product_id');
            $report[$monthName] = [];

            foreach ($products as $productId) {
                $product = StoreProduct::find($productId);
                $lines = [];

                $orders = StoreOrderItem::where('store_product_id', $productId)
                    ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
                    ->get();

                foreach ($orders as $orderItem) {
                    $stockNumber = $orderItem->storeStock->invoice_number ?? 'INV-XXXX';
                    $saleTotal = $orderItem->sale_price * $orderItem->quantity;
                    $stockBefore = $orderItem->quantity + $this->getRemainingStock($orderItem->store_stock_id, $productId);
                    $stockAfter = $stockBefore - $orderItem->quantity;

                    $lines[] = [
                        'stock_number' => $stockNumber,
                        'sale' => "{$orderItem->sale_price}x{$orderItem->quantity} = \${$saleTotal}",
                        'stock' => "{$stockBefore}-{$orderItem->quantity}={$stockAfter}",
                    ];
                }

                // Adjustment rows
                $adjustment = StoreStockMovement::where('store_product_id', $productId)
                    ->where('source_type', 'adjustment')
                    ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
                    ->latest()
                    ->first();

                if ($adjustment) {
                    $lines[] = [
                        'stock_number' => 'Adjusted',
                        'sale' => '0',
                        'stock' => "adjusted Product={$this->getAvailableStock($product)}",
                    ];
                }

                // Summary row
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

        // Categories for filter dropdown
        $allCategory = StoreProductType::select('id', 'name')->get();

        return Inertia::render('store/reports/StockReport', [
            'report' => $report,
            'allCategory' => $allCategory,
            'filters' => [
                'month' => $selectedMonth,
                'category_id' => $selectedCategory,
            ],
        ]);
    }




    private function getAvailableStock(StoreProduct $product): int
    {
        $purchased = StoreStockItem::where('store_product_id', $product->id)->sum('quantity');
        $moved = StoreStockMovement::where('store_product_id', $product->id)->sum('change_quantity');
        return $purchased + $moved;
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
