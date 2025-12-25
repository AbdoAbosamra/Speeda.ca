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
        // Fix service_provider_profiles table
        if (Schema::hasTable('service_provider_profiles')) {
            Schema::table('service_provider_profiles', function (Blueprint $table) {
                if (!Schema::hasColumn('service_provider_profiles', 'portfolio_images')) {
                    $table->json('portfolio_images')->nullable();
                }
                if (!Schema::hasColumn('service_provider_profiles', 'portfolio_videos')) {
                    $table->json('portfolio_videos')->nullable();
                }
                if (!Schema::hasColumn('service_provider_profiles', 'is_featured')) {
                    $table->boolean('is_featured')->default(false);
                }
                
                // Ensure foreign keys exist
                if (!Schema::hasColumn('service_provider_profiles', 'user_id')) {
                    $table->foreignId('user_id')->constrained()->onDelete('cascade');
                }
            });
        }

        // Create service_provider_categories pivot table if it doesn't exist
        if (!Schema::hasTable('service_provider_categories')) {
            Schema::create('service_provider_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_provider_profile_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->constrained()->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the service_provider_categories table if it exists
        if (Schema::hasTable('service_provider_categories')) {
            Schema::dropIfExists('service_provider_categories');
        }
        
        // Remove added columns from service_provider_profiles
        Schema::table('service_provider_profiles', function (Blueprint $table) {
            $table->dropColumn(['portfolio_images', 'portfolio_videos', 'is_featured']);
        });
    }
};
