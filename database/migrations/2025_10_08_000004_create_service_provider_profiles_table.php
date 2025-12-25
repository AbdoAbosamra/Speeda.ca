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
        Schema::create('service_provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Allow nullable category/location so profiles can be created when mapping is not found
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('cascade');

            // Professional Information
            $table->string('profession')->nullable();
            $table->text('bio')->nullable();
            $table->text('services_offered')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->integer('experience_years')->nullable();

            // Contact Information
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();

            // Location & Availability
            $table->string('service_area')->nullable(); // e.g., "Montreal and surrounding areas"
            $table->boolean('available_weekends')->default(false);
            $table->boolean('available_evenings')->default(false);
            $table->json('availability_schedule')->nullable(); // Store weekly schedule

            // Professional Details
            $table->json('certifications')->nullable(); // Array of certifications
            $table->json('languages')->nullable(); // Array of languages spoken
            $table->json('specializations')->nullable(); // Array of specializations

            // Media & Portfolio
            $table->string('profile_image')->nullable();
            $table->json('portfolio_images')->nullable(); // Array of portfolio image URLs
            $table->json('portfolio_videos')->nullable(); // Array of portfolio video URLs

            // Business Information
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->enum('business_type', ['individual', 'company'])->default('individual');
            $table->string('company_name')->nullable();
            $table->string('business_license')->nullable();

            // Ratings & Reviews
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->integer('completed_jobs')->default(0);

            // Emergency Services
            $table->boolean('emergency_available')->default(false);
            $table->integer('response_time_hours')->nullable(); // Response time in hours

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_provider_profiles');
    }
};
