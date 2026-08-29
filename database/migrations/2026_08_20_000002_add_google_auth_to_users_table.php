<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Google sign-in support.
 *
 * `password` becomes nullable because an account created through Google has
 * never had one. Storing a random hash instead would work, but then nothing
 * could tell a Google-only account apart from a normal one — and the person
 * who signs up with Google, comes back a month later and types a password
 * would just be told their credentials are wrong, with no hint why.
 *
 * Laravel's hasher already returns false for a null hash, so a null password
 * can never be matched by any input.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Google's subject id, not the email: an email can be changed or
            // reassigned, the subject id is stable for the life of the account.
            $table->string('google_id')->nullable()->unique()->after('email_verified_at');
            $table->timestamp('google_linked_at')->nullable()->after('google_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'google_linked_at']);
        });

        // Not reverted to NOT NULL: rows created through Google have no
        // password, so the constraint could not be re-applied without
        // inventing one for them.
    }
};
