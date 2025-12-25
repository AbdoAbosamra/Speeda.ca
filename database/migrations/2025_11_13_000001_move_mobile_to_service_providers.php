<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration will ensure a `phone` column exists on `service_providers`,
     * copy existing `users.mobile` values into `service_providers.phone` where
     * a provider record exists for the user, and create a minimal provider
     * record for users that have a mobile but no provider row yet. Finally it
     * drops the `mobile` column from `users`.
     */
    public function up(): void
    {
        // Add phone column to service_providers if it does not exist already
        if (! Schema::hasTable('service_providers')) {
            // If the table doesn't exist yet, nothing to migrate here.
            return;
        }

        if (! Schema::hasColumn('service_providers', 'phone')) {
            Schema::table('service_providers', function (Blueprint $table) {
                $table->string('phone')->nullable()->after('location_id');
            });
        }

        // Copy mobile -> phone for users who already have a provider record
        // Use updateOrInsert to safely write phone values and avoid
        // duplicate-key errors that can occur in some environments.
        DB::table('users')
            ->whereNotNull('mobile')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $mobile = trim($user->mobile);
                    if (empty($mobile)) {
                        continue;
                    }

                    // If a provider already exists with the same phone number
                    // but a different user_id, skip creating a new row to avoid
                    // violating the unique phone constraint. Otherwise update
                    // or insert as appropriate.
                    $existingByPhone = DB::table('service_providers')->where('phone', $mobile)->first();

                    if ($existingByPhone) {
                        if ($existingByPhone->user_id == $user->id) {
                            DB::table('service_providers')
                                ->where('id', $existingByPhone->id)
                                ->update(['phone' => $mobile, 'updated_at' => now()]);
                        } else {
                            // Skip creating a duplicate record; the phone already
                            // belongs to another provider. Continue to next user.
                            continue;
                        }
                    } else {
                        // Atomically update existing provider for this user or insert a new one.
                        DB::table('service_providers')->updateOrInsert(
                            ['user_id' => $user->id],
                            ['phone' => $mobile, 'updated_at' => now(), 'created_at' => now()]
                        );
                    }
                }
            });

        // Drop mobile column from users (if present)
        if (Schema::hasColumn('users', 'mobile')) {
            Schema::table('users', function (Blueprint $table) {
                // Drop the index first, then the column
                $table->dropIndex(['mobile']);
                $table->dropColumn('mobile');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Move phone back to users.mobile and remove phone from service_providers
     * if it was added by this migration.
     */
    public function down(): void
    {
        // Recreate the users.mobile column if it doesn't exist
        if (! Schema::hasColumn('users', 'mobile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('mobile')->nullable()->after('email');
            });
        }

        // Copy phone -> mobile where possible
        if (Schema::hasTable('service_providers') && Schema::hasColumn('service_providers', 'phone')) {
            DB::table('service_providers')
                ->whereNotNull('phone')
                ->orderBy('id')
                ->chunkById(100, function ($providers) {
                    foreach ($providers as $provider) {
                        DB::table('users')
                            ->where('id', $provider->user_id)
                            ->update(['mobile' => $provider->phone]);
                    }
                });

            // Drop phone column from service_providers - only if it exists
            if (Schema::hasColumn('service_providers', 'phone')) {
                Schema::table('service_providers', function (Blueprint $table) {
                    $table->dropColumn('phone');
                });
            }
        }
    }
};
