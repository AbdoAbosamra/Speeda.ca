<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;

class HomeSeoBuilder extends BaseSeoBuilder
{
    public function build(?object $model = null): SeoData
    {
        return new SeoData(
            title: __('seo.home_title') . ' | ' . config('app.name'),
            description: __('seo.home_description'),
            keywords: __('seo.home_keywords'),
            ogImage: asset('images/og-home.png'),
            canonical: $this->getCanonicalUrl(),
            hreflangs: $this->getHreflangs()
        );
    }
}
