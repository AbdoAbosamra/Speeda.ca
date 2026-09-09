<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(AdminUserSeeder::class);
        if (! \Illuminate\Support\Facades\DB::table('categories')->exists()) {
            $this->call([CategorySeeder::class, SafeCategoryUpdateSeeder::class]);
        }
        $this->call(LocationSeeder::class);

        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('   ✅ Categories: 57 (7 sections + 50 categories)');
        $this->command->info('   ✅ Locations seeded');
        $this->command->info('');
        $this->command->info('💡 For test data (60 Canadian users): php artisan db:seed --class=TestDataSeeder');
        $this->command->info('🧹 To clean up test data: php artisan test-data:cleanup');
    }
}
