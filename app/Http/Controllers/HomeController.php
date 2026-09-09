<?php

namespace App\Http\Controllers;

use App\Domain\SEO\Services\SeoMetaService;
use App\Models\Category;
use App\Models\HomeVideo;
use App\Models\ServiceProvider;
use App\Models\Review;
use App\Models\Endorsement;
use App\Models\Post;
use App\Models\SiteTestimonial;
use App\Services\CategoryCacheService;
use App\Services\LocationClusterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(SeoMetaService $seoService)
    {
        $seoService->apply('home');

        // PERFORMANCE: Use cached categories from Redis (24h TTL)
        $categories = app(CategoryCacheService::class)->getFilterGroups();

        // @change 2026-06-08 | Use the same public cluster filter as the service provider listing.
        $locationClusters = app(LocationClusterService::class)->getPublicFilterClusters();


        // Provider Stats (Cache for 1 hour)
        $providerStats = Cache::remember('home_provider_stats', 3600, function () {
            return [
                'total_providers' => ServiceProvider::count(),
                'total_reviews' => Review::active()->count(),
                'total_recommendations' => Endorsement::count(),
            ];
        });

        // "Discover our providers" — random selection of complete profiles.
        // Requirements:
        //   1. Profile must be at least 80% complete (photo + 2 of 3 other fields)
        //   2. Provider must be publicly visible (active + user active)
        //   3. Must have a profile image
        //   4. Random order so the homepage feels fresh on every visit
        $featuredProviders = ServiceProvider::query()
            ->with(['user', 'category', 'location', 'media'])
            ->withCount([
                'endorsements',
                'reviews' => function ($query) {
                    $query->where('is_active', 1);
                },
            ])
            ->publiclyVisible()
            ->where('profile_completion_percent', '>=', 80)
            ->whereNotNull('profile_image')
            ->where('profile_image', '!=', '')
            ->inRandomOrder()
            ->take(12)
            ->get();

        // Fallback: if there aren't enough complete profiles yet, still populate
        // the homepage marquee with the strongest available profiles so the
        // section never disappears.
        if ($featuredProviders->isEmpty()) {
            $featuredProviders = ServiceProvider::query()
                ->with(['user', 'category', 'location', 'media'])
                ->withCount([
                    'endorsements',
                    'reviews' => function ($query) {
                        $query->where('is_active', 1);
                    },
                ])
                ->publiclyVisible()
                ->whereNotNull('profile_image')
                ->where('profile_image', '!=', '')
                ->inRandomOrder()
                ->take(12)
                ->get();
        }

        // Latest Blogs (Cache for 1 hour)
        $latestBlogPosts = Cache::remember('home_latest_blog_posts', 3600, function () {
            return Post::published()
                ->with(['category', 'author'])
                ->latestPublished()
                ->take(3)
                ->get();
        });

        // Admin-managed provider testimonials — shown only when exactly 3 are active.
        $siteTestimonials = SiteTestimonial::forHomePage();

        // Admin-managed video section — null unless it is switched on and has
        // a playable video, in which case the home view renders nothing.
        $homeVideo = HomeVideo::forHomePage();

        return view('home', compact('categories', 'locationClusters', 'providerStats', 'featuredProviders', 'latestBlogPosts', 'siteTestimonials', 'homeVideo'));
    }
}
