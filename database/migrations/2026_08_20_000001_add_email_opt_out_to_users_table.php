<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A single, global opt-out from every non-essential email.
 *
 * The existing `broadcast_opt_out_at` only ever covered admin broadcasts, so a
 * provider who unsubscribed from the inbox kept receiving journey and review
 * emails — which is exactly what people mean when they say the unsubscribe
 * link "doesn't work". This column is what the footer link in every marketing
 * and lifecycle email now sets.
 *
 * Transactional mail (password reset, email verification) deliberately ignores
 * it: those are not marketing, and silencing them would lock a user out of
 * their own account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_opt_out_at')->nullable()->after('broadcast_opt_out_at');
        });

        // Anyone who already opted out of broadcasts clearly did not want the
        // rest either — carry the decision over rather than quietly resuming.
        \DB::table('users')
            ->whereNotNull('broadcast_opt_out_at')
            ->update(['email_opt_out_at' => \DB::raw('broadcast_opt_out_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_opt_out_at');
        });
    }
};
