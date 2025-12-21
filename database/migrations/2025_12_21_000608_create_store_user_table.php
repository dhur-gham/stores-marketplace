<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_user', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            
            $table->foreignId('store_id')
                ->constrained()
                ->cascadeOnDelete();
            
            $table->timestamps();
            
            // Ensure a user can't be added to the same store twice
            $table->unique(['user_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_user');
    }
};
