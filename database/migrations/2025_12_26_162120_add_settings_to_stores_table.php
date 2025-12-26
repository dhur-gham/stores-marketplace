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
        Schema::table('stores', function (Blueprint $table) {
            $table->text('business_hours')->nullable()->after('bio');
            $table->string('phone')->nullable()->after('business_hours');
            $table->string('email')->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
            $table->string('facebook_url')->nullable()->after('address');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('twitter_url')->nullable()->after('instagram_url');
            $table->text('return_policy')->nullable()->after('twitter_url');
            $table->text('shipping_policy')->nullable()->after('return_policy');
            $table->text('privacy_policy')->nullable()->after('shipping_policy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'business_hours',
                'phone',
                'email',
                'address',
                'facebook_url',
                'instagram_url',
                'twitter_url',
                'return_policy',
                'shipping_policy',
                'privacy_policy',
            ]);
        });
    }
};
