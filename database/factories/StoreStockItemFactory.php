<?php

namespace Database\Factories;

use App\Models\StoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreStockItem>
 */
class StoreStockItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 20);
        $unitCost = $this->faker->randomFloat(2, 5, 100);
        $totalCost = $quantity * $unitCost;

        return [
            'store_product_id' => StoreProduct::factory(), // Assumes factory exists
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'sale_price' => $unitCost * 1.5,
            'adjustment_data' => null,
            'note' => $this->faker->sentence,
        ];
    }
}
