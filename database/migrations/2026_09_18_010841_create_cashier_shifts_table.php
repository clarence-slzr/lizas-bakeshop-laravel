<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('shift_start');
            $table->dateTime('shift_end')->nullable();
            $table->decimal('starting_cash', 10, 2)->default(0);
            $table->decimal('ending_cash', 10, 2)->default(0);
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->integer('order_count')->default(0);
            $table->text('notes')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_shifts');
    }
};