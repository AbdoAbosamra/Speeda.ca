<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;
use Illuminate\Support\Facades\Request;

abstract class BaseSeoBuilder
{
    abstract public function build(?object $model = null): SeoData;

    protected function getCanonicalUrl(): string
    {
        // Strip common tracking parameters and noise
        $url = Request::url();
        $query = Request::query();

        // Remove tracking / noise parameters
        unset(
            $query['lang'],
            $query['utm_source'],
            $query['utm_medium'],
            $query['utm_campaign'],
            $query['utm_term'],
            $query['utm_content'],
            $query['fbclid'],
            $query['gclid'],
            $query['ref']
        );

        if (isset($query['page']) && (int) $query['page'] <= 1) {
            unset($query['page']);
        }

        return empty($query) ? $url : $url . '?' . http_build_query($query);
    }

    protected function getHreflangs(): array
    {
        $locales = config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []]);
        $hreflangs = [];
        $baseCanonical = $this->getCanonicalUrl();
        $separator = str_contains($baseCanonical, '?') ? '&' : '?';

        foreach (array_keys($locales) as $locale) {
            $hreflangs[$locale] = $baseCanonical . $separator . 'lang=' . $locale;
        }

        return $hreflangs;
    }
}
