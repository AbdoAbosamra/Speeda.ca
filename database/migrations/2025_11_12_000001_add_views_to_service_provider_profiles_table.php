<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_provider_profiles') && ! Schema::hasColumn('service_provider_profiles', 'views')) {
            Schema::table('service_provider_profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('views')->default(0)->after('completed_jobs');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_provider_profiles') && Schema::hasColumn('service_provider_profiles', 'views')) {
            Schema::table('service_provider_profiles', function (Blueprint $table) {
                $table->dropColumn('views');
            });
        }
    }
};
