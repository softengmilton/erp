<?php

namespace App\Models;


class StoreExpense extends Model
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

    public function storeExpenseType()
    {
        return $this->belongsTo(StoreExpenseType::class);
    }

    /*----------------------------------------
    * Accessors
    ----------------------------------------*/
    public function getPrimaryImageUrlAttribute()
    {
        return $this->primaryImage?->url ?? null;
    }
}
