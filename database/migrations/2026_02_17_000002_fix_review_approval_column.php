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
            // Check if 'approved_by' exists (from the incorrect migration) and rename it
            if (Schema::hasColumn('service_provider_reviews', 'approved_by') && !Schema::hasColumn('service_provider_reviews', 'admin_approved_by')) {
                $table->renameColumn('approved_by', 'admin_approved_by');
            } elseif (!Schema::hasColumn('service_provider_reviews', 'admin_approved_by')) {
                // If neither exists, create the correct column
                $table->foreignId('admin_approved_by')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_provider_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('service_provider_reviews', 'admin_approved_by')) {
                $table->renameColumn('admin_approved_by', 'approved_by');
            }
        });
    }
};
