<?php

namespace App\Services\Store;

use App\Models\StoreProduct;
use App\Models\StoreStock;
use App\Models\StoreStockItem;
use App\Models\StoreStockMovement;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    /**
     * Adjust product stock (manual reset).
     */
    public function adjustStock(StoreProduct $product, StoreStock $stock)
    {
        return DB::transaction(function () use ($product, $stock,) {
            // Calculate current stock
            $currentQuantity = $this->getAvailableStock($product, $stock);
            // Difference
            $changeQuantity = $currentQuantity;

            // Save movement
            StoreStockMovement::create([
                'store_stock_id' => $stock->id, // manual adjustment has no purchase reference
                'store_product_id' => $product->id,
                'change_quantity' => $changeQuantity,
                'source_type' => 'adjustment',
                'source_data' => json_encode([
                    'adjusted_at' => now(),
                ]),
            ]);


            return [
                'product_id' => $product->id,
                'old_quantity' => $currentQuantity,
                'difference' => $changeQuantity,
            ];
        });
    }

    /**
     * Calculate available stock for a product
     */
    public function getAvailableStock(StoreProduct $product, StoreStock $stock): int
    {
        // Sum from stock items (purchases)
        $purchased = StoreStockMovement::where('store_stock_id', $stock->id)
            ->where('store_product_id', $product->id)
            ->where('source_type', '=', 'purchase')
            ->sum('change_quantity');

        // Sum movements excluding adjustments and purchases
        $moved = StoreStockMovement::where('store_stock_id', $stock->id)
            ->where('store_product_id', $product->id)
            ->where('source_type', '!=', 'adjustment')
            ->where('source_type', '!=', 'purchase')
            ->sum('change_quantity');


        return $purchased - $moved;
    }
}
