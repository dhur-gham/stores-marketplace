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
        Schema::create('discount_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->unsignedInteger('discount_value'); // Percentage (1-100) or fixed amount in IQD
            $table->dateTime('start_date'); // Stored in UTC
            $table->dateTime('end_date'); // Stored in UTC
            $table->enum('status', ['scheduled', 'active', 'expired'])->default('scheduled');
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_plans');
    }
};
