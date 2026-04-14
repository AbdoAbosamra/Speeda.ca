<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            CategorySeeder::class,  // MUST run first (categories before locations)
            SafeCategoryUpdateSeeder::class,
            LocationSeeder::class,
        ]);

        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('   ✅ Categories: 57 (7 sections + 50 categories)');
        $this->command->info('   ✅ Locations seeded');
        $this->command->info('');
        $this->command->info('💡 For test data (60 Canadian users): php artisan db:seed --class=TestDataSeeder');
        $this->command->info('🧹 To clean up test data: php artisan test-data:cleanup');
    }
}
