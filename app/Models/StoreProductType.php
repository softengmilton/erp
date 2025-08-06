<?php

namespace App\Models;

class StoreProductType extends Model
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

    public function storeProducts()
    {
        return $this->hasMany(StoreProduct::class);
    }


    /*----------------------------------------
    * Accessors
    ----------------------------------------*/
    public function getPrimaryImageUrlAttribute(): string
    {

        $imageUrl = asset('assets/default/default-product-type.png');

        if ($this->primaryImage()->exists()) {
            $imageUrl = $this->relations['primaryImage']->url;
        }

        return $imageUrl;
    }
}
