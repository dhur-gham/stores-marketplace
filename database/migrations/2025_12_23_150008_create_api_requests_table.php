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
        Schema::create('api_requests', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('path', 500);
            $table->text('full_url');
            $table->unsignedSmallInteger('status_code');
            $table->timestamp('request_started_at', 6);
            $table->timestamp('request_ended_at', 6);
            $table->unsignedInteger('duration_ms');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('request_size')->nullable();
            $table->unsignedInteger('response_size')->nullable();
            $table->json('request_headers')->nullable();
            $table->text('exception')->nullable();
            $table->timestamps();

            $table->index('method');
            $table->index('path');
            $table->index('status_code');
            $table->index('duration_ms');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_requests');
    }
};
