<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('AizRVv7s7Q50'),
        ]);

        // $this->call([
        //     CustomerSeeder::class,
        //     StoreProductType::class,
        //     StoreProductSeeder::class,
        //     StoreSettingSeeder::class,
        //     StoreStockSeeder::class,
        //     StoreExpenseTypeSeeder::class,
        //     StoreExpenseSeeder::class,
        //     StoreOrderSeeder::class,
        // ]);
    }
}
