<?php

namespace App\Models;

class StoreProductType extends Model
{
    //
    public function storeProducts()
    {
        return $this->hasMany(StoreProduct::class);
    }
}
