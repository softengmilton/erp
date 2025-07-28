<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreExpenseFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        $slug = Str::slug($name);
        $amount = $this->faker->numberBetween(100, 10000);

        return [
            'store_expense_type_id' => rand(1, 5),
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'amount' => $amount,
        ];
    }
}
