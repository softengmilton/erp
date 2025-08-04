<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreOrderItem>
 */
class StoreOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_order_id' => $this->faker->numberBetween(1, 100),
            'store_product_id' => $this->faker->numberBetween(1, 50),
            'store_stock_id' => $this->faker->numberBetween(1, 100),
            'quantity' => $this->faker->numberBetween(1, 5),
            'sale_price' => $this->faker->randomFloat(2, 10, 200),
        ];
    }
}
