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
        Schema::create('store_stock_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_stock_id')->constrained('store_stocks')->cascadeOnDelete();
            $table->foreignId('store_product_id')->constrained('store_products')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->double('unit_cost', 10, 2)->default(0);
            $table->double('total_cost', 10, 2)->default(0);
            $table->double('sale_price', 10, 2)->default(0);
            $table->json('adjustment_data')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_stock_items');
    }
};
