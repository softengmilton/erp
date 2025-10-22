<?php

namespace App\Models;

class StoreStockMovement extends Model
{
    /*----------------------------------------
     * Relationships
     ----------------------------------------*/
    public function storeStock()
    {
        return $this->belongsTo(StoreStock::class);
    }

    public function storeProduct()
    {
        return $this->belongsTo(StoreProduct::class);
    }

    public function storeStockItem()
    {
        return $this->belongsTo(StoreStockItem::class, 'store_stock_id', 'store_stock_id')
            ->whereColumn('store_product_id', 'store_product_id');
    }
}
