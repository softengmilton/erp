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
            'key'   => 'business_title',
            'value' => 'My Business',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'business_email',
            'value' => 'business@example.com',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'phone',
            'value' => '+1234567890',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'address',
            'value' => '123 Main Street, City, Country',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'description',
            'value' => 'This is a demo business description.',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'currency',
            'value' => 'USD',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'opening_time',
            'value' => '09:00',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'closing_time',
            'value' => '18:00',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'invoice_footer_text',
            'value' => 'Thank you for shopping with us!',
        ]);

        \App\Models\StoreSetting::create([
            'key'   => 'logo',
            'value' => 'default-logo.png',
        ]);
    }
}
