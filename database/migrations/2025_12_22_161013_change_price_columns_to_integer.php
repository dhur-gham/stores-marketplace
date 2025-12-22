<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Change all price columns from decimal to unsigned big integer for IQD currency.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->default(0)->change();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->default(0)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('total')->default(0)->change();
            $table->unsignedBigInteger('delivery_price')->default(0)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->default(0)->change();
        });

        Schema::table('city_store_delivery', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->change();
            $table->decimal('delivery_price', 10, 2)->default(0)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        Schema::table('city_store_delivery', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });
    }
};
