<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class StoreProduct extends Model
{
    protected $with = ['primaryImage'];
    protected $appends = ['primary_image_url'];
    /*----------------------------------------
     * Relationships
     ----------------------------------------*/
    public function primaryImage(): MorphOne
    {
        return $this->morphOne(Media::class, 'media');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'media');
    }

    public function storeProductType()
    {
        return $this->belongsTo(StoreProductType::class);
    }

    public function storeStockItems()
    {
        return $this->hasMany(StoreStockItem::class);
    }
    public function storeStockMovements()
    {
        return $this->hasMany(StoreStockMovement::class, 'store_product_id', 'id');
    }

    public function storeOrderItems()
    {
        return $this->hasMany(StoreOrderItem::class, 'store_product_id', 'id');
    }



    /*----------------------------------------
    * Accessors
    ----------------------------------------*/
    public function getPrimaryImageUrlAttribute(): string
    {

        $imageUrl = asset('assets/default/default_product.png');

        if ($this->primaryImage()->exists()) {
            $imageUrl = $this->relations['primaryImage']->url;
        }

        return $imageUrl;
    }
}
