<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_provider_reviews', function (Blueprint $table) {
            // Check and add rating_breakdown if missing
            if (!Schema::hasColumn('service_provider_reviews', 'rating_breakdown')) {
                $table->json('rating_breakdown')->nullable()->after('review_text');
            }

            // Check and add booking_id if missing
            if (!Schema::hasColumn('service_provider_reviews', 'booking_id')) {
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null')->after('client_id');
            }

            // Check and add is_verified if missing
            if (!Schema::hasColumn('service_provider_reviews', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('rating_breakdown');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_provider_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('service_provider_reviews', 'rating_breakdown')) {
                $table->dropColumn('rating_breakdown');
            }
            if (Schema::hasColumn('service_provider_reviews', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }
            if (Schema::hasColumn('service_provider_reviews', 'is_verified')) {
                $table->dropColumn('is_verified');
            }
        });
    }
};
