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
        // Add missing columns to service_provider_reviews table if they don't exist
        if (Schema::hasTable('service_provider_reviews')) {
            Schema::table('service_provider_reviews', function (Blueprint $table) {
                // Add is_active column for admin approval workflow
                if (!Schema::hasColumn('service_provider_reviews', 'is_active')) {
                    $table->boolean('is_active')->default(false)->after('is_featured');
                }

                // Add admin audit trail columns
                if (!Schema::hasColumn('service_provider_reviews', 'admin_approved_by')) {
                    $table->unsignedBigInteger('admin_approved_by')->nullable()->after('is_active');
                }

                if (!Schema::hasColumn('service_provider_reviews', 'admin_approved_at')) {
                    $table->timestamp('admin_approved_at')->nullable()->after('admin_approved_by');
                }

                // Add foreign key for admin_approved_by if it doesn't exist
                if (!Schema::hasColumn('service_provider_reviews', 'admin_approved_by')) {
                    // Already handled above
                } else {
                    // Check if the foreign key exists
                    try {
                        $table->foreign('admin_approved_by')->references('id')->on('users')->onDelete('set null');
                    } catch (\Exception $e) {
                        // Foreign key might already exist, continue
                    }
                }
            });
        }

        // Also restore columns on service_provider_profiles table that were removed
        if (Schema::hasTable('service_provider_profiles')) {
            Schema::table('service_provider_profiles', function (Blueprint $table) {
                // Add columns if they don't exist
                if (!Schema::hasColumn('service_provider_profiles', 'average_rating')) {
                    $table->decimal('average_rating', 3, 2)->default(0);
                }
                if (!Schema::hasColumn('service_provider_profiles', 'total_reviews')) {
                    $table->unsignedInteger('total_reviews')->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove review-related columns from service_provider_profiles
        if (Schema::hasTable('service_provider_profiles')) {
            Schema::table('service_provider_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('service_provider_profiles', 'average_rating')) {
                    $table->dropColumn('average_rating');
                }
                if (Schema::hasColumn('service_provider_profiles', 'total_reviews')) {
                    $table->dropColumn('total_reviews');
                }
            });
        }

        // Remove admin audit columns from service_provider_reviews
        if (Schema::hasTable('service_provider_reviews')) {
            Schema::table('service_provider_reviews', function (Blueprint $table) {
                if (Schema::hasColumn('service_provider_reviews', 'admin_approved_by')) {
                    $table->dropForeign(['admin_approved_by']);
                    $table->dropColumn('admin_approved_by');
                }
                if (Schema::hasColumn('service_provider_reviews', 'admin_approved_at')) {
                    $table->dropColumn('admin_approved_at');
                }
                if (Schema::hasColumn('service_provider_reviews', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }
    }
};
