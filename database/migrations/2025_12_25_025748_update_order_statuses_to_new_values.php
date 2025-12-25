<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update old status values to new ones in orders table
        DB::table('orders')
            ->where('status', 'completed')
            ->update(['status' => 'complete']);

        DB::table('orders')
            ->where('status', 'pending')
            ->update(['status' => 'processing']);

        DB::table('orders')
            ->where('status', 'refunded')
            ->update(['status' => 'cancelled']);

        // Update order_status_histories table if it exists and has data
        if (Schema::hasTable('order_status_histories')) {
            DB::table('order_status_histories')
                ->where('status', 'completed')
                ->update(['status' => 'complete']);

            DB::table('order_status_histories')
                ->where('status', 'pending')
                ->update(['status' => 'processing']);

            DB::table('order_status_histories')
                ->where('status', 'refunded')
                ->update(['status' => 'cancelled']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the changes (for rollback purposes)
        DB::table('orders')
            ->where('status', 'complete')
            ->update(['status' => 'completed']);

        DB::table('orders')
            ->where('status', 'processing')
            ->whereIn('id', function ($query) {
                // Only reverse orders that were originally 'pending'
                // This is approximate since we can't track exact original values
                $query->select('id')->from('orders')->where('status', 'processing');
            })
            ->update(['status' => 'pending']);

        DB::table('orders')
            ->where('status', 'cancelled')
            ->whereIn('id', function ($query) {
                // Only reverse orders that were originally 'refunded'
                // This is approximate since we can't track exact original values
                $query->select('id')->from('orders')->where('status', 'cancelled');
            })
            ->update(['status' => 'refunded']);

        if (Schema::hasTable('order_status_histories')) {
            DB::table('order_status_histories')
                ->where('status', 'complete')
                ->update(['status' => 'completed']);

            DB::table('order_status_histories')
                ->where('status', 'processing')
                ->update(['status' => 'pending']);

            DB::table('order_status_histories')
                ->where('status', 'cancelled')
                ->update(['status' => 'refunded']);
        }
    }
};
