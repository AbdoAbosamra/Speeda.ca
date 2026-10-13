<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;
use App\Models\Category;

class CategorySeoBuilder extends BaseSeoBuilder
{
    public function build(?object $category = null): SeoData
    {
        /** @var Category|null $category */
        if (!$category) {
            return $this->buildFallback();
        }

        $name = $category->translated_name;
        $canonicalUrl = route('service-providers.index', ['category' => $category->slug]);

        $locales = config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []]);
        $hreflangs = [];
        foreach (array_keys($locales) as $locale) {
            $hreflangs[$locale] = route('service-providers.index', [
                'category' => $category->slug,
                'lang' => $locale,
            ]);
        }

        // Check if category has active providers to avoid thin content / soft 404 indexing
        $hasProviders = \App\Models\ServiceProvider::publiclyVisible()
            ->whereIn('category_id', $category->providerCategoryIds())
            ->exists();

        $robots = $hasProviders ? 'index, follow' : 'noindex, follow';

        return new SeoData(
            title: __('seo.category_title', ['name' => $name]) . ' | ' . config('app.name'),
            description: __('seo.category_description', ['name' => $name]),
            keywords: $name . ', ' . __('seo.category_keywords'),
            robots: $robots,
            canonical: $canonicalUrl,
            hreflangs: $hreflangs
        );
    }

    protected function buildFallback(): SeoData
    {
        $canonicalUrl = request()->routeIs('categories')
            ? route('categories')
            : route('service-providers.index');

        return new SeoData(
            title: __('seo.categories_all') . ' | ' . config('app.name'),
            description: __('seo.categories_description'),
            canonical: $canonicalUrl,
            hreflangs: $this->getHreflangs()
        );
    }
}
