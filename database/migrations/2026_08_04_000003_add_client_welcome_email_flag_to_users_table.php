<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a send-once guard for the regular client welcome email.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'client_welcome_email_sent_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('client_welcome_email_sent_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('users', 'client_welcome_email_sent_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('client_welcome_email_sent_at');
        });
    }
};
