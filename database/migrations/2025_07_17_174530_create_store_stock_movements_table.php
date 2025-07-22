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
        Schema::create('store_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_stock_id')->constrained('store_stocks')->cascadeOnDelete();
            $table->foreignId('store_product_id')->constrained('store_products')->cascadeOnDelete();
            $table->integer('change_quantity')->default(0);
            $table->enum('source_type', ['purchase', 'sale', 'return'])->default('sale');
            $table->json('source_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_stock_movements');
    }
};
