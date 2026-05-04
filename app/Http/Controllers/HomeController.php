<?php

namespace App\Http\Controllers;

use App\Domain\SEO\Services\SeoMetaService;
use App\Models\Category;
use App\Models\ServiceProvider;
use App\Models\Review;
use App\Models\Endorsement;
use App\Models\Post;
use App\Services\LocationClusterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(SeoMetaService $seoService)
    {
        $seoService->apply('home');

        // Get categories with their relationships (Cache for 1 hour)
        $categories = Cache::remember('home_categories', 3600, function () {
            return Category::with('children')
                ->filterGroups()
                ->get()
                ->sortBy('translated_name')
                ->values();
        });

        // Location clusters for dropdown
        $locationClusters = [
            'cluster_montreal' => 'Laval – Montréal',
            'cluster_ottawa' => 'Ottawa – Gatineau',
        ];

        // Provider Stats (Cache for 1 hour)
        $providerStats = Cache::remember('home_provider_stats', 3600, function () {
            return [
                'total_providers' => ServiceProvider::verified()->count(),
                'total_reviews' => Review::active()->count(),
                'total_recommendations' => Endorsement::count(),
            ];
        });

        // Top Providers - Enforcing Multi-Level Priority Order (Rating > Completion)
        $topProviders = Cache::remember('home_top_providers', 3600, function () {
            return ServiceProvider::verified()
                ->with(['user', 'category', 'location', 'media'])
                ->withCount(['reviews', 'endorsements'])
                // Constraint: Must have at least one rating OR 80%+ profile completion
                ->where(function ($query) {
                    $query->whereNotNull('rating')
                        ->orWhere('profile_completion_percent', '>=', 80);
                })
                // Priority 1: Average Rating (Highest stars first)
                ->orderByRaw('rating IS NULL ASC') // Push NULLs to end of rating group
                ->orderBy('rating', 'desc')
                // Priority 2: Profile Completeness (Tie-breaker or for unrated)
                ->orderBy('profile_completion_percent', 'desc')
                // Priority 3: Quantity of feedback
                ->orderBy('reviews_count', 'desc')
                ->take(8)
                ->get();
        });

        // Latest Blogs (Cache for 1 hour)
        $latestBlogPosts = Cache::remember('home_latest_blog_posts', 3600, function () {
            return Post::published()
                ->with(['category', 'author'])
                ->latestPublished()
                ->take(3)
                ->get();
        });

        return view('home', compact('categories', 'locationClusters', 'providerStats', 'topProviders', 'latestBlogPosts'));
    }
}
