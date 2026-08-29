<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consent trail for a photo the ADMIN uploads on a provider's behalf.
 *
 * Never shown on the public profile — only the admin edit screen and the
 * provider's own dashboard read these columns. Two separate confirmations are
 * kept because they are two separate facts: the admin asserting they obtained
 * consent (often over phone/WhatsApp, before the upload) and the provider
 * confirming it themselves afterwards, in-app, on their own account.
 *
 * A photo the provider uploads themselves is not covered by any of this: there
 * is nothing to consent to when someone chooses their own photo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->timestamp('photo_uploaded_by_admin_at')->nullable()->after('profile_image');
            $table->foreignId('photo_consent_admin_id')->nullable()->after('photo_uploaded_by_admin_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('photo_consent_admin_confirmed_at')->nullable()->after('photo_consent_admin_id');
            $table->timestamp('photo_consent_provider_confirmed_at')->nullable()->after('photo_consent_admin_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('photo_consent_admin_id');
            $table->dropColumn([
                'photo_uploaded_by_admin_at',
                'photo_consent_admin_confirmed_at',
                'photo_consent_provider_confirmed_at',
            ]);
        });
    }
};
