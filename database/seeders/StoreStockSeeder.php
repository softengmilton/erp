<?php

namespace Database\Seeders;

use App\Models\StoreStock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StoreStock::factory()->count(500)->create();
    }
}
