<?php

namespace Database\Seeders;

use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 100 store orders
        $orders = StoreOrder::factory()
            ->count(100)
            ->create();

        // For each order, create 1-5 order items
        $orders->each(function ($order) {
            $itemCount = rand(1, 5);
            StoreOrderItem::factory()
                ->count($itemCount)
                ->create(['store_order_id' => $order->id]);

            // Update order total based on items
            $total = $order->storeOrderItems()->sum(\DB::raw('quantity * sale_price'));
            $order->update([
                'total_amount' => $total,
                'due_amount' => max(0, $total - $order->paid_amount)
            ]);
        });
    }
}
