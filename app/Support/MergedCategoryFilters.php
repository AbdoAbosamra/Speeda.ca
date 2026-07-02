<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Merged category filters.
 *
 * Lets the public provider filter expose a single option that spans several
 * underlying categories. The mapping is slug-based (with a deliberately broad
 * slug list) so it keeps working regardless of the exact category tree that is
 * seeded in a given environment.
 */
class MergedCategoryFilters
{
    /**
     * Each merged group renders as ONE filter option (keyed by its filter slug)
     * and resolves to the union of its member categories' providers.
     *
     * @return array<string, array{name:string,name_en:string,name_ar:string,name_fr:string,slugs:array<int,string>}>
     */
    public static function groups(): array
    {
        return [
            'renovation-and-construction' => [
                'name' => 'Renovation and Construction',
                'name_en' => 'Renovation and Construction',
                'name_ar' => 'التجديد والبناء',
                'name_fr' => 'Rénovation et construction',
                // Home Repair & Maintenance + Renovation + Construction — slug
                // variants across the different category seeders are all listed
                // so whichever exists in this environment gets merged.
                'slugs' => [
                    'home-repair-maintenance',
                    'repairs-and-maintenance',
                    'renovation-construction',
                    'home-renovation',
                    'general-construction',
                    'construction-services',
                ],
            ],
        ];
    }

    /**
     * Every member slug across all merged groups (used to hide the individual
     * categories from the filter once they are merged).
     *
     * @return array<int, string>
     */
    public static function memberSlugs(): array
    {
        return collect(static::groups())
            ->flatMap(fn (array $group) => $group['slugs'])
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Whether the submitted filter value is one of the merged group keys.
     */
    public static function isMergedKey(?string $value): bool
    {
        return $value !== null && array_key_exists($value, static::groups());
    }

    /**
     * Provider category IDs for a merged group key: the union of every member
     * category's provider category IDs. Returns [] when no member exists.
     *
     * @return array<int, int>
     */
    public static function providerCategoryIds(string $key): array
    {
        $group = static::groups()[$key] ?? null;

        if (! $group) {
            return [];
        }

        return static::memberCategories($group['slugs'])
            ->flatMap(fn (Category $category) => $category->providerCategoryIds())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Rebuild the filter option list: drop the individual member categories and
     * append one synthetic option per merged group that actually has members.
     */
    public static function apply(Collection $filterGroups): Collection
    {
        $memberSlugs = static::memberSlugs();

        $options = $filterGroups
            ->reject(fn ($category) => in_array($category->slug, $memberSlugs, true))
            ->values();

        foreach (static::groups() as $key => $group) {
            if (static::memberCategories($group['slugs'])->isEmpty()) {
                continue; // nothing to merge in this environment
            }

            $option = new Category();
            $option->id = null;
            $option->slug = $key;
            $option->name = $group['name'];
            $option->name_en = $group['name_en'];
            $option->name_ar = $group['name_ar'];
            $option->name_fr = $group['name_fr'];
            $option->is_section = false;

            $options->push($option);
        }

        return $options->sortBy(fn ($category) => $category->translated_name)->values();
    }

    /**
     * Active categories matching any of the given slugs.
     *
     * @param  array<int, string>  $slugs
     */
    protected static function memberCategories(array $slugs): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->whereIn('slug', $slugs)
            ->get();
    }
}
