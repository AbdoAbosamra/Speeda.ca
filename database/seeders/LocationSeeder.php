<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run()
    {
        // Disable query logging to reduce memory usage
        DB::connection()->disableQueryLog();

        $cities = ['Laval', 'Montreal', 'Ottawa', 'Gatineau'];
        $locationData = [];

        foreach ($cities as $city) {
            if (!Location::where('city', $city)->exists()) {
                $locationData[] = [
                    'city' => $city,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($locationData)) {
            Location::insert($locationData);
        }

        // Re-enable query logging
        DB::connection()->enableQueryLog();

        $this->command->info('Locations seeded successfully! Memory usage: ' . (memory_get_usage() / 1024 / 1024) . ' MB');
    }
}
