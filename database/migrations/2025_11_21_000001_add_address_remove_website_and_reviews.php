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
        // Add address column to service_providers table
        if (Schema::hasTable('service_providers')) {
            Schema::table('service_providers', function (Blueprint $table) {
                if (! Schema::hasColumn('service_providers', 'address')) {
                    $table->string('address', 500)->nullable()->after('contact_email');
                }
                if (Schema::hasColumn('service_providers', 'website')) {
                    $table->dropColumn('website');
                }
            });
        }

        // Add address column to service_provider_profiles table and remove review-related columns
        if (Schema::hasTable('service_provider_profiles')) {
            Schema::table('service_provider_profiles', function (Blueprint $table) {
                if (! Schema::hasColumn('service_provider_profiles', 'address')) {
                    $table->string('address', 500)->nullable()->after('phone');
                }
                if (Schema::hasColumn('service_provider_profiles', 'website')) {
                    $table->dropColumn('website');
                }
                if (Schema::hasColumn('service_provider_profiles', 'average_rating')) {
                    $table->dropColumn('average_rating');
                }
                if (Schema::hasColumn('service_provider_profiles', 'total_reviews')) {
                    $table->dropColumn('total_reviews');
                }
            });
        }

        // Drop the service_provider_reviews table
        Schema::dropIfExists('service_provider_reviews');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the service_provider_reviews table
        Schema::create('service_provider_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_provider_profile_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->integer('rating');
            $table->text('review_text')->nullable();
            $table->json('rating_breakdown')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->foreign('service_provider_profile_id')
                  ->references('id')
                  ->on('service_provider_profiles')
                  ->onDelete('cascade');

            $table->foreign('client_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('booking_id')
                  ->references('id')
                  ->on('bookings')
                  ->onDelete('set null');

            $table->unique(['service_provider_profile_id', 'client_id'], 'sp_reviews_profile_client_unique');
            $table->index('rating');
            $table->index('created_at');
        });

        // Restore columns in service_provider_profiles
        if (Schema::hasTable('service_provider_profiles')) {
            Schema::table('service_provider_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('service_provider_profiles', 'address')) {
                    $table->dropColumn('address');
                }
                if (! Schema::hasColumn('service_provider_profiles', 'website')) {
                    $table->string('website', 255)->nullable()->after('phone');
                }
                if (! Schema::hasColumn('service_provider_profiles', 'average_rating')) {
                    $table->decimal('average_rating', 3, 2)->default(0)->after('business_license');
                }
                if (! Schema::hasColumn('service_provider_profiles', 'total_reviews')) {
                    $table->integer('total_reviews')->default(0)->after('average_rating');
                }
            });
        }

        // Restore columns in service_providers
        if (Schema::hasTable('service_providers')) {
            Schema::table('service_providers', function (Blueprint $table) {
                if (Schema::hasColumn('service_providers', 'address')) {
                    $table->dropColumn('address');
                }
                if (! Schema::hasColumn('service_providers', 'website')) {
                    $table->string('website', 255)->nullable()->after('contact_email');
                }
            });
        }
    }
};
