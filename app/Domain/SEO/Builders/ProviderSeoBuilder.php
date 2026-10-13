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

        // "Name - Category Ottawa", without stray separators when a part is missing.
        $detail = trim(implode(' ', array_filter([$category, $city])));
        $bio = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $provider->bio)));
        if ($bio === 'Service provider profile. Please complete your details.') {
            $bio = ''; // the signup placeholder, not a description
        }

        return new SeoData(
            title: ($detail !== '' ? $name . ' - ' . $detail : $name) . ' | ' . config('app.name'),
            description: $bio !== '' ? \Illuminate\Support\Str::limit($bio, 157) : __('seo.provider_default_description', ['name' => $name]),
            // Share previews need an absolute URL; the fallback image is a path.
            ogImage: url($provider->display_image_url),
            ogType: 'profile',
            canonical: $canonicalUrl,
            hreflangs: $hreflangs
        );
    }
}
