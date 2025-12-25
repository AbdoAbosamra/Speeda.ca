<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Service provider service areas (multiple locations they serve)
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->integer('radius_km')->default(10);
            $table->decimal('extra_charge', 10, 2)->nullable();
            $table->integer('estimated_travel_time')->nullable()->comment('in minutes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['service_provider_id', 'location_id']);
            $table->index(['location_id', 'is_active']);
        });

        // Service provider availability schedule
        Schema::create('availability_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['service_provider_id', 'day_of_week']);
            $table->index(['day_of_week', 'is_available']);
        });

        // Service provider portfolio (work samples)
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->date('project_date')->nullable();
            $table->decimal('project_cost', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['service_provider_id', 'is_featured']);
        });

        // Service packages (pre-defined service packages)
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_minutes')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['service_provider_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_packages');
        Schema::dropIfExists('portfolios');
        Schema::dropIfExists('availability_schedules');
        Schema::dropIfExists('service_areas');
    }
};
