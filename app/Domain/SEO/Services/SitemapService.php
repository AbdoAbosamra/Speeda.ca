<?php

namespace App\Domain\SEO\Services;

use App\Models\Category;
use App\Models\Location;
use App\Models\Offer;
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
        $originalGenerator = UrlFacade::getFacadeRoot();
        UrlFacade::swap(clone $originalGenerator);
        $rootUrl = rtrim($baseUrl ?? config('app.url'), '/');
        UrlFacade::forceRootUrl($rootUrl);

        // Always generate HTTPS URLs for production sitemaps.
        if (str_starts_with($rootUrl, 'https://')) {
            UrlFacade::forceScheme('https');
        }

        try {
            $sitemap = Sitemap::create();
            $locales = array_keys(config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []]));
            $urlCount = 0;

            // 1. Homepage (Canonical)
            $sitemap->add($this->createCanonicalEntry(route('home'), 1.0, Url::CHANGE_FREQUENCY_DAILY, $locales));
            $urlCount++;

            // 2. Core Public Static Pages (Canonical 200 OK)
            $staticRoutes = [
                ['name' => 'categories', 'priority' => 0.9, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
                ['name' => 'service-providers.index', 'priority' => 0.9, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
                ['name' => 'blogs.index', 'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
                ['name' => 'offers.index', 'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_DAILY],
                ['name' => 'about-us', 'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
                ['name' => 'help-center', 'priority' => 0.6, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
                ['name' => 'legal-affairs', 'priority' => 0.5, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
                ['name' => 'privacy-policy', 'priority' => 0.4, 'freq' => Url::CHANGE_FREQUENCY_YEARLY],
                ['name' => 'terms-of-service', 'priority' => 0.4, 'freq' => Url::CHANGE_FREQUENCY_YEARLY],
            ];

            foreach ($staticRoutes as $routeInfo) {
                if (\Illuminate\Support\Facades\Route::has($routeInfo['name'])) {
                    $sitemap->add($this->createCanonicalEntry(
                        route($routeInfo['name']),
                        $routeInfo['priority'],
                        $routeInfo['freq'],
                        $locales
                    ));
                    $urlCount++;
                }
            }

            // 3. Active Categories with actual providers (Prevents Soft 404 / Thin Content)
            Category::where('is_active', true)
                ->where(function ($query) {
                    $query->whereHas('serviceProviders', fn ($q) => $q->publiclyVisible())
                          ->orWhereHas('children.serviceProviders', fn ($q) => $q->publiclyVisible());
                })
                ->chunk(100, function ($categories) use ($sitemap, $locales, &$urlCount) {
                    foreach ($categories as $category) {
                        $sitemap->add($this->createCanonicalEntry(
                            route('service-providers.index', ['category' => $category->slug]),
                            0.8,
                            Url::CHANGE_FREQUENCY_WEEKLY,
                            $locales,
                            $category->updated_at
                        ));
                        $urlCount++;
                    }
                });

            // 4. Publicly Visible Service Providers (Active owner & active listing)
            ServiceProvider::publiclyVisible()
                ->with(['user'])
                ->chunk(100, function ($providers) use ($sitemap, $locales, &$urlCount) {
                    foreach ($providers as $provider) {
                        $sitemap->add($this->createCanonicalEntry(
                            route('service-providers.show', $provider),
                            0.7,
                            Url::CHANGE_FREQUENCY_WEEKLY,
                            $locales,
                            $provider->updated_at
                        ));
                        $urlCount++;
                    }
                });

            // 5. Published Blog Posts
            Post::query()->published()->chunk(100, function ($posts) use ($sitemap, $locales, &$urlCount) {
                foreach ($posts as $post) {
                    $sitemap->add($this->createCanonicalEntry(
                        route('blogs.show', $post),
                        0.7,
                        Url::CHANGE_FREQUENCY_MONTHLY,
                        $locales,
                        $post->updated_at
                    ));
                    $urlCount++;
                }
            });

            // 6. Live Local Offers (drafts, scheduled and ended offers are left out)
            Offer::live()->chunk(100, function ($offers) use ($sitemap, $locales, &$urlCount) {
                foreach ($offers as $offer) {
                    $sitemap->add($this->createCanonicalEntry(
                        route('offers.show', $offer->slug),
                        0.7,
                        Url::CHANGE_FREQUENCY_DAILY,
                        $locales,
                        $offer->updated_at
                    ));
                    $urlCount++;
                }
            });

            $sitemap->writeToFile(public_path('sitemap.xml'));

            return $urlCount;
        } finally {
            // Restore the original URL generator state
            UrlFacade::swap($originalGenerator);
        }
    }

    /**
     * Build a single canonical <url> tag with hreflang alternates for every supported locale.
     */
    protected function createCanonicalEntry(
        string $canonicalUrl,
        float $priority,
        string $changeFrequency,
        array $locales,
        ?\DateTimeInterface $lastModified = null
    ): Url {
        $tag = Url::create($canonicalUrl)
            ->setPriority($priority)
            ->setChangeFrequency($changeFrequency);

        if ($lastModified) {
            $tag->setLastModificationDate($lastModified);
        }

        $separator = str_contains($canonicalUrl, '?') ? '&' : '?';

        // Add hreflang alternates for multilingual crawling
        foreach ($locales as $altLocale) {
            $altUrl = $canonicalUrl . $separator . 'lang=' . $altLocale;
            $tag->addAlternate($altUrl, $altLocale);
        }

        // x-default points to the canonical base URL
        $tag->addAlternate($canonicalUrl, 'x-default');

        return $tag;
    }
}
