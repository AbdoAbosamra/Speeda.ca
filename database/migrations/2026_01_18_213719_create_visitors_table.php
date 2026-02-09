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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip_hash', 64); // SHA256 hash of IP
            $table->string('user_agent_hash', 64); // SHA256 hash of User Agent
            $table->string('path')->nullable(); // The path visited
            $table->string('referer')->nullable(); // HTTP referer
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('visited_at');

            // Composite index for faster queries on unique visitors
            $table->index(['ip_hash', 'user_agent_hash']);
            $table->index('visited_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
