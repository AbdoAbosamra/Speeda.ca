<?php

namespace App\Domain\SEO\Services;

use App\Domain\SEO\Builders\CategorySeoBuilder;
use App\Domain\SEO\Builders\BlogIndexSeoBuilder;
use App\Domain\SEO\Builders\BlogPostSeoBuilder;
use App\Domain\SEO\Builders\HomeSeoBuilder;
use App\Domain\SEO\Builders\ProviderSeoBuilder;
use App\Domain\SEO\Builders\SearchSeoBuilder;
use App\Domain\SEO\DTOs\SeoData;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class SeoMetaService
{
    protected array $builders = [
        'home' => HomeSeoBuilder::class,
        'category' => CategorySeoBuilder::class,
        'provider' => ProviderSeoBuilder::class,
        'search' => SearchSeoBuilder::class,
        'blog_index' => BlogIndexSeoBuilder::class,
        'blog_post' => BlogPostSeoBuilder::class,
    ];

    public function apply(string $type, ?object $model = null): void
    {
        $data = $this->getData($type, $model);
        $this->injectIntoSeoTools($data);
    }

    /**
     * Full meta for a page that has no builder: title, description, canonical,
     * one alternate per language, Open Graph and Twitter. The canonical is
     * passed in (a route URL), never read from the visitor's query string.
     */
    public function applyPage(string $title, ?string $description, string $canonical, ?string $image = null, string $robots = 'index, follow'): void
    {
        $hreflangs = [];
        foreach (array_keys(config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []])) as $locale) {
            $hreflangs[$locale] = $canonical . (str_contains($canonical, '?') ? '&' : '?') . 'lang=' . $locale;
        }

        $this->injectIntoSeoTools(new SeoData(
            title: $title . ' | ' . config('app.name'),
            description: $description ? \Illuminate\Support\Str::limit(trim(strip_tags($description)), 160) : __('seo.default_description'),
            ogImage: $image,
            canonical: $canonical,
            hreflangs: $hreflangs,
            robots: $robots,
        ));
    }

    public function getData(string $type, ?object $model = null): SeoData
    {
        $build = function () use ($type, $model) {
            $builderClass = $this->builders[$type] ?? null;
            if (!$builderClass) {
                return new SeoData(title: config('app.name'));
            }

            return app($builderClass)->build($model);
        };

        // Only a model's own page is cached: its meta depends on the model and
        // locale alone. Pages without a model (home, search, the category
        // lists) are built from the current URL — one shared key let the first
        // visitor's search title or page (/categories vs /service-providers)
        // be served to everyone for an hour. They are cheap to build.
        if ($model?->id === null) {
            return $build();
        }

        return Cache::remember("seo_meta_{$type}_{$model->id}_" . App::getLocale(), 3600, $build);
    }

    protected function injectIntoSeoTools(SeoData $data): void
    {
        // Builders already end the title with "| Speeda"; without false the
        // package appends its default again ("… | Speeda - Speeda").
        SEOTools::setTitle($data->title, false);
        if ($data->description) {
            SEOTools::setDescription($data->description);
        }
        if ($data->keywords) {
            SEOTools::metatags()->setKeywords(array_map('trim', explode(',', $data->keywords)));
        }
        if ($data->canonical) {
            SEOTools::setCanonical($data->canonical);
        }

        SEOTools::metatags()->setRobots($data->robots);
        SEOTools::opengraph()->setTitle($data->title);
        SEOTools::opengraph()->setDescription($data->description ?? '');
        SEOTools::opengraph()->setUrl($data->canonical ?: url()->current());

        // Every page gets a share preview; pages without their own image use
        // the site card rather than none (WhatsApp/Facebook show a bare link).
        $image = $data->ogImage ?: asset('images/og-home.jpg');
        SEOTools::opengraph()->addImage($image);
        SEOTools::twitter()->setImage($image);
        SEOTools::twitter()->setType('summary_large_image');
        SEOTools::jsonLd()->addImage($image);

        SEOTools::opengraph()->setType($data->ogType);
        SEOTools::twitter()->setTitle($data->title);
        SEOTools::twitter()->setDescription($data->description ?? '');
        SEOTools::jsonLd()->setTitle($data->title);
        SEOTools::jsonLd()->setDescription($data->description ?? '');
        SEOTools::jsonLd()->setType($data->ogType === 'article' ? 'Article' : 'WebPage');
        if ($data->canonical) {
            SEOTools::jsonLd()->setUrl($data->canonical);
        }

        foreach ($data->hreflangs as $lang => $url) {
            SEOTools::metatags()->addAlternateLanguage($lang, $url);
        }
        if ($data->hreflangs && $data->canonical) {
            SEOTools::metatags()->addAlternateLanguage('x-default', $data->canonical);
        }
    }

    public function invalidate(string $type, $modelId): void
    {
        $locales = array_keys(config('app.supported_locales', ['en' => []]));
        foreach ($locales as $locale) {
            Cache::forget("seo_meta_{$type}_{$modelId}_{$locale}");
        }
    }
}
