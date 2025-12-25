<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder to verify that mobile numbers are moved to service_providers.phone
 * and that formatting is applied. Run this after running the migration.
 */
class VerifyMobileMoveSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting verification: copying and formatting checks');

        $usersWithMobile = DB::table('users')->whereNotNull('mobile')->count();
        $this->command->info("Users with mobile column present: {$usersWithMobile}");

        $providersWithPhone = DB::table('service_providers')->whereNotNull('phone')->count();
        $this->command->info("Service providers with phone set: {$providersWithPhone}");

        // Show a few examples (first 10) to inspect formatting
        $samples = DB::table('service_providers')
            ->whereNotNull('phone')
            ->limit(10)
            ->get(['id', 'user_id', 'phone']);

        foreach ($samples as $s) {
            $this->command->info("provider: {$s->id}, user: {$s->user_id}, phone: {$s->phone}");
        }

        $this->command->info('Verification complete. Manually inspect outputs for expected +1 (###) ###-#### formatting.');
    }
}
