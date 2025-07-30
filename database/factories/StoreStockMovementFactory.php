<?php

namespace Database\Factories;

use App\Models\StoreProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreStockMovement>
 */
class StoreStockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_product_id' => StoreProduct::factory(), // Assumes factory exists
            'change_quantity' => $this->faker->numberBetween(1, 10),
            'source_type' => $this->faker->randomElement(['purchase', 'sale', 'return']),
            'source_data' => json_encode(['info' => 'Generated for testing']),
        ];
    }
}
