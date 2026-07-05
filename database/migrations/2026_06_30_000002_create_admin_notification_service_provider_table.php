<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableName = 'admin_notification_service_provider';

        if (Schema::hasTable($tableName)) {
            if (DB::table($tableName)->count() === 0) {
                Schema::drop($tableName);
            } else {
                return;
            }
        }

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_notification_id');
            $table->unsignedBigInteger('service_provider_id');
            $table->timestamps();

            $table->unique(
                ['admin_notification_id', 'service_provider_id'],
                'an_sp_unique'
            );
            $table->index('service_provider_id', 'an_sp_provider_idx');

            $table->foreign('admin_notification_id', 'an_sp_notification_fk')
                ->references('id')
                ->on('admin_notifications')
                ->cascadeOnDelete();

            $table->foreign('service_provider_id', 'an_sp_provider_fk')
                ->references('id')
                ->on('service_providers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notification_service_provider');
    }
};
