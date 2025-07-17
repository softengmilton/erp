<?php

namespace App\Models;

class StoreProduct extends Model
{
    public function storeProductType()
    {
        return $this->belongsTo(StoreProductType::class);
    }
}
