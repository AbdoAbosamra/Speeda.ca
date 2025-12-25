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
            LocationSeeder::class,
        ]);

        $this->command->info('🎉 Database seeded successfully!');
        $this->command->info('   ✅ Categories: 57 (7 sections + 50 categories)');
        $this->command->info('   ✅ Locations seeded');
    }
}
