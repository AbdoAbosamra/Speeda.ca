<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @change 2026-06-07 | Added 12 Ontario cities for provider registration and GTA cluster filters
 *                     | Additive only — existing locations untouched | risk:LOW
 */
return new class extends Migration
{
    /**
     * The cities to add, in display order.
     */
    private const NEW_CITIES = [
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

    /**
     * Run the migrations.
     * SAFE: Uses updateOrInsert — idempotent and additive.
     * No existing rows are modified or deleted.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        foreach (self::NEW_CITIES as $city) {
            DB::table('locations')->updateOrInsert(
                ['city' => $city],
                [
                    'is_active'  => true,
                    'country'    => 'Canada',
                    'area'       => 'Ontario',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     * NON-DESTRUCTIVE: Only deletes cities added by this migration
     * IF they have no associated providers. Logs and skips otherwise.
     */
    public function down(): void
    {
        foreach (self::NEW_CITIES as $city) {
            $location = DB::table('locations')->where('city', $city)->first();

            if (!$location) {
                continue;
            }

            $hasProviders = DB::table('service_providers')
                ->where('location_id', $location->id)
                ->exists();

            if ($hasProviders) {
                Log::warning("Skipping deletion of location '{$city}' (id={$location->id}): has associated providers.");
                continue;
            }

            DB::table('locations')->where('id', $location->id)->delete();
        }
    }
};
