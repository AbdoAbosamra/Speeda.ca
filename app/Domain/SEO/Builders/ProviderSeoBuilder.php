<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;
use App\Models\ServiceProvider;

class ProviderSeoBuilder extends BaseSeoBuilder
{
    public function build(?object $provider = null): SeoData
    {
        /** @var ServiceProvider $provider */
        if (!$provider) {
            return new SeoData(title: config('app.name'));
        }

        $name = $provider->company_name ?? $provider->user?->name;
        $category = $provider->category?->translated_name;
        $city = $provider->location?->city;

        $canonicalUrl = route('service-providers.show', $provider);
        $locales = config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []]);
        $hreflangs = [];
        foreach (array_keys($locales) as $locale) {
            $hreflangs[$locale] = route('service-providers.show', [$provider, 'lang' => $locale]);
        }

        return new SeoData(
            title: $name . ' - ' . $category . ' ' . $city . ' | ' . config('app.name'),
            description: $provider->bio ? mb_substr(strip_tags($provider->bio), 0, 160) : null,
            ogImage: $provider->display_image_url,
            ogType: 'profile',
            canonical: $canonicalUrl,
            hreflangs: $hreflangs
        );
    }
}
