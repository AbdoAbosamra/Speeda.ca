<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SIGNUP_LOCATIONS = [
        'Montreal' => 'Quebec',
        'Laval' => 'Quebec',
        'Gatineau' => 'Quebec',
        'Ottawa' => 'Ontario',
        'Mississauga' => 'Ontario',
        'Brampton' => 'Ontario',
        'Oakville' => 'Ontario',
        'Burlington' => 'Ontario',
        'Milton' => 'Ontario',
        'Markham' => 'Ontario',
        'Vaughan' => 'Ontario',
        'Richmond Hill' => 'Ontario',
        'Oshawa' => 'Ontario',
        'Whitby' => 'Ontario',
        'Ajax' => 'Ontario',
        'City of Toronto' => 'Ontario',
    ];

    public function up(): void
    {
        foreach (self::SIGNUP_LOCATIONS as $city => $province) {
            $exists = DB::table('locations')->where('city', $city)->exists();

            if ($exists) {
                DB::table('locations')
                    ->where('city', $city)
                    ->update([
                        'is_active' => true,
                        'country' => 'Canada',
                        'area' => $province,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('locations')->insert([
                'city' => $city,
                'is_active' => true,
                'country' => 'Canada',
                'area' => $province,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->clearLocationCache();
    }

    public function down(): void
    {
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
