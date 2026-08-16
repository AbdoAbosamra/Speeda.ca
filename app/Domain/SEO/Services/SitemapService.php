<?php

namespace App\Domain\SEO\Services;

use App\Models\Category;
use App\Models\Location;
use App\Models\Post;
use App\Models\ServiceProvider;
use Illuminate\Support\Facades\URL as UrlFacade;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    /**
     * Generate the XML sitemap and write it to public/sitemap.xml.
     *
     * URLs are derived from Laravel's route() helper, which reads its base from
     * the URL generator's root URL. In CLI (Artisan) context there is no HTTP
     * request, so the generator falls back to config('app.url'). We explicitly
     * force the root URL here to guarantee production-correct output regardless
     * of the environment the command runs in.
     *
     * @param  string|null  $baseUrl  Override the base URL (e.g. 'https://speeda.ca').
     *                                Falls back to config('app.url').
     * @return int  The number of <url> entries written.
     */
    public function generate(?string $baseUrl = null): int
    {
        // -----------------------------------------------------------------
        // Force the URL generator to use the production domain so that every
        // call to route() inside this method produces https://speeda.ca/...
        // instead of http://localhost/...
        // -----------------------------------------------------------------
        $originalRoot = UrlFacade::to('/');
        $rootUrl = rtrim($baseUrl ?? config('app.url'), '/');
        UrlFacade::forceRootUrl($rootUrl);

        // Always generate HTTPS URLs for production sitemaps.
        if (str_starts_with($rootUrl, 'https://')) {
            UrlFacade::forceScheme('https');
        }

        try {
            $sitemap = Sitemap::create();
            $locales = array_keys(config('app.supported_locales', ['en' => []]));
            $urlCount = 0;

            // 1. Home Pages
            foreach ($locales as $locale) {
                $sitemap->add($this->createUrl(route('home'), $locale, 1.0, $locales));
                $urlCount++;
            }

            // 2. Categories
            Category::where('is_active', true)->chunk(100, function ($categories) use ($sitemap, $locales, &$urlCount) {
                foreach ($categories as $category) {
                    foreach ($locales as $locale) {
                        $sitemap->add($this->createUrl(
                            route('service-providers.index', ['category' => $category->slug]),
                            $locale,
                            0.8,
                            $locales
                        ));
                        $urlCount++;
                    }
                }
            });

            // 3. Service Providers
            // Deactivated profiles must not be advertised in the sitemap.
            ServiceProvider::publiclyVisible()->chunk(100, function ($providers) use ($sitemap, $locales, &$urlCount) {
                foreach ($providers as $provider) {
                    foreach ($locales as $locale) {
                        $sitemap->add($this->createUrl(
                            route('service-providers.show', $provider),
                            $locale,
                            0.7,
                            $locales
                        ));
                        $urlCount++;
                    }
                }
            });

            // 4. Locations
            Location::where('is_active', true)->chunk(100, function ($locations) use ($sitemap, $locales, &$urlCount) {
                foreach ($locations as $location) {
                    foreach ($locales as $locale) {
                        $sitemap->add($this->createUrl(
                            route('service-providers.index', ['location' => $location->id]),
                            $locale,
                            0.6,
                            $locales
                        ));
                        $urlCount++;
                    }
                }
            });

            // 5. Blog pages
            foreach ($locales as $locale) {
                $sitemap->add($this->createUrl(route('blogs.index'), $locale, 0.8, $locales));
                $urlCount++;
            }

            Post::query()->published()->chunk(100, function ($posts) use ($sitemap, $locales, &$urlCount) {
                foreach ($posts as $post) {
                    foreach ($locales as $locale) {
                        $sitemap->add($this->createUrl(
                            route('blogs.show', $post),
                            $locale,
                            0.7,
                            $locales
                        ));
                        $urlCount++;
                    }
                }
            });

            $sitemap->writeToFile(public_path('sitemap.xml'));

            return $urlCount;
        } finally {
            // Restore the original URL generator state so that other parts of
            // the application are not affected if this runs in a long-lived
            // process (e.g. queue worker).
            UrlFacade::forceRootUrl($originalRoot);
        }
    }

    /**
     * Build a single <url> tag with hreflang alternates for every supported locale.
     */
    protected function createUrl(string $baseUrl, string $locale, float $priority, array $locales): Url
    {
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        $urlWithLang = $baseUrl . $separator . 'lang=' . $locale;

        $tag = Url::create($urlWithLang)
            ->setPriority($priority)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY);

        // Add hreflang alternates for each supported locale.
        foreach ($locales as $altLocale) {
            $altUrl = $baseUrl . $separator . 'lang=' . $altLocale;
            $tag->addAlternate($altUrl, $altLocale);
        }

        // x-default points to the English version as the fallback for
        // users whose language is not explicitly supported.
        $defaultUrl = $baseUrl . $separator . 'lang=en';
        $tag->addAlternate($defaultUrl, 'x-default');

        return $tag;
    }
}
