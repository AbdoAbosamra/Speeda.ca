<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Retired. Kept as a no-op so legacy deployment commands remain safe.
 * The former developer account must never be recreated or restored by seeding.
 * Existing accounts are removed through the admin user deletion workflow.
 */
class OwnerProviderSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('OwnerProviderSeeder is retired; no accounts were created or restored.');
    }
}
