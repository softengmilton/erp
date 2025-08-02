<?php

namespace App\Models;

class StoreOrderItem extends Model
{
    public function storeOrder()
    {
        return $this->belongsTo(StoreOrder::class);
    }

    public function storeProduct()
    {
        return $this->belongsTo(StoreProduct::class);
    }

    public function storeStock()
    {
        return $this->belongsTo(StoreStockItem::class);
    }
}
