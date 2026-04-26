<?php

namespace App\Http\Controllers;

use App\Domain\SEO\Services\SeoMetaService;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    public function index(Request $request, SeoMetaService $seoService): View
    {
        $seoService->apply('blog_index');

        $search = trim((string) $request->string('search'));

        $posts = Post::query()
            ->published()
            ->with(['author', 'category'])
            ->searchPublic($search)
            ->latestPublished()
            ->paginate(9)
            ->withQueryString();

        $featuredPosts = collect();

        if ($search === '') {
            $featuredPosts = Cache::remember('blog_featured_posts_' . app()->getLocale(), 1800, function () {
                $query = Post::query()
                    ->published()
                    ->with(['author', 'category'])
                    ->latestPublished();

                if (\Illuminate\Support\Facades\Schema::hasColumn('posts', 'is_featured')) {
                    $query->where('is_featured', true);
                }

                return $query->take(3)->get();
            });
        }

        return view('blog.index', [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'search' => $search,
        ]);
    }

    public function show(Post $post, SeoMetaService $seoService): View
    {
        abort_unless(
            Post::query()->published()->whereKey($post->getKey())->exists(),
            404
        );

        $post->loadMissing(['author', 'category']);

        $seoService->apply('blog_post', $post);

        $relatedPosts = $this->getRelatedPosts($post);

        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->localized_seo_title,
            'description' => $post->localized_seo_description,
            'image' => [$post->image_url],
            'datePublished' => optional($post->published_at ?: $post->created_at)?->toIso8601String(),
            'dateModified' => optional($post->updated_at ?: $post->created_at)?->toIso8601String(),
            'mainEntityOfPage' => url()->current(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author?->name ?? config('app.name'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
            ],
        ];

        return view('blog.show', compact('post', 'relatedPosts', 'schemaData'));
    }

    protected function getRelatedPosts(Post $post): Collection
    {
        $query = Post::query()
            ->published()
            ->with(['author', 'category'])
            ->whereKeyNot($post->getKey());

        if ($post->category_id) {
            $query->where('category_id', $post->category_id);
        }

        $related = $query->latestPublished()->take(3)->get();

        if ($related->count() < 3) {
            $fallbackIds = $related->pluck('id')->push($post->id)->all();

            $fallback = Post::query()
                ->published()
                ->with(['author', 'category'])
                ->whereNotIn('id', $fallbackIds)
                ->latestPublished()
                ->take(3 - $related->count())
                ->get();

            $related = $related->concat($fallback);
        }

        return $related;
    }
}
