<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unsubscribe flag for admin broadcast emails.
 *
 * Canada's anti-spam law (CASL) requires a working unsubscribe mechanism in
 * commercial email, and mailbox providers weigh unsubscribe handling heavily
 * when scoring sender reputation. Opting out of broadcasts deliberately does
 * NOT stop transactional mail (password resets, review notifications) — those
 * are service messages the account still depends on.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'broadcast_opt_out_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('broadcast_opt_out_at')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'broadcast_opt_out_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('broadcast_opt_out_at');
        });
    }
};
