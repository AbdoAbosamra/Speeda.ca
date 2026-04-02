<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('service_providers', 'profile_completion_percent')) {
            return;
        }

        Schema::table('service_providers', function (Blueprint $table) {
            $table->unsignedTinyInteger('profile_completion_percent')
                ->default(0)
                ->after('profile_image');
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (Schema::hasColumn('service_providers', 'profile_completion_percent')) {
                $table->dropColumn('profile_completion_percent');
            }
        });
    }
};

