<?php

namespace App\Services;

use App\Models\ServiceProvider;
use App\Models\Category;
use Illuminate\Support\Facades\App;

class SEOService
{
    /**
     * Generate meta tags array for a given page type.
     */
    public function generateMeta(string $type, ?object $model = null): array
    {
        return match ($type) {
            'home' => $this->homePageMeta(),
            'service_provider' => $this->serviceProviderMeta($model),
            'category' => $this->categoryMeta($model),
            'location' => $this->locationMeta($model),
            default => $this->defaultMeta(),
        };
    }

    /**
     * Home page meta tags.
     */
    protected function homePageMeta(): array
    {
        return [
            'title' => config('app.name') . ' - ' . __('seo.home_title'),
            'description' => __('seo.home_description'),
            'keywords' => __('seo.home_keywords'),
            'og_type' => 'website',
            'og_image' => asset('images/og-image.png'),
        ];
    }

    /**
     * Service provider profile meta tags.
     */
    protected function serviceProviderMeta(?ServiceProvider $provider): array
    {
        if (!$provider) {
            return $this->defaultMeta();
        }

        $title = $provider->company_name ?? $provider->user?->name ?? config('app.name');
        $description = $provider->bio
            ? strip_tags(substr($provider->bio, 0, 160))
            : __('seo.provider_default_description', ['name' => $title]);

        return [
            'title' => $title . ' | ' . config('app.name'),
            'description' => $description,
            'keywords' => $this->generateProviderKeywords($provider),
            'og_type' => 'profile',
            'og_image' => url($provider->display_image_url),
            'canonical' => route('service-providers.show', $provider),
        ];
    }

    /**
     * Category page meta tags.
     */
    protected function categoryMeta(?Category $category): array
    {
        if (!$category) {
            return $this->defaultMeta();
        }

        return [
            'title' => $category->getTranslatedNameAttribute() . ' | ' . config('app.name'),
            'description' => $category->description ?? __('seo.category_description', ['name' => $category->name]),
            'keywords' => $category->name . ', ' . __('seo.category_keywords'),
            'og_type' => 'website',
            'canonical' => route('categories.show', $category),
        ];
    }

    /**
     * Location page meta tags.
     */
    protected function locationMeta(?object $location): array
    {
        if (!$location) {
            return $this->defaultMeta();
        }

        return [
            'title' => $location->city . ' | ' . config('app.name'),
            'description' => $location->meta_description ?? __('seo.location_description', ['city' => $location->city]),
            'keywords' => $location->city . ', ' . __('seo.location_keywords'),
            'og_type' => 'website',
            'canonical' => route('location'),
        ];
    }

    /**
     * Default meta tags.
     */
    protected function defaultMeta(): array
    {
        return [
            'title' => config('app.name'),
            'description' => __('seo.default_description'),
            'keywords' => __('seo.default_keywords'),
            'og_type' => 'website',
        ];
    }

    /**
     * Generate keywords for a service provider.
     */
    protected function generateProviderKeywords(ServiceProvider $provider): string
    {
        $keywords = [];

        if ($provider->category) {
            $keywords[] = $provider->category->name;
        }

        if ($provider->location) {
            $keywords[] = $provider->location->city;
        }

        if ($provider->company_name) {
            $keywords[] = $provider->company_name;
        }

        $keywords[] = 'service provider';
        $keywords[] = config('app.name');

        return implode(', ', $keywords);
    }

    /**
     * Generate JSON-LD structured data for a service provider.
     */
    public function getServiceProviderJsonLd(ServiceProvider $provider): array
    {
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $provider->company_name ?? $provider->user?->name,
            'url' => route('service-providers.show', $provider),
        ];

        if ($provider->bio) {
            $jsonLd['description'] = strip_tags($provider->bio);
        }

        if ($provider->phone) {
            $jsonLd['telephone'] = $provider->phone;
        }

        if ($provider->email) {
            $jsonLd['email'] = $provider->email;
        }

        if ($provider->location) {
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $provider->location->city,
                'addressCountry' => $provider->location->country ?? 'CA',
            ];
        }

        if ($provider->rating) {
            $jsonLd['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $provider->rating,
                'bestRating' => 5,
                'worstRating' => 1,
                'ratingCount' => $provider->activeReviews()->count(),
            ];
        }

        $jsonLd['image'] = url($provider->display_image_url);

        return $jsonLd;
    }

    /**
     * Generate JSON-LD structured data for the website.
     */
    public function getWebsiteJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => config('app.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => config('app.url') . '/service-providers?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }
}
