<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * The cities offered during provider registration, in display order.
 *
 * Both the normal and Google signup paths use this class so the UI, city
 * normalization and server-side validation cannot drift apart.
 */
final class SignupLocations
{
    private const GROUPS = [
        'Quebec' => [
            'montreal' => 'Montreal',
            'laval' => 'Laval',
            'gatineau' => 'Gatineau',
        ],
        'Ontario' => [
            'ottawa' => 'Ottawa',
            'mississauga' => 'Mississauga',
            'brampton' => 'Brampton',
            'oakville' => 'Oakville',
            'burlington' => 'Burlington',
            'milton' => 'Milton',
            'markham' => 'Markham',
            'vaughan' => 'Vaughan',
            'richmond hill' => 'Richmond Hill',
            'oshawa' => 'Oshawa',
            'whitby' => 'Whitby',
            'ajax' => 'Ajax',
            'city of toronto' => 'City of Toronto',
        ],
    ];

    public static function normalize(?string $city): string
    {
        $city = trim((string) $city);
        $key = mb_strtolower($city);

        return self::cities()[$key] ?? $city;
    }

    public static function supports(?string $city): bool
    {
        return array_key_exists(mb_strtolower(trim((string) $city)), self::cities());
    }

    /** @return array<string, string> */
    public static function cities(): array
    {
        $cities = [];

        foreach (self::GROUPS as $group) {
            $cities += $group;
        }

        return $cities;
    }

    /**
     * Match the configured signup list to active database locations.
     *
     * @return array<string, Collection<int, object>>
     */
    public static function groupActiveLocations(Collection $locations): array
    {
        $index = $locations->keyBy(fn ($location) => mb_strtolower(trim($location->city)));
        $groups = [];

        foreach (self::GROUPS as $label => $cities) {
            $matched = collect(array_keys($cities))
                ->map(fn ($city) => $index->get($city))
                ->filter()
                ->values();

            if ($matched->isNotEmpty()) {
                $groups[$label] = $matched;
            }
        }

        return $groups;
    }
}
