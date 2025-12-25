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
        if (Schema::hasTable('service_providers') && ! Schema::hasColumn('service_providers', 'contact_email')) {
            Schema::table('service_providers', function (Blueprint $table) {
                $table->string('contact_email')->nullable()->after('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('service_providers', 'contact_email')) {
            Schema::table('service_providers', function (Blueprint $table) {
                $table->dropColumn('contact_email');
            });
        }
    }
};
