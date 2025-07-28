<?php

namespace App\Models;

class StoreStock extends Model
{
    /*----------------------------------------
     * Relationships
     ----------------------------------------*/
    public function storeStockItems()
    {
        return $this->hasMany(StoreStockItem::class);
    }
    public function storeStockMovements()
    {
        return $this->hasMany(StoreStockMovement::class);
    }
}
