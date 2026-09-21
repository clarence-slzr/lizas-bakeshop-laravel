<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('reason', 255);
            $table->text('refund_items')->nullable();
            $table->unsignedBigInteger('processed_by');
            $table->dateTime('refund_date');
            $table->string('status', 20)->default('approved');
            $table->text('notes')->nullable();

            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('processed_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
