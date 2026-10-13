<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;

class BlogIndexSeoBuilder extends BaseSeoBuilder
{
    public function build(?object $model = null): SeoData
    {
        $canonicalUrl = route('blogs.index');
        $locales = config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []]);
        $hreflangs = [];
        foreach (array_keys($locales) as $locale) {
            $hreflangs[$locale] = route('blogs.index', ['lang' => $locale]);
        }

        return new SeoData(
            title: __('blog.index_meta_title', ['app_name' => config('app.name')]),
            description: __('blog.index_meta_description'),
            keywords: __('blog.index_meta_keywords'),
            ogType: 'website',
            canonical: $canonicalUrl,
            hreflangs: $hreflangs
        );
    }
}
