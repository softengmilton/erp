<?php

namespace App\Http\Controllers\Store\Report;

use App\Http\Controllers\Controller;
use App\Models\StoreOrderItem;
use App\Models\StoreProductType;
use App\Models\StoreStock;
use App\Models\StoreStockItem;
use App\Services\Store\StockAdjustmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockReportController extends Controller
{
    /**
     * Generate stock report for multiple months grouped by Month → Product.
     */
    public function index(Request $request)
    {
        $service = new StockAdjustmentService;
        $service->adjustAllStockItems();
        $selectedMonth = $request->input('month'); // format: YYYY-MM
        $selectedCategoryId = $request->input('category_id');

        // Determine month start and end
        if ($selectedMonth) {
            $monthStart = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
            $monthEnd = Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();
        } else {
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();
        }

        // --- Get stocks created within the month
        $stocks = StoreStock::with('storeStockItems.storeProduct')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->get();

        $report = [];

        foreach ($stocks as $stock) {
            foreach ($stock->storeStockItems as $item) {
                $product = $item->storeProduct;

                // Filter by category
                if ($selectedCategoryId && $product->store_product_type_id != $selectedCategoryId) {
                    continue;
                }

                $initialStock = $item->quantity;

                // --- Sales for this stock item within the month
                $soldQty = StoreOrderItem::where('store_stock_id', $stock->id)
                    ->where('store_product_id', $product->id)

                    ->sum('quantity');

                // --- Get adjustments for this stock item
                $adjs = StoreStockItem::where('store_stock_id', $stock->id)
                    ->where('store_product_id', $product->id)
                    ->where('adjustment', true)
                    ->get();

                $adjustment = 0;

                foreach ($adjs as $adj) {
                    // Find how many units from this adjusted stock were sold
                    $soldFromAdjustment = StoreOrderItem::where('store_stock_id', $adj->store_stock_id)
                        ->where('store_product_id', $adj->store_product_id)
                        ->sum('quantity');

                    // Subtract sold quantity from adjustment
                    $netAdjustment = $adj->quantity - $soldFromAdjustment;

                    $adjustment += $netAdjustment;
                }

                // --- Available stock considering adjustments
                $availableStock = $initialStock - $soldQty;

                $availableStock = $initialStock - $soldQty;

                // --- Total sales amount
                $totalSale = StoreOrderItem::where('store_stock_id', $stock->id)
                    ->where('store_product_id', $product->id)
                    ->sum(\DB::raw('sale_price * quantity'));

                $report[$stock->invoice_number][$product->name] = [
                    'initial_stock' => $initialStock,
                    'sold_qty' => $soldQty,
                    'adjustment' => $adjustment,
                    'available_stock' => $availableStock,
                    'total_sale' => $totalSale,
                ];
            }
        }

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
}
