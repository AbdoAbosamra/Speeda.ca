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

        $description = $post->localized_seo_description
            ?: Str::limit(strip_tags($post->localized_excerpt ?: $post->localized_content), 160);

        return new SeoData(
            title: $post->localized_seo_title . ' | ' . config('app.name'),
            description: $description,
            keywords: $post->localized_seo_keywords,
            ogImage: $post->image_url,
            ogType: 'article',
            canonical: $post->canonical_url ?: $this->getCanonicalUrl(),
            hreflangs: $this->getHreflangs(),
            robots: $post->isIndexable() ? ($post->meta_robots ?: 'index, follow') : 'noindex, nofollow'
        );
    }
}
