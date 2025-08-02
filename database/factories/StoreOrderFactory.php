<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreOrder>
 */
class StoreOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $paymentStatus = $this->faker->randomElement(['paid', 'due']);
        $paidAmount = $paymentStatus === 'paid'
            ? $this->faker->randomFloat(2, 100, 1000)
            : $this->faker->randomFloat(2, 0, 500);

        $totalAmount = $this->faker->randomFloat(2, 100, 1000);
        $dueAmount = max(0, $totalAmount - $paidAmount);

        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numberBetween(1000, 9999),
            'customer_id' => $this->faker->optional()->numberBetween(1, 50),
            'customer_type' => $this->faker->randomElement(['walking', 'registered']),
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
            'payment_method' => $this->faker->randomElement(['cash', 'card', 'bank', 'bkash', 'Nagad']),
            'discount' => $this->faker->randomFloat(2, 0, 50),
            'adjustment' => $this->faker->randomFloat(2, -10, 10),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
