<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\StoreProductType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class StoreProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->words(2, true)) . ' ' . substr($this->faker->uuid, 0, 8);
        $slug = Str::slug($name);
        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->sentence(),
            'barcode' => $this->faker->unique()->ean13(),
            'unit' => $this->faker->randomElement(['kg', 'pcs', 'ml', 'liter', 'g']),
            'low_stock_alert' => $this->faker->numberBetween(5, 20),
            'store_product_type_id' => StoreProductType::factory(),
        ];
    }
}
