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
        Schema::table('service_providers', function (Blueprint $table) {
            $table->string('whatsapp_number', 20)->nullable()->after('phone');
            $table->index('whatsapp_number');
        });
    }

    /**
     * Down the migrations.
     */
    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropIndex(['whatsapp_number']);
            $table->dropColumn('whatsapp_number');
        });
    }
};
