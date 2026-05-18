<?php

namespace Database\Seeders;

use App\Models\AdminNotification;
use App\Models\Category;
use App\Models\Endorsement;
use App\Models\Location;
use App\Models\Post;
use App\Models\ProviderAnalytics;
use App\Models\Review;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    private const CANADIAN_CITIES = [
        ['city' => 'Toronto',     'lat' => 43.6532, 'lng' => -79.3832],
        ['city' => 'Montreal',    'lat' => 45.5017, 'lng' => -73.5673],
        ['city' => 'Vancouver',   'lat' => 49.2827, 'lng' => -123.1207],
        ['city' => 'Ottawa',      'lat' => 45.4215, 'lng' => -75.6972],
        ['city' => 'Calgary',     'lat' => 51.0447, 'lng' => -114.0719],
        ['city' => 'Edmonton',    'lat' => 53.5461, 'lng' => -113.4938],
        ['city' => 'Quebec City', 'lat' => 46.8139, 'lng' => -71.2080],
        ['city' => 'Laval',       'lat' => 45.6066, 'lng' => -73.7124],
        ['city' => 'Gatineau',    'lat' => 45.4763, 'lng' => -75.7016],
        ['city' => 'Longueuil',   'lat' => 45.5369, 'lng' => -73.5105],
        ['city' => 'Mississauga', 'lat' => 43.5890, 'lng' => -79.6441],
        ['city' => 'Brampton',    'lat' => 43.7315, 'lng' => -79.7624],
    ];

    private const PROFESSIONS = [
        ['name_ar' => 'سباك',     'name_en' => 'Plumber'],
        ['name_ar' => 'كهربائي',  'name_en' => 'Electrician'],
        ['name_ar' => 'نجار',     'name_en' => 'Carpenter'],
        ['name_ar' => 'رسام',     'name_en' => 'Painter'],
        ['name_ar' => 'مقاول',    'name_en' => 'Contractor'],
        ['name_ar' => 'ميكانيكي', 'name_en' => 'Mechanic'],
        ['name_ar' => 'عامل نظافة', 'name_en' => 'Cleaner'],
        ['name_ar' => 'مصمم داخلي', 'name_en' => 'Interior Designer'],
        ['name_ar' => 'مصور',    'name_en' => 'Photographer'],
        ['name_ar' => 'مطور ويب', 'name_en' => 'Web Developer'],
    ];

    public function run(): void
    {
        $this->command->info('=== Speeda TestDataSeeder v2 ===');
        $this->command->info('Generating comprehensive test dataset...');
        $this->command->line('');

        $this->ensureBaseData();
        $this->seedLocations();
        $this->seedCategories();
        $this->seedAdminUser();
        $this->seedServiceProviders();
        $this->seedClients();
        $this->seedReviews();
        $this->seedEndorsements();
        $this->seedAnalytics();
        $this->seedPosts();
        $this->seedNotifications();

        $this->command->line('');
        $this->command->info('=== TestDataSeeder complete ===');
        $this->printSummary();
    }

    // ──────────────────────────────────────────────
    //  Phase 0 — Ensure base tables are populated
    // ──────────────────────────────────────────────

    private function ensureBaseData(): void
    {
        if (Category::count() === 0) {
            $this->command->warn('No categories found. Running CategorySeeder...');
            $this->call(CategorySeeder::class);
        }

        if (Location::count() === 0) {
            $this->command->warn('No locations found. Running LocationSeeder...');
            $this->call(LocationSeeder::class);
        }
    }

    // ──────────────────────────────────────────────
    //  Phase 1 — Locations
    // ──────────────────────────────────────────────

    private function seedLocations(): void
    {
        $existing = Location::count();
        if ($existing >= count(self::CANADIAN_CITIES)) {
            $this->command->info("  ✓ {$existing} locations already exist. Skipping.");
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar(count(self::CANADIAN_CITIES));
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Creating locations...');
        $bar->start();

        foreach (self::CANADIAN_CITIES as $city) {
            Location::firstOrCreate(
                ['city' => $city['city']],
                [
                    'country' => 'Canada',
                    'latitude' => $city['lat'],
                    'longitude' => $city['lng'],
                    'is_active' => true,
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info("  ✓ " . count(self::CANADIAN_CITIES) . " locations ready.");
    }

    // ──────────────────────────────────────────────
    //  Phase 2 — Category seed
    // ──────────────────────────────────────────────

    private function seedCategories(): void
    {
        $existing = Category::count();
        if ($existing >= 20) {
            $this->command->info("  ✓ {$existing} categories already exist. Skipping.");
            return;
        }

        $sections = ['Construction', 'Home Services', 'Technology', 'Automotive', 'Design'];
        $bar = $this->command->getOutput()->createProgressBar(count($sections));
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Seeding category hierarchy...');
        $bar->start();

        foreach ($sections as $sectionName) {
            $section = Category::firstOrCreate(
                ['name' => $sectionName],
                [
                    'slug' => \Illuminate\Support\Str::slug($sectionName),
                    'is_section' => true,
                    'is_active' => true,
                    'sort_order' => fake()->numberBetween(1, 10),
                ]
            );

            foreach (self::PROFESSIONS as $i => $prof) {
                Category::firstOrCreate(
                    ['name' => $prof['name_en']],
                    [
                        'slug' => \Illuminate\Support\Str::slug($prof['name_en']),
                        'is_section' => false,
                        'parent_id' => $section->id,
                        'is_active' => true,
                        'sort_order' => $i + 1,
                    ]
                );
            }
            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info('  ✓ Category hierarchy seeded.');
    }

    // ──────────────────────────────────────────────
    //  Phase 3 — Admin user
    // ──────────────────────────────────────────────

    private function seedAdminUser(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@speeda.ca'],
            [
                'name' => 'Admin Speeda',
                'password' => bcrypt('Admin@123456'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $this->command->info('  ✓ Admin user (admin@speeda.ca / Admin@123456)');
    }

    // ──────────────────────────────────────────────
    //  Phase 4 — 50 Service Providers
    // ──────────────────────────────────────────────

    private function seedServiceProviders(): void
    {
        $existingTestProviders = ServiceProvider::whereHas('user', fn($q) =>
            $q->where('email', 'like', '%@test-speeda.ca')
        )->count();

        if ($existingTestProviders >= 40) {
            $this->command->info("  ✓ {$existingTestProviders} test providers exist. Skipping.");
            return;
        }

        $locationIds = Location::pluck('id')->toArray();
        $categoryIds = Category::where('is_active', true)->where('is_section', false)->pluck('id')->toArray();

        if (empty($locationIds) || empty($categoryIds)) {
            $this->command->error('  ✗ Locations or categories missing. Run LocationSeeder & CategorySeeder first.');
            return;
        }

        $this->command->info('  Creating 50 service providers (15 complete, 20 partial, 10 minimal, 5 arabic)...');
        $bar = $this->command->getOutput()->createProgressBar(50);
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Providers...');
        $bar->start();

        for ($i = 1; $i <= 50; $i++) {
            $user = User::factory()->testData('provider', $i)->serviceProvider()->create();

            $state = match (true) {
                $i <= 15  => 'complete',
                $i <= 35  => 'partial',
                $i <= 45  => 'minimal',
                default   => 'arabic',
            };

            ServiceProvider::factory()
                ->{$state}()
                ->forUser($user->id)
                ->forCategory($categoryIds[array_rand($categoryIds)])
                ->forLocation($locationIds[array_rand($locationIds)])
                ->create(['rating' => fake()->randomFloat(1, 1, 5)]);

            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info('  ✓ 50 service providers created.');
    }

    // ──────────────────────────────────────────────
    //  Phase 5 — 15 Clients
    // ──────────────────────────────────────────────

    private function seedClients(): void
    {
        $existing = User::where('email', 'like', '%@test-speeda.ca')->where('role', 'client')->count();
        if ($existing >= 15) {
            $this->command->info("  ✓ {$existing} test clients exist. Skipping.");
            return;
        }

        $this->command->info('  Creating 15 client users (10 en, 5 arabic)...');
        $bar = $this->command->getOutput()->createProgressBar(15);
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Clients...');
        $bar->start();

        for ($i = 1; $i <= 15; $i++) {
            $factory = User::factory()->testData('client', $i)->client();
            if ($i > 10) {
                $factory->arabic();
            }
            $factory->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info('  ✓ 15 clients created.');
    }

    // ──────────────────────────────────────────────
    //  Phase 6 — Reviews
    // ──────────────────────────────────────────────

    private function seedReviews(): void
    {
        $targetCount = 120;
        $existing = Review::whereHas('client', fn($q) =>
            $q->where('email', 'like', '%@test-speeda.ca')
        )->count();

        if ($existing >= $targetCount) {
            $this->command->info("  ✓ {$existing} reviews exist. Skipping.");
            return;
        }

        $providers = ServiceProvider::whereHas('user', fn($q) =>
            $q->where('email', 'like', '%@test-speeda.ca')
        )->pluck('id')->toArray();

        $clients = User::where('email', 'like', '%@test-speeda.ca')
            ->where('role', 'client')
            ->pluck('id')->toArray();

        if (empty($providers) || empty($clients)) {
            $this->command->warn('  ⚠ Providers or clients missing. Skipping reviews.');
            return;
        }

        $admin = User::where('role', 'admin')->first();
        $remaining = $targetCount - $existing;
        $this->command->info("  Creating {$remaining} more reviews ({$existing} existing)...");

        $bar = $this->command->getOutput()->createProgressBar($remaining);
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Reviews...');
        $bar->start();

        $counts = ['approved' => 0, 'pending' => 0, 'rejected' => 0];

        for ($i = 1; $i <= $remaining; $i++) {
            $providerId = $providers[array_rand($providers)];
            $clientId = $clients[array_rand($clients)];

            $totalAfter = $existing + $i;
            $state = match (true) {
                $totalAfter <= 70  => 'approved',
                $totalAfter <= 100 => 'pending',
                default            => 'rejected',
            };

            // Avoid duplicate client+provider
            $dup = Review::where('service_provider_id', $providerId)
                ->where('client_id', $clientId)
                ->exists();

            if ($dup) {
                $clientId = $clients[array_rand($clients)];
            }

            $review = Review::factory()
                ->{$state}()
                ->forProvider($providerId)
                ->byClient($clientId)
                ->create();

            if ($admin && in_array($state, ['approved', 'rejected'])) {
                $review->update(['admin_approved_by' => $admin->id]);
            }

            $counts[$state]++;
            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');

        $this->command->info('  Recalculating provider ratings...');
        foreach ($providers as $pid) {
            $provider = ServiceProvider::find($pid);
            if ($provider) {
                $provider->recalculateRating();
            }
        }

        $this->command->info("  ✓ Reviews: {$counts['approved']} approved, {$counts['pending']} pending, {$counts['rejected']} rejected (total ~{$targetCount}).");
    }

    // ──────────────────────────────────────────────
    //  Phase 7 — Endorsements
    // ──────────────────────────────────────────────

    private function seedEndorsements(): void
    {
        $existing = Endorsement::count();
        if ($existing > 0) {
            $this->command->info("  ✓ {$existing} endorsements exist. Skipping.");
            return;
        }

        $providers = ServiceProvider::whereHas('user', fn($q) =>
            $q->where('email', 'like', '%@test-speeda.ca')
        )->pluck('id')->toArray();

        $clients = User::where('email', 'like', '%@test-speeda.ca')
            ->where('role', 'client')
            ->pluck('id')->toArray();

        if (empty($providers) || empty($clients)) {
            $this->command->warn('  ⚠ Providers or clients missing. Skipping endorsements.');
            return;
        }

        $target = min(count($providers) * 2, 60);
        $this->command->info("  Creating {$target} endorsements...");
        $bar = $this->command->getOutput()->createProgressBar($target);
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Endorsements...');
        $bar->start();

        $inserts = [];
        $seen = [];

        for ($i = 0; $i < $target; $i++) {
            $pid = $providers[array_rand($providers)];
            $cid = $clients[array_rand($clients)];
            $key = "{$pid}_{$cid}";

            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $inserts[] = [
                'service_provider_id' => $pid,
                'user_id' => $cid,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $bar->advance();
        }

        if (!empty($inserts)) {
            DB::table('endorsements')->insert($inserts);
            // Update endorsement counts
            foreach (array_count_values(array_column($inserts, 'service_provider_id')) as $pid => $count) {
                ServiceProvider::where('id', $pid)->increment('endorsement_count', $count);
            }
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info('  ✓ Endorsements created.');
    }

    // ──────────────────────────────────────────────
    //  Phase 8 — Analytics
    // ──────────────────────────────────────────────

    private function seedAnalytics(): void
    {
        $target = 300;
        $existing = ProviderAnalytics::count();

        if ($existing >= $target) {
            $this->command->info("  ✓ {$existing} analytics records exist. Skipping.");
            return;
        }

        $providers = ServiceProvider::whereHas('user', fn($q) =>
            $q->where('email', 'like', '%@test-speeda.ca')
        )->pluck('id')->toArray();

        if (empty($providers)) {
            $this->command->warn('  ⚠ No test providers. Skipping analytics.');
            return;
        }

        $remaining = $target - $existing;
        $this->command->info("  Creating {$remaining} analytics records...");
        $bar = $this->command->getOutput()->createProgressBar($remaining);
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Analytics...');
        $bar->start();

        for ($i = 0; $i < $remaining; $i++) {
            $pid = $providers[array_rand($providers)];
            ProviderAnalytics::factory()
                ->forProvider($pid)
                ->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info("  ✓ {$remaining} analytics records created (total ~{$target}).");
    }

    // ──────────────────────────────────────────────
    //  Phase 9 — Blog Posts
    // ──────────────────────────────────────────────

    private function seedPosts(): void
    {
        $target = 12;
        $existing = Post::count();

        if ($existing >= $target) {
            $this->command->info("  ✓ {$existing} posts exist. Skipping.");
            return;
        }

        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->warn('  ⚠ No admin user. Skipping posts.');
            return;
        }

        $remaining = $target - $existing;
        $totalAfter = $existing;
        $this->command->info("  Creating {$remaining} blog posts...");
        $bar = $this->command->getOutput()->createProgressBar($remaining);
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Posts...');
        $bar->start();

        for ($i = 1; $i <= $remaining; $i++) {
            $totalAfter++;
            $factory = Post::factory()->authoredBy($admin->id);
            if ($totalAfter <= 10) {
                $factory->published();
            } else {
                $factory->draft();
            }
            if ($totalAfter > 8) {
                $factory->arabic();
            }
            $factory->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info("  ✓ ~{$target} total posts.");
    }

    // ──────────────────────────────────────────────
    //  Phase 10 — Admin Notifications
    // ──────────────────────────────────────────────

    private function seedNotifications(): void
    {
        $target = 10;
        $existing = AdminNotification::count();

        if ($existing >= $target) {
            $this->command->info("  ✓ {$existing} notifications exist. Skipping.");
            return;
        }

        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->warn('  ⚠ No admin user. Skipping notifications.');
            return;
        }

        $remaining = $target - $existing;
        $totalAfter = $existing;
        $this->command->info("  Creating {$remaining} admin notifications...");
        $bar = $this->command->getOutput()->createProgressBar($remaining);
        $bar->setFormat('  %current%/%max% [%bar%] %message%');
        $bar->setMessage('Notifications...');
        $bar->start();

        for ($i = 1; $i <= $remaining; $i++) {
            $totalAfter++;
            $factory = AdminNotification::factory()->createdBy($admin->id);
            if ($totalAfter <= $target - 2) {
                $factory->active();
            } else {
                $factory->expired();
            }
            if ($totalAfter === $existing + 1) {
                $factory->urgent();
            }
            $factory->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info("  ✓ ~{$target} total notifications.");
    }

    // ──────────────────────────────────────────────
    //  Summary
    // ──────────────────────────────────────────────

    private function printSummary(): void
    {
        $providers = ServiceProvider::whereHas('user', fn($q) =>
            $q->where('email', 'like', '%@test-speeda.ca')
        )->count();

        $users = User::where('email', 'like', '%@test-speeda.ca')->count();
        $reviews = Review::count();
        $endorsements = Endorsement::count();
        $analytics = ProviderAnalytics::count();
        $posts = Post::count();
        $notifications = AdminNotification::count();

        $this->command->line('');
        $this->command->info('=== Summary ===');
        $this->command->info("  Users (test):         {$users}");
        $this->command->info("  Service Providers:    {$providers}");
        $this->command->info("  Reviews:              {$reviews}");
        $this->command->info("  Endorsements:         {$endorsements}");
        $this->command->info("  Analytics records:    {$analytics}");
        $this->command->info("  Blog posts:           {$posts}");
        $this->command->info("  Admin notifications:  {$notifications}");
        $this->command->line('');
        $this->command->info('🧹 Cleanup: php artisan test-data:cleanup (wipes @test-speeda.ca users)');
    }

    // ──────────────────────────────────────────────
    //  Static helpers (kept for compatibility)
    // ──────────────────────────────────────────────

    public static function wipeTestData(): array
    {
        $testUserIds = User::where('email', 'like', '%@test-speeda.ca')
            ->pluck('id')
            ->toArray();

        if (empty($testUserIds)) {
            return [
                'success' => true,
                'message' => 'No test users found to clean up.',
                'deleted_users' => 0,
                'deleted_providers' => 0,
            ];
        }

        $userCount = count($testUserIds);

        Endorsement::whereIn('user_id', $testUserIds)->delete();
        Review::whereIn('client_id', $testUserIds)->delete();
        ProviderAnalytics::whereIn('provider_id', function ($q) use ($testUserIds) {
            $q->select('id')->from('service_providers')->whereIn('user_id', $testUserIds);
        })->delete();

        $providerCount = ServiceProvider::whereIn('user_id', $testUserIds)->count();
        ServiceProvider::whereIn('user_id', $testUserIds)->delete();
        User::whereIn('id', $testUserIds)->delete();

        return [
            'success' => true,
            'message' => "Wiped {$userCount} users, {$providerCount} providers, and related data.",
            'deleted_users' => $userCount,
            'deleted_providers' => $providerCount,
        ];
    }

    public static function getTestUserCount(): int
    {
        return User::where('email', 'like', '%@test-speeda.ca')->count();
    }
}
