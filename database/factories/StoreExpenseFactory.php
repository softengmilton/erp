<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreExpenseFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true); // "Travel Expense"
        $slug = Str::slug($name);
        $amount = $this->faker->numberBetween(100, 10000);

        return [
            'store_expense_type_id' => rand(1, 5), // assumes types with IDs 1–5 exist
            'name' => $name,                      // ✅ add name field
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'amount' => $amount,
        ];
    }
}
