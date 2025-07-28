<?php

namespace Database\Factories;

use App\Models\StoreStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreStock>
 */
class StoreStockFactory extends Factory
{
    protected $model = StoreStock::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'invoice_number' => $this->faker->unique()->bothify('INV-#####'),
            'supplier_name' => $this->faker->company,
            'total_cost' => 0, // Will be updated after creating items
            'note' => $this->faker->sentence,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (StoreStock $stock) {
            $items = \App\Models\StoreStockItem::factory()->count(3)->create([
                'store_stock_id' => $stock->id,
            ]);

            // Sum up total cost from items
            $totalCost = $items->sum('total_cost');
            $stock->update(['total_cost' => $totalCost]);

            // Optional: create stock movements
            foreach ($items as $item) {
                \App\Models\StoreStockMovement::factory()->create([
                    'store_stock_id' => $stock->id,
                    'store_product_id' => $item->store_product_id,
                    'change_quantity' => $item->quantity,
                    'source_type' => 'purchase',
                    'source_data' => json_encode(['invoice' => $stock->invoice_number]),
                ]);
            }
        });
    }
}
