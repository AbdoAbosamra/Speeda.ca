<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;

class BlogIndexSeoBuilder extends BaseSeoBuilder
{
    public function build(?object $model = null): SeoData
    {
        return new SeoData(
            title: __('blog.index_meta_title', ['app_name' => config('app.name')]),
            description: __('blog.index_meta_description'),
            keywords: __('blog.index_meta_keywords'),
            ogType: 'website',
            canonical: $this->getCanonicalUrl(),
            hreflangs: $this->getHreflangs()
        );
    }
}
