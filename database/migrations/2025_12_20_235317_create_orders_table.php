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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // buyer
            $table->foreignId('store_id')->constrained()->cascadeOnDelete(); // store
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete(); // delivery city
            $table->decimal('total', 10, 2);
            $table->decimal('delivery_price', 10, 2)->default(0);
            $table->string('status')->default('new');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
