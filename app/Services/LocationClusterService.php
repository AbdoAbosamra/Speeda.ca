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
        // Québec clusters (bidirectional)
        'laval'    => ['laval', 'montreal'],
        'montreal' => ['montreal', 'laval'],
        // National Capital — split into standalone cities (each returns only itself)
        'gatineau' => ['gatineau'],
        'ottawa'   => ['ottawa'],
        // Ontario — each city is its own cluster (no cross-city grouping at row level)
        'mississauga'     => ['mississauga'],
        'brampton'        => ['brampton'],
        'oakville'        => ['oakville', 'burlington', 'milton'],
        'burlington'      => ['oakville', 'burlington', 'milton'],
        'milton'          => ['oakville', 'burlington', 'milton'],
        'markham'         => ['markham', 'vaughan', 'richmond hill'],
        'vaughan'         => ['markham', 'vaughan', 'richmond hill'],
        'richmond hill'   => ['markham', 'vaughan', 'richmond hill'],
        'oshawa'          => ['oshawa', 'whitby', 'ajax'],
        'whitby'          => ['oshawa', 'whitby', 'ajax'],
        'ajax'            => ['oshawa', 'whitby', 'ajax'],
        'city of toronto' => ['city of toronto'],
    ];

    /**
     * Named cluster keys used by the public listing filter dropdown.
     * Each key maps to the set of city names it covers.
     *
     * @change 2026-06-07 | Added 6 Ontario GTA clusters for /service-providers filter | risk:LOW
     */
    private const NAMED_CLUSTERS = [
        // Original clusters
        'cluster_montreal' => ['laval', 'montreal'],
        // National Capital — split into two standalone filter options
        'cluster_ottawa'   => ['ottawa'],
        'cluster_gatineau' => ['gatineau'],
        // Ontario GTA clusters — 6 groups as specified
        'cluster_mississauga'           => ['mississauga'],
        'cluster_brampton'              => ['brampton'],
        'cluster_oakville_burlington_milton' => ['oakville', 'burlington', 'milton'],
        'cluster_markham_vaughan_richmond_hill' => ['markham', 'vaughan', 'richmond hill'],
        'cluster_oshawa_whitby_ajax'    => ['oshawa', 'whitby', 'ajax'],
        'cluster_city_of_toronto'       => ['city of toronto'],
    ];

    private const PUBLIC_FILTER_CLUSTERS = [
        'cluster_montreal'                      => 'Laval – Montréal',
        'cluster_ottawa'                        => 'Ottawa',
        'cluster_gatineau'                      => 'Gatineau',
        'cluster_mississauga'                   => 'Mississauga',
        'cluster_brampton'                      => 'Brampton',
        'cluster_oakville_burlington_milton'    => 'Oakville – Burlington – Milton',
        'cluster_markham_vaughan_richmond_hill' => 'Markham – Vaughan – Richmond Hill',
        'cluster_oshawa_whitby_ajax'            => 'Oshawa – Whitby – Ajax',
        'cluster_city_of_toronto'               => 'City of Toronto',
    ];

    /**
     * Public location filter choices shared by home and service provider listings.
     *
     * @return array<string, string>
     */
    public function getPublicFilterClusters(): array
    {
        return self::PUBLIC_FILTER_CLUSTERS;
    }

    /**
     * The set of city names (lowercased) that are reachable through the public
     * location filter. Anything outside this set cannot be filtered, so it is
     * not offered when a provider picks additional service areas.
     *
     * @return array<int, string>
     */
    public function getFilterableCityNames(): array
    {
        $cities = [];
        foreach (array_keys(self::PUBLIC_FILTER_CLUSTERS) as $key) {
            $cities = array_merge($cities, self::NAMED_CLUSTERS[$key] ?? []);
        }

        return array_values(array_unique($cities));
    }

    /**
     * Active location IDs whose city participates in the public filter.
     * Used to both render and validate the provider service-area picker.
     *
     * @return array<int>
     */
    public function getFilterableLocationIds(): array
    {
        $cities = $this->getFilterableCityNames();

        return Location::where('is_active', true)
            ->get()
            ->filter(fn ($loc) => in_array(strtolower(trim((string) $loc->city)), $cities, true))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Active locations whose city participates in the public filter,
     * sorted by display name for the picker.
     *
     * @return \Illuminate\Support\Collection<int, \App\Models\Location>
     */
    public function getFilterableLocations(): \Illuminate\Support\Collection
    {
        $cities = $this->getFilterableCityNames();

        return Location::where('is_active', true)
            ->get()
            ->filter(fn ($loc) => in_array(strtolower(trim((string) $loc->city)), $cities, true))
            ->sortBy(fn ($loc) => $loc->localized_name ?? $loc->city)
            ->values();
    }

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
