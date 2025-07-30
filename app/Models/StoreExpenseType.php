<?php

namespace App\Models;

class StoreExpenseType extends Model
{
    public function storeExpenses()
    {
        return $this->hasMany(StoreExpense::class);
    }
}
