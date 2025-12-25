<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewsToServiceProvidersTable extends Migration
{
    public function up()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->unsignedBigInteger('views')->default(0)->after('completed_jobs');
        });
    }

    public function down()
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn('views');
        });
    }
}
