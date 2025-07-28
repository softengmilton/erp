<?php

namespace App\Models;


class StoreExpense extends Model
{

    protected $with = ['primaryImage'];
    protected $appends = ['primary_image_url'];
    // protected $fillable = ['name', 'description', 'amount', 'attachment', 'slug', 'store_expense_type_id',];

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

     public function getPrimaryImageUrlAttribute()
    {
        return $this->primaryImage?->url ?? null;
    }
}
