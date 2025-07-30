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
        Schema::create('store_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('supplier_name')->nullable();
            $table->double('shipping_cost', 10, 2)->default(0);
            $table->double('other_fees', 10, 2)->default(0);
            $table->double('total_cost', 10, 2)->default(0);
            $table->longText('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_stocks');
    }
};
