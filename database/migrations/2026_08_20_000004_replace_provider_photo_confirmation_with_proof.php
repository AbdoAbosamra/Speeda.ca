<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reworks the admin-photo consent trail: no in-app "confirm" step from the
 * provider, and no checkbox blocking the upload. Instead the admin can attach
 * an optional proof image (e.g. a screenshot of the provider agreeing over
 * WhatsApp) alongside the photo, and both are simply visible to the provider
 * on their own profile — nothing for them to click.
 *
 * photo_consent_admin_confirmed_at is dropped for being a duplicate of
 * photo_uploaded_by_admin_at (both were always set together in practice).
 * photo_consent_provider_confirmed_at is dropped along with the confirmation
 * step it recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->string('photo_consent_proof_path')->nullable()->after('photo_consent_admin_id');
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn(['photo_consent_admin_confirmed_at', 'photo_consent_provider_confirmed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->timestamp('photo_consent_admin_confirmed_at')->nullable()->after('photo_consent_admin_id');
            $table->timestamp('photo_consent_provider_confirmed_at')->nullable()->after('photo_consent_admin_confirmed_at');
        });

        // Best-effort backfill so a rollback does not leave the restored
        // columns silently empty for rows that already have the upload stamp.
        DB::table('service_providers')
            ->whereNotNull('photo_uploaded_by_admin_at')
            ->update(['photo_consent_admin_confirmed_at' => DB::raw('photo_uploaded_by_admin_at')]);

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn('photo_consent_proof_path');
        });
    }
};
