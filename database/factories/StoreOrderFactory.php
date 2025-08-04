<?php

namespace Database\Factories;

use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\StoreStockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\StoreOrder>
 */
class StoreOrderFactory extends Factory
{
    protected $model = StoreOrder::class;

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

    public function configure()
    {
        return $this->afterCreating(function (StoreOrder $order) {
            $faker = \Faker\Factory::create();

            // Create and save StoreOrderItems
            $items = StoreOrderItem::factory()
                ->count($faker->numberBetween(1, 5))
                ->make([
                    'store_order_id' => $order->id,
                ]);

            $order->storeOrderItems()->saveMany($items);

            // Create and save StoreStockMovements for each item
            foreach ($items as $item) {
                $movements = StoreStockMovement::factory()
                    ->count($faker->numberBetween(1, 3))
                    ->make([
                        'store_stock_id' => $item->store_stock_id,
                        'store_product_id' => $item->store_product_id,
                        'change_quantity' => $item->quantity,
                        'source_data' => $order->id,
                        'source_type' => 'sale',
                    ]);

                foreach ($movements as $movement) {
                    $movement->save();
                }
            }
        });
    }
}
