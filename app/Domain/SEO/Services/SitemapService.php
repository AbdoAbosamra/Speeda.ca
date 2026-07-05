<?php

namespace App\Domain\SEO\Services;

use App\Models\Category;
use App\Models\Location;
use App\Models\Post;
use App\Models\ServiceProvider;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public function generate(): void
    {
        $sitemap = Sitemap::create();
        $locales = array_keys(config('app.supported_locales', ['en' => []]));

        // 1. Home Pages
        foreach ($locales as $locale) {
            $sitemap->add($this->createUrl(route('home'), $locale, 1.0));
        }

        // 2. Categories
        Category::where('is_active', true)->chunk(100, function ($categories) use ($sitemap, $locales) {
            foreach ($categories as $category) {
                foreach ($locales as $locale) {
                    $sitemap->add($this->createUrl(
                        route('service-providers.index', ['category' => $category->slug]),
                        $locale,
                        0.8
                    ));
                }
            }
        });

        // 3. Service Providers
        ServiceProvider::whereHas('user', function ($query) {
            $query->where('is_active', true);
        })->chunk(100, function ($providers) use ($sitemap, $locales) {
            foreach ($providers as $provider) {
                foreach ($locales as $locale) {
                    $sitemap->add($this->createUrl(
                        route('service-providers.show', $provider),
                        $locale,
                        0.7
                    ));
                }
            }
        });

        // 4. Locations
        Location::where('is_active', true)->chunk(100, function ($locations) use ($sitemap, $locales) {
            foreach ($locations as $location) {
                foreach ($locales as $locale) {
                    $sitemap->add($this->createUrl(
                        route('service-providers.index', ['location' => $location->id]),
                        $locale,
                        0.6
                    ));
                }
            }
        });

        // 5. Blog pages
        foreach ($locales as $locale) {
            $sitemap->add($this->createUrl(route('blogs.index'), $locale, 0.8));
        }

        Post::query()->published()->chunk(100, function ($posts) use ($sitemap, $locales) {
            foreach ($posts as $post) {
                foreach ($locales as $locale) {
                    $sitemap->add($this->createUrl(
                        route('blogs.show', $post),
                        $locale,
                        0.7
                    ));
                }
            }
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }

    protected function createUrl(string $baseUrl, string $locale, float $priority): Url
    {
        $urlWithLang = $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . 'lang=' . $locale;
        
        $tag = Url::create($urlWithLang)
            ->setPriority($priority)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY);

        // Add hreflang alternatives
        $locales = array_keys(config('app.supported_locales', ['en' => []]));
        foreach ($locales as $altLocale) {
            $altUrl = $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') . 'lang=' . $altLocale;
            $tag->addAlternate($altUrl, $altLocale);
        }

        return $tag;
    }
}
