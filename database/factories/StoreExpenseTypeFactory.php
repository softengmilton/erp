<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreExpenseType>
 */
class StoreExpenseTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\StoreExpenseType::class;

    public function definition(): array
    {
        $name = ucfirst($this->faker->words(2, true)) . ' ' . substr($this->faker->uuid, 0, 8);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
        ];
    }
}
