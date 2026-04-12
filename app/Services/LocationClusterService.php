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

    private const NAMED_CLUSTERS = [
        'cluster_montreal' => ['laval', 'montreal'],
        'cluster_ottawa' => ['ottawa', 'gatineau'],
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
     * Resolve the public named cluster keys used by the listing filter.
     *
     * @return array<int>
     */
    // @change 2026-04-12 TASK-3 | Added named cluster lookup for public listing filters | Support two fixed dropdown values without changing legacy city-based filtering | risk:LOW
    public function getClusterIdsByKey(string $clusterKey): array
    {
        $normalizedKey = strtolower(trim($clusterKey));

        if (!isset(self::NAMED_CLUSTERS[$normalizedKey])) {
            return [];
        }

        return $this->resolveClusterCities(self::NAMED_CLUSTERS[$normalizedKey]);
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
        $clusterIds = $this->resolveClusterCities($clusterCities);

        // Ensure the original location is always included
        if (!in_array($locationId, $clusterIds)) {
            $clusterIds[] = $locationId;
        }

        return $clusterIds;
    }

    /**
     * @param array<int, string> $clusterCities
     * @return array<int>
     */
    private function resolveClusterCities(array $clusterCities): array
    {
        return Location::where('is_active', true)
            ->get()
            ->filter(function ($loc) use ($clusterCities) {
                return in_array(strtolower(trim($loc->city)), $clusterCities, true);
            })
            ->pluck('id')
            ->toArray();
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
