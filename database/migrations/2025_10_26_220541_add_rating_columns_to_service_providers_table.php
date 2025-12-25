<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('service_providers', 'average_rating')) {
                $table->decimal('average_rating', 3, 2)->default(0.00);
            }
            if (!Schema::hasColumn('service_providers', 'total_reviews')) {
                $table->integer('total_reviews')->default(0);
            }
            if (!Schema::hasColumn('service_providers', 'completed_jobs')) {
                $table->integer('completed_jobs')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn(['average_rating', 'total_reviews', 'completed_jobs']);
        });
    }
};
