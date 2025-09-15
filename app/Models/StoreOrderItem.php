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

    public function storeStockItem()
    {
        return $this->belongsTo(StoreStockItem::class);
    }
    // One order item belongs to one stock
    public function storeStock()
    {
        return $this->belongsTo(StoreStock::class);
    }
}
