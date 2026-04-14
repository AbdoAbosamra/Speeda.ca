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
        if (Schema::hasTable('service_provider_reviews') && Schema::hasColumn('service_provider_reviews', 'service_provider_profile_id')) {
            Schema::table('service_provider_reviews', function (Blueprint $table) {
                $table->renameColumn('service_provider_profile_id', 'service_provider_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_provider_reviews', function (Blueprint $table) {
            $table->renameColumn('service_provider_id', 'service_provider_profile_id');
        });
    }
};
