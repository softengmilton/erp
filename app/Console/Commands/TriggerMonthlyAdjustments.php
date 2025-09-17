<?php

namespace App\Console\Commands;

use App\Models\StoreProduct;
use App\Models\StoreStock;
use App\Services\Store\StockAdjustmentService;
use Illuminate\Console\Command;

class TriggerMonthlyAdjustments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:trigger-monthly-adjustments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service  = new StockAdjustmentService();
        $products = StoreProduct::all();
        $stocks   = StoreStock::all();

        foreach ($stocks as $stock) {
            foreach ($products as $product) {
                $service->adjustStock($product, $stock);
            }
        }

        $this->info('Monthly stock adjustment completed successfully.');
    }
}
