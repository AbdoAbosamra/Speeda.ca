<?php

namespace App\Console\Commands;

use Database\Seeders\TestDataSeeder;
use Illuminate\Console\Command;

/**
 * Artisan command to clean up all test data from Speeda.ca
 *
 * Usage: php artisan test-data:cleanup [--confirm]
 */
class TestDataCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-data:cleanup
                            {--confirm : Skip confirmation prompt (for automation)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe all test users with @test-speeda.ca domain and their related data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if there are any test users
        $testUserCount = TestDataSeeder::getTestUserCount();

        if ($testUserCount === 0) {
            $this->warn('⚠️  No test users found with @test-speeda.ca domain.');
            $this->info('   Nothing to clean up.');
            return self::SUCCESS;
        }

        $this->info("Found {$testUserCount} test user(s) with @test-speeda.ca domain.");

        // Confirm deletion unless --confirm flag is used
        if (!$this->option('confirm')) {
            if (!$this->confirm('⚠️  Are you sure you want to delete ALL test users and their data? This cannot be undone.', false)) {
                $this->info('Cleanup cancelled.');
                return self::SUCCESS;
            }
        }

        $this->info('Starting cleanup...');

        // Perform cleanup
        $result = TestDataSeeder::wipeTestData();

        if ($result['success']) {
            $this->newLine();
            $this->info('✅ ' . $result['message']);
            $this->newLine();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Test Users Deleted', $result['deleted_users']],
                    ['Provider Profiles Deleted', $result['deleted_providers']],
                ]
            );

            return self::SUCCESS;
        }

        $this->error('❌ Cleanup failed: ' . $result['message']);
        return self::FAILURE;
    }
}
