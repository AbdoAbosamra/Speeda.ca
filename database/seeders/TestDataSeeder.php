<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * TestDataSeeder - Seeds 60 Canadian-localized test users for Speeda.ca
 *
 * Creates:
 * - 50 Service Providers with profiles
 * - 10 Clients
 * All users use @test-speeda.ca domain for easy cleanup
 */
class TestDataSeeder extends Seeder
{
    /**
     * Canadian cities with their provinces for realistic localization
     */
    private const CANADIAN_CITIES = [
        ['city' => 'Toronto', 'province' => 'ON', 'area_codes' => ['416', '647', '437']],
        ['city' => 'Vancouver', 'province' => 'BC', 'area_codes' => ['604', '778', '236']],
        ['city' => 'Ottawa', 'province' => 'ON', 'area_codes' => ['613', '819']],
        ['city' => 'Montreal', 'province' => 'QC', 'area_codes' => ['514', '438', '450']],
        ['city' => 'Calgary', 'province' => 'AB', 'area_codes' => ['403', '587', '825']],
        ['city' => 'Edmonton', 'province' => 'AB', 'area_codes' => ['780', '587']],
        ['city' => 'Quebec City', 'province' => 'QC', 'area_codes' => ['418', '581']],
        ['city' => 'Winnipeg', 'province' => 'MB', 'area_codes' => ['204', '431']],
        ['city' => 'Hamilton', 'province' => 'ON', 'area_codes' => ['905', '289', '365']],
        ['city' => 'Kitchener', 'province' => 'ON', 'area_codes' => ['519', '226', '548']],
        ['city' => 'London', 'province' => 'ON', 'area_codes' => ['519', '226']],
        ['city' => 'Halifax', 'province' => 'NS', 'area_codes' => ['902']],
    ];

    /**
     * Professional service categories for providers
     */
    private const PROFESSIONS = [
        'Plumber', 'Electrician', 'HVAC Technician', 'Carpenter', 'Painter',
        'Landscaper', 'Roofing Specialist', 'General Contractor', 'Interior Designer',
        'Photographer', 'Web Developer', 'Marketing Consultant', 'Accountant',
        'Financial Advisor', 'Lawyer', 'Real Estate Agent', 'Insurance Broker',
        'Personal Trainer', 'Yoga Instructor', 'Massage Therapist', 'Nutritionist',
        'Tutor', 'Translator', 'Driver', 'Cleaner', 'Babysitter',
        'Chef', 'Caterer', 'Event Planner', 'DJ', 'Musician',
        'Mobile Mechanic', 'Auto Detailer', 'Towing Service', 'Pet Groomer',
        'Dog Walker', 'Handyman', 'Pool Cleaner', 'Security Guard', 'Notary'
    ];

    /**
     * Company name prefixes and suffixes for realistic business names
     */
    private const COMPANY_PREFIXES = ['Expert', 'Pro', 'Elite', 'Premier', 'Master', 'Superior', 'Advanced', 'Quality'];
    private const COMPANY_SUFFIXES = ['Services', 'Solutions', 'Experts', 'Pros', 'Group', 'Ltd', 'Inc'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Speeda.ca Test Data Seeder...');
        $this->command->info('Target: 60 users (50 providers, 10 clients) with Canadian localization');

        // Ensure categories exist before seeding providers
        $this->ensureCategoriesExist();

        // Create 50 Service Providers
        $this->command->info('Creating 50 Service Providers...');
        $providers = $this->createServiceProviders(50);
        $this->command->info("Created {$providers} service providers with profiles.");

        // Create 10 Clients
        $this->command->info('Creating 10 Clients...');
        $clients = $this->createClients(10);
        $this->command->info("Created {$clients} clients.");

        $total = $providers + $clients;
        $this->command->info("✅ Seeding complete! Total: {$total} test users created.");
        $this->command->info("📧 All users have @test-speeda.ca email domain for easy cleanup.");
        $this->command->info("🧹 To clean up: php artisan test-data:cleanup");
    }

    /**
     * Create service providers with full profiles
     */
    private function createServiceProviders(int $count): int
    {
        $faker = \Faker\Factory::create('en_CA');
        $categories = Category::whereNotNull('parent_id')->where('is_active', true)->pluck('id')->toArray();
        $locations = \App\Models\Location::pluck('id')->toArray();

        if (empty($categories)) {
            $this->command->warn('No subcategories found! Creating providers without category assignment.');
            $categories = [null];
        }

        if (empty($locations)) {
            $locations = [1, 2, 3, 4]; // Default to first 4 locations
        }

        $created = 0;

        for ($i = 1; $i <= $count; $i++) {
            $cityData = $this->getRandomCanadianCity($faker);
            $profession = $faker->randomElement(self::PROFESSIONS);
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;

            // Create User
            $user = User::create([
                'name' => "{$firstName} {$lastName}",
                'email' => $this->generateTestEmail("provider", $i),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'service_provider',
                'is_active' => true,
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ]);

            // Create Service Provider Profile
            ServiceProvider::create([
                'user_id' => $user->id,
                'category_id' => $faker->randomElement($categories),
                'location_id' => $faker->randomElement($locations),
                'company_name' => $this->generateCompanyName($faker, $profession),
                'bio' => $faker->paragraph(3),
                'address' => $this->generateCanadianAddress($faker, $cityData),
                'experience_years' => $faker->numberBetween(1, 25),
                'hourly_rate' => $faker->randomFloat(2, 25, 200),
                'emergency_available' => $faker->boolean(30),
                'available_weekends' => $faker->boolean(60),
                'available_evenings' => $faker->boolean(50),
                'response_time_hours' => $faker->numberBetween(1, 48),
                'languages' => $faker->randomElements(['en', 'fr', 'ar'], $faker->numberBetween(1, 2)),
                'specializations' => $faker->words(3),
                'services_offered' => $faker->paragraph(2),
                'phone' => $this->generateCanadianPhone($cityData['area_codes'], $faker),
                'whatsapp_number' => $faker->boolean(70) ? $this->generateCanadianPhone($cityData['area_codes'], $faker) : null,
                'is_verified' => $faker->boolean(40),
                'is_certified' => $faker->boolean(25),
                'certification' => $faker->boolean(30) ? $faker->randomElement(['Licensed', 'Certified', 'Bonded']) : null,
                'business_type' => $faker->randomElement(['individual', 'company']),
                'business_license' => $faker->optional(0.3)->regexify('[A-Z]{2}[0-9]{6}'),
                'rating' => $faker->randomFloat(1, 1, 5),
                'total_reviews' => $faker->numberBetween(0, 50),
                'completed_jobs' => $faker->numberBetween(0, 100),
                'views' => $faker->numberBetween(0, 500),
                'profile_completion_percent' => $faker->numberBetween(60, 100),
                'created_at' => $user->created_at,
                'updated_at' => now(),
            ]);

            $created++;

            if ($i % 10 === 0) {
                $this->command->info("  Progress: {$i}/{$count} providers created...");
            }
        }

        return $created;
    }

    /**
     * Create client users
     */
    private function createClients(int $count): int
    {
        $faker = \Faker\Factory::create('en_CA');
        $created = 0;

        for ($i = 1; $i <= $count; $i++) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;

            User::create([
                'name' => "{$firstName} {$lastName}",
                'email' => $this->generateTestEmail("client", $i),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'client',
                'is_active' => true,
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * Ensure minimum categories exist
     */
    private function ensureCategoriesExist(): void
    {
        $categoryCount = Category::count();

        if ($categoryCount === 0) {
            $this->command->warn('No categories found! Running CategorySeeder first...');
            $this->call(CategorySeeder::class);
        } else {
            $this->command->info("✓ Found {$categoryCount} categories.");
        }

        // Ensure locations exist
        $locationCount = \App\Models\Location::count();
        if ($locationCount === 0) {
            $this->command->warn('No locations found! Creating default locations...');
            $this->createDefaultLocations();
        }
    }

    /**
     * Create default Canadian locations if none exist
     */
    private function createDefaultLocations(): void
    {
        $locations = [
            ['city' => 'Toronto', 'province' => 'ON'],
            ['city' => 'Vancouver', 'province' => 'BC'],
            ['city' => 'Ottawa', 'province' => 'ON'],
            ['city' => 'Montreal', 'province' => 'QC'],
        ];

        foreach ($locations as $loc) {
            \App\Models\Location::create([
                'city' => $loc['city'],
                'province' => $loc['province'],
                'country' => 'Canada',
                'is_active' => true,
            ]);
        }

        $this->command->info('Created 4 default Canadian locations.');
    }

    /**
     * Generate unique test email with @test-speeda.ca domain
     */
    private function generateTestEmail(string $type, int $index): string
    {
        $uniqueId = Str::random(6);
        return "{$type}.{$index}.{$uniqueId}@test-speeda.ca";
    }

    /**
     * Get random Canadian city with area codes
     */
    private function getRandomCanadianCity($faker): array
    {
        return $faker->randomElement(self::CANADIAN_CITIES);
    }

    /**
     * Generate realistic Canadian company name
     */
    private function generateCompanyName($faker, string $profession): string
    {
        $prefix = $faker->randomElement(self::COMPANY_PREFIXES);
        $suffix = $faker->randomElement(self::COMPANY_SUFFIXES);

        return $faker->randomElement([
            "{$prefix} {$profession} {$suffix}",
            "{$profession} {$suffix}",
            "{$prefix} {$profession}",
            "{$faker->lastName} {$profession} {$suffix}",
        ]);
    }

    /**
     * Generate Canadian address with postal code
     */
    private function generateCanadianAddress($faker, array $cityData): string
    {
        $streetNumber = $faker->numberBetween(1, 9999);
        $streetName = $faker->streetName;
        $postalCode = $this->generateCanadianPostalCode($faker);

        return "{$streetNumber} {$streetName}, {$cityData['city']}, {$cityData['province']} {$postalCode}";
    }

    /**
     * Generate valid Canadian postal code (Format: A1B 2C3 or A1B2C3)
     */
    private function generateCanadianPostalCode($faker): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTVWXYZ'; // Excludes I, O, Q to avoid confusion
        $format = $faker->randomElement(['A1B 2C3', 'A1B2C3']);

        $postal = '';
        for ($i = 0; $i < strlen($format); $i++) {
            $char = $format[$i];
            if ($char === 'A') {
                $postal .= $letters[$faker->numberBetween(0, strlen($letters) - 1)];
            } elseif ($char === '1') {
                $postal .= $faker->numberBetween(0, 9);
            } elseif ($char === ' ') {
                $postal .= ' ';
            }
        }

        return $postal;
    }

    /**
     * Generate Canadian phone number with proper area code
     */
    private function generateCanadianPhone(array $areaCodes, $faker): string
    {
        $areaCode = $faker->randomElement($areaCodes);
        $exchange = $faker->numberBetween(200, 999); // Cannot start with 0 or 1
        $lineNumber = $faker->numberBetween(1000, 9999);

        return "+1 ({$areaCode}) {$exchange}-{$lineNumber}";
    }

    /**
     * Wipe all test data - Can be called externally or via command
     */
    public static function wipeTestData(): array
    {
        // Find all test users
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

        // Delete related ServiceProvider profiles first
        $providerCount = ServiceProvider::whereIn('user_id', $testUserIds)->count();
        ServiceProvider::whereIn('user_id', $testUserIds)->delete();

        // Delete test users
        User::whereIn('id', $testUserIds)->delete();

        return [
            'success' => true,
            'message' => "Successfully wiped {$userCount} test users and {$providerCount} provider profiles.",
            'deleted_users' => $userCount,
            'deleted_providers' => $providerCount,
        ];
    }

    /**
     * Get count of existing test users
     */
    public static function getTestUserCount(): int
    {
        return User::where('email', 'like', '%@test-speeda.ca')->count();
    }
}
