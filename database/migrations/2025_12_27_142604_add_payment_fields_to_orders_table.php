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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->string('payment_transaction_id')->nullable()->after('payment_status');
            $table->string('payment_reference')->nullable()->after('payment_transaction_id');
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
            $table->json('payment_metadata')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'payment_transaction_id',
                'payment_reference',
                'paid_at',
                'payment_metadata',
            ]);
        });
    }
};
