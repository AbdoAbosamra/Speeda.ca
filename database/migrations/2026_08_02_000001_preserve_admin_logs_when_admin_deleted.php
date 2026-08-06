<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stop the audit trail from being destroyed when an admin is deleted.
 *
 * admin_logs.admin_id was created with onDelete('cascade'), so permanently
 * deleting an admin silently removed every action they had ever taken — exactly
 * the records an audit log exists to keep. The column becomes nullable with
 * ON DELETE SET NULL so the history survives; the log already stores
 * ip_address/user_agent, and the affected record is named in model_name.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_logs')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // SQLite has no real ALTER for constraints; Laravel rebuilds the table,
        // and dropping a foreign key by name is unsupported there.
        if (!$isSqlite) {
            Schema::table('admin_logs', function (Blueprint $table) {
                $table->dropForeign(['admin_id']);
            });
        }

        Schema::table('admin_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable()->change();
        });

        if (!$isSqlite) {
            Schema::table('admin_logs', function (Blueprint $table) {
                $table->foreign('admin_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('admin_logs')) {
            return;
        }

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // Rows orphaned while the column was nullable cannot be re-pointed at a
        // user, so drop them before restoring the NOT NULL cascade.
        DB::table('admin_logs')->whereNull('admin_id')->delete();

        if (!$isSqlite) {
            Schema::table('admin_logs', function (Blueprint $table) {
                $table->dropForeign(['admin_id']);
            });
        }

        Schema::table('admin_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id')->nullable(false)->change();
        });

        if (!$isSqlite) {
            Schema::table('admin_logs', function (Blueprint $table) {
                $table->foreign('admin_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }
};
