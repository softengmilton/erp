<?php

namespace App\Models;

class StoreStock extends Model
{
    protected $with = ['primaryImage'];
    protected $appends = ['primary_image_url'];
    /*----------------------------------------
     * Relationships
     ----------------------------------------*/
    public function primaryImage()
    {
        return $this->morphOne(Media::class, 'media');
    }

    public function images()
    {
        return $this->morphMany(Media::class, 'media');
    }
    public function storeStockItems()
    {
        return $this->hasMany(StoreStockItem::class);
    }
    public function storeStockMovements()
    {
        return $this->hasMany(StoreStockMovement::class);
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
