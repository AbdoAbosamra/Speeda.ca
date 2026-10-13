<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offers no longer use claim codes: the WhatsApp message itself names the
 * SPEEDA offer and links to it, and offers have no claim limit.
 *
 * Removes what the code system left behind on databases that already ran the
 * first offers migration. On a fresh database these are already absent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('offer_claims');

        foreach (['max_claims', 'redeemed_count'] as $column) {
            if (Schema::hasColumn('offers', $column)) {
                Schema::table('offers', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }

    public function down(): void
    {
        // The code system is gone; there is nothing to restore.
    }
};
