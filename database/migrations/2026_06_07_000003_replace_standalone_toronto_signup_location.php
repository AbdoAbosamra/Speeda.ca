<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const GTA_LOCATIONS = [
        'Mississauga',
        'Brampton',
        'Oakville',
        'Burlington',
        'Milton',
        'Markham',
        'Vaughan',
        'Richmond Hill',
        'Oshawa',
        'Whitby',
        'Ajax',
        'City of Toronto',
    ];

    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        foreach (self::GTA_LOCATIONS as $city) {
            $exists = DB::table('locations')->where('city', $city)->exists();

            if ($exists) {
                DB::table('locations')
                    ->where('city', $city)
                    ->update([
                        'is_active' => true,
                        'country' => 'Canada',
                        'area' => 'Ontario',
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('locations')->insert([
                'city' => $city,
                'is_active' => true,
                'country' => 'Canada',
                'area' => 'Ontario',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('locations')
            ->where('city', 'Toronto')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('service_providers')
                    ->whereColumn('service_providers.location_id', 'locations.id');
            })
            ->delete();

        $this->clearLocationCache();
    }

    public function down(): void
    {
        DB::table('locations')->updateOrInsert(
            ['city' => 'Toronto'],
            [
                'is_active' => true,
                'country' => 'Canada',
                'area' => 'Ontario',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->clearLocationCache();
    }

    private function clearLocationCache(): void
    {
        $configuredLocales = config('app.supported_locales', ['en', 'ar', 'fr']);
        $locales = array_is_list($configuredLocales) ? $configuredLocales : array_keys($configuredLocales);

        foreach ($locales as $locale) {
            foreach (['speeda.location_active', 'speeda.location_all'] as $key) {
                Cache::forget($key . '_' . $locale);

                if (extension_loaded('redis')) {
                    try {
                        Cache::store('redis')->forget($key . '_' . $locale);
                    } catch (Throwable) {
                        // Ignore Redis connection issues during migrations.
                    }
                }
            }
        }
    }
};
