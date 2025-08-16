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
        Schema::create('store_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->enum('customer_type', ['walking', 'registered'])->default('walking');
            $table->double('total_amount', 10, 2)->default(0);
            $table->double('paid_amount', 10, 2)->default(0);
            $table->double('due_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['paid', 'due', 'partial'])->default('due');
            $table->enum('payment_method', ['cash', 'card', 'bank', 'bkash', 'Nagad'])->default('cash');
            $table->double('discount', 10, 2)->default(0);
            $table->double('adjustment', 10, 2)->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_orders');
    }
};
