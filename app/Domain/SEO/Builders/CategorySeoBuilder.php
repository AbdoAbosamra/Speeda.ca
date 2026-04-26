<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;
use App\Models\Category;

class CategorySeoBuilder extends BaseSeoBuilder
{
    public function build(?object $category = null): SeoData
    {
        /** @var Category $category */
        if (!$category) {
            return $this->buildFallback();
        }

        $name = $category->translated_name;
        
        return new SeoData(
            title: __('seo.category_title', ['name' => $name]) . ' | ' . config('app.name'),
            description: __('seo.category_description', ['name' => $name]),
            keywords: $name . ', ' . __('seo.category_keywords'),
            canonical: $this->getCanonicalUrl(),
            hreflangs: $this->getHreflangs()
        );
    }

    protected function buildFallback(): SeoData
    {
        return new SeoData(
            title: __('seo.categories_all') . ' | ' . config('app.name'),
            description: __('seo.categories_description'),
            canonical: $this->getCanonicalUrl(),
            hreflangs: $this->getHreflangs()
        );
    }
}
