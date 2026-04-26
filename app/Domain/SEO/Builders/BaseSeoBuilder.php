<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;
use Illuminate\Support\Facades\Request;

abstract class BaseSeoBuilder
{
    abstract public function build(?object $model = null): SeoData;

    protected function getCanonicalUrl(): string
    {
        return Request::url();
    }

    protected function getHreflangs(): array
    {
        $locales = config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []]);
        $hreflangs = [];

        foreach (array_keys($locales) as $locale) {
            $hreflangs[$locale] = Request::fullUrlWithQuery(['lang' => $locale]);
        }

        return $hreflangs;
    }
}
