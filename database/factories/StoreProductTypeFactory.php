<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreProductType>
 */
class StoreProductTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->words(2, true)) . ' ' . substr($this->faker->uuid, 0, 8);
        return [
            'name' => $name,
            'slug' => str()->slug($name),
            'description' => $this->faker->sentence(),
        ];
    }
}
