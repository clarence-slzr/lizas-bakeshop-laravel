<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('full_name', 100)->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'cashier']);
            $table->timestamp('created_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_login')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};