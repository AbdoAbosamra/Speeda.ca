<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Facades\Cache;

class LocationClusterService
{
    /**
     * City cluster mapping (case-insensitive).
     *
     * Rules from requirements:
     * - Laval      → Laval + Montreal
     * - Montreal   → Montreal + Laval
     * - Gatineau   → Gatineau + Ottawa
     * - Ottawa     → Ottawa + Gatineau
     */
    private const CLUSTERS = [
        'laval'    => ['laval', 'montreal'],
        'montreal' => ['montreal', 'laval'],
        'gatineau' => ['gatineau', 'ottawa'],
        'ottawa'   => ['ottawa', 'gatineau'],
    ];

    /**
     * Get all location IDs that should be included when filtering by a given location.
     *
     * @param int $locationId The selected location ID
     * @return array<int> Array of location IDs (includes the original + cluster partners)
     */
    public function getClusterIds(int $locationId): array
    {
        return Cache::remember(
            "location_cluster_{$locationId}",
            now()->addHours(6),
            function () use ($locationId) {
                return $this->resolveCluster($locationId);
            }
        );
    }

    /**
     * Resolve the cluster for a given location ID by looking up city names.
     */
    private function resolveCluster(int $locationId): array
    {
        $location = Location::find($locationId);

        if (!$location || empty($location->city)) {
            return [$locationId];
        }

        $cityLower = strtolower(trim($location->city));

        // Check if this city has a cluster definition
        if (!isset(self::CLUSTERS[$cityLower])) {
            return [$locationId];
        }

        $clusterCities = self::CLUSTERS[$cityLower];

        // Look up all location IDs matching the cluster cities
        $clusterIds = Location::where('is_active', true)
            ->get()
            ->filter(function ($loc) use ($clusterCities) {
                return in_array(strtolower(trim($loc->city)), $clusterCities);
            })
            ->pluck('id')
            ->toArray();

        // Ensure the original location is always included
        if (!in_array($locationId, $clusterIds)) {
            $clusterIds[] = $locationId;
        }

        return $clusterIds;
    }

    /**
     * Clear the cluster cache (call when locations are modified).
     */
    public function clearCache(): void
    {
        $locations = Location::all();
        foreach ($locations as $location) {
            Cache::forget("location_cluster_{$location->id}");
        }
    }
}
