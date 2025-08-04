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
        return $this->belongsTo(StoreStockItem::class);
    }
}
