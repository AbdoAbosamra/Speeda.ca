<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_providers') && Schema::hasColumn('service_providers', 'average_rating')) {
            Schema::table('service_providers', function (Blueprint $table) {
                // Drop the duplicate/legacy column to avoid confusion with `rating`.
                if (Schema::hasColumn('service_providers', 'average_rating')) {
                    $table->dropColumn('average_rating');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_providers') && ! Schema::hasColumn('service_providers', 'average_rating')) {
            Schema::table('service_providers', function (Blueprint $table) {
                $table->decimal('average_rating', 3, 2)->default(0.00)->after('is_featured');
            });
        }
    }
};
