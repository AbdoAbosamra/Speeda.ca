<?php

namespace App\Http\Controllers;

use App\Domain\SEO\Services\SeoMetaService;
use App\Models\Category;
use App\Models\ServiceProvider;
use App\Models\Review;
use App\Models\Endorsement;
use App\Models\Post;
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

        // Individual locations for homepage dropdown
        $locationClusters = [
            '2' => 'Montreal',
            '1' => 'Laval',
            '4' => 'Gatineau',
            '3' => 'Ottawa',
        ];

        // Provider Stats (Cache for 1 hour)
        $providerStats = Cache::remember('home_provider_stats', 3600, function () {
            return [
                'total_providers' => ServiceProvider::count(),
                'total_reviews' => Review::active()->count(),
                'total_recommendations' => Endorsement::count(),
            ];
        });

        // Top Providers - Top 2 per category (Prioritizing reviews, then completion)
        // Requirement 1: Profile must be at least 80% complete (MANDATORY)
        // Requirement 2: Top 2 from each category (to ensure diversity)
        // Requirement 3: Prioritize those with reviews. If no reviews, show based on completion %.
        // Debug: Get providers without cache and without complex ranking for a moment
        $featuredProviders = ServiceProvider::query()
            ->with(['user', 'category', 'location', 'media'])
            ->withCount([
                'endorsements',
                'reviews' => function ($query) {
                    $query->where('is_active', 1);
                },
            ])
            ->where('profile_completion_percent', '>=', 80)
            ->orderByDesc('reviews_count')
            ->orderByDesc('profile_completion_percent')
            ->take(8)
            ->get();

        // Latest Blogs (Cache for 1 hour)
        $latestBlogPosts = Cache::remember('home_latest_blog_posts', 3600, function () {
            return Post::published()
                ->with(['category', 'author'])
                ->latestPublished()
                ->take(3)
                ->get();
        });

        return view('home', compact('categories', 'locationClusters', 'providerStats', 'featuredProviders', 'latestBlogPosts'));
    }
}
