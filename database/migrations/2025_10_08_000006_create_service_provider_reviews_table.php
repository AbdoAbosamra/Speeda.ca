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
        Schema::create('service_provider_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');

            $table->integer('rating')->unsigned(); // 1-5 stars
            $table->text('review_text')->nullable();
            $table->json('rating_breakdown')->nullable(); // e.g., {"quality": 5, "communication": 4, "punctuality": 5}

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            // Ensure a client can only review a service provider once
            $table->unique(['service_provider_profile_id', 'client_id'], 'sp_reviews_profile_client_unique');

            // Indexes for better performance
            $table->index('rating');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_reviews');
    }
};

