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

    public function getData(string $type, ?object $model = null): SeoData
    {
        $locale = App::getLocale();
        $modelId = $model?->id ?? 'default';
        $cacheKey = "seo_meta_{$type}_{$modelId}_{$locale}";

        return Cache::remember($cacheKey, 3600, function () use ($type, $model) {
            $builderClass = $this->builders[$type] ?? null;
            if (!$builderClass) {
                return new SeoData(title: config('app.name'));
            }

            return app($builderClass)->build($model);
        });
    }

    protected function injectIntoSeoTools(SeoData $data): void
    {
        SEOTools::setTitle($data->title);
        if ($data->description) {
            SEOTools::setDescription($data->description);
        }
        if ($data->keywords) {
            SEOTools::metatags()->setKeywords(array_map('trim', explode(',', $data->keywords)));
        }
        if ($data->canonical) {
            SEOTools::setCanonical($data->canonical);
        }

        SEOTools::metatags()->addMeta('robots', $data->robots);
        SEOTools::opengraph()->setTitle($data->title);
        SEOTools::opengraph()->setDescription($data->description ?? '');
        SEOTools::opengraph()->setUrl($data->canonical ?: url()->current());

        if ($data->ogImage) {
            SEOTools::opengraph()->addImage($data->ogImage);
            SEOTools::twitter()->setImage($data->ogImage);
            SEOTools::jsonLd()->addImage($data->ogImage);
        }

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
    }

    public function invalidate(string $type, $modelId): void
    {
        $locales = array_keys(config('app.supported_locales', ['en' => []]));
        foreach ($locales as $locale) {
            Cache::forget("seo_meta_{$type}_{$modelId}_{$locale}");
        }
    }
}
