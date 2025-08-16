<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE store_orders MODIFY COLUMN payment_status ENUM('paid', 'due', 'partial') NOT NULL DEFAULT 'due'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE store_orders MODIFY COLUMN payment_status ENUM('paid', 'due') NOT NULL DEFAULT 'due'");
        });
    }
};
