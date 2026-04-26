<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;
use Illuminate\Support\Facades\Request;

class SearchSeoBuilder extends BaseSeoBuilder
{
    public function build(?object $model = null): SeoData
    {
        $query = Request::input('search');
        $category = Request::input('category');
        $city = Request::input('city');

        $titleParts = [];
        if ($query) $titleParts[] = '"' . $query . '"';
        if ($category) $titleParts[] = $category;
        if ($city) $titleParts[] = 'in ' . $city;

        $title = !empty($titleParts) 
            ? implode(' ', $titleParts) . ' | ' . config('app.name')
            : __('seo.search_results') . ' | ' . config('app.name');

        return new SeoData(
            title: $title,
            description: __('seo.search_description'),
            robots: 'noindex, follow', // Don't index dynamic search results
            canonical: $this->getCanonicalUrl(),
            hreflangs: $this->getHreflangs()
        );
    }
}
