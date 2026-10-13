<?php

namespace App\Domain\SEO\Builders;

use App\Domain\SEO\DTOs\SeoData;
use App\Models\Post;
use Illuminate\Support\Str;

class BlogPostSeoBuilder extends BaseSeoBuilder
{
    public function build(?object $model = null): SeoData
    {
        /** @var Post|null $post */
        $post = $model instanceof Post ? $model : null;

        if (!$post) {
            return new SeoData(title: config('app.name'));
        }

        // Built from the route, not the visitor's URL: this result is cached
        // per post, so a stray query string must not become the canonical.
        $url = route('blogs.show', $post);
        $hreflangs = [];
        foreach (array_keys(config('app.supported_locales', ['en' => [], 'ar' => [], 'fr' => []])) as $locale) {
            $hreflangs[$locale] = route('blogs.show', [$post, 'lang' => $locale]);
        }

        $description = $post->localized_seo_description
            ?: Str::limit(strip_tags($post->localized_excerpt ?: $post->localized_content), 160);

        return new SeoData(
            title: $post->localized_seo_title . ' | ' . config('app.name'),
            description: $description,
            keywords: $post->localized_seo_keywords,
            ogImage: $post->image_url,
            ogType: 'article',
            canonical: $post->canonical_url ?: $url,
            hreflangs: $hreflangs,
            robots: $post->isIndexable() ? ($post->meta_robots ?: 'index, follow') : 'noindex, nofollow'
        );
    }
}
