<?php

namespace App\Services\Store;

use App\Models\StoreStockItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockAdjustmentService
{
    /**
     * Mark current month items as TRUE and all previous as FALSE
     */
    public static function adjustAllStockItems(): void
    {
        $currentMonthStart = Carbon::now()->startOfMonth();

        DB::transaction(function () use ($currentMonthStart) {

            // 1. Set ALL previous months to false
            StoreStockItem::where('created_at', '<', $currentMonthStart)
                ->update(['adjustment' => true]);

            // 2. Set ONLY current month to false
            StoreStockItem::where('created_at', '>=', $currentMonthStart)
                ->update(['adjustment' => false]);
        });
    }
}
