<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;

class HomeSeoBuilder extends BaseSeoBuilder
{
    public function build(?object $model = null): SeoData
    {
        $locales = config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []]);
        $hreflangs = [];
        foreach (array_keys($locales) as $locale) {
            $hreflangs[$locale] = route('home', ['lang' => $locale]);
        }

        return new SeoData(
            title: __('seo.home_title') . ' | ' . config('app.name'),
            description: __('seo.home_description'),
            keywords: __('seo.home_keywords'),
            ogImage: asset('images/og-home.png'),
            canonical: route('home'),
            hreflangs: $hreflangs
        );
    }
}
