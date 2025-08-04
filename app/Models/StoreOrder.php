<?php

namespace App\Models;

class StoreOrder extends Model
{
    public function storeOrderItems()
    {
        return $this->hasMany(StoreOrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
