<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 100);
            $table->string('contact_number', 20)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->string('order_type', 50)->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('landmark', 255)->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->dateTime('order_date')->useCurrent();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->string('discount_type', 50)->nullable();

            $table->foreign('processed_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};