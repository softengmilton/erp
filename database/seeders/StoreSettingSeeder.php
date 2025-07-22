<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\StoreSetting::create([
            'key' => 'store_name',
            'value' => 'Store Name',
        ]);
        \App\Models\StoreSetting::create([
            'key' => 'store_address',
            'value' => 'Store Address',
        ]);
        \App\Models\StoreSetting::create([
            'key' => 'store_phone',
            'value' => 'Store Phone',
        ]);
        \App\Models\StoreSetting::create([
            'key' => 'store_email',
            'value' => 'Store Email',
        ]);
        \App\Models\StoreSetting::create([
            'key' => 'store_logo',
            'value' => 'Store Logo',
        ]);
        \App\Models\StoreSetting::create([
            'key' => 'store_currency',
            'value' => 'Store Currency',
        ]);
        \App\Models\StoreSetting::create([
            'key' => 'store_wallet',
            'value' => 0.00,
        ]);
    }
}
