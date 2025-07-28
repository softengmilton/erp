<?php

namespace App\Models;

class StoreExpenseType extends Model
{

      public function storeExpenses()
      {
            // 'store_expense_type_id' is the foreign key in the store_expenses table referencing this model's id
            return $this->hasMany(StoreExpense::class, 'store_expense_type_id');
      }
}
