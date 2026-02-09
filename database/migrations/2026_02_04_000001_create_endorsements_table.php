<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Creates the endorsements table for professional "Recommend" feature.
     * Following SPEEDA V5.0 architecture - using service_provider_id (not legacy profile_id)
     */
    public function up(): void
    {
        Schema::create('endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')
                ->constrained('service_providers')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamps();

            // Unique constraint: one endorsement per user per provider
            $table->unique(['service_provider_id', 'user_id']);

            // Index for efficient counting
            $table->index('service_provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endorsements');
    }
};
