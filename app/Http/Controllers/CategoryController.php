<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display all main sections and stats (Frontend)
     */
    public function index(Request $request)
    {
        // Use caching for better performance
        $cacheKey = 'categories_frontend_' . app()->getLocale() . '_' . md5($request->fullUrl());
        
        $data = Cache::remember($cacheKey, 300, function () use ($request) {
            // Get search query if provided
            $search = $request->input('search');

            // Get selected city by id or by name (support ?city_id=3 or ?city=Montreal)
            if ($request->filled('city_id')) {
                $selectedCity = Location::find($request->get('city_id'));
            } elseif ($request->filled('city')) {
                $selectedCity = Location::where('city', $request->get('city'))->first();
            } else {
                $selectedCity = null;
            }

            // Get ONLY ACTIVE categories for frontend visibility
            $categoriesQuery = Category::with(['parent', 'serviceProviders' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->where('is_active', true) // Frontend: Only show active categories
                ->orderBy('sort_order')
                ->orderBy('name');

            // Apply search filter
            if ($search) {
                $categoriesQuery->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                      ->orWhere('name_ar', 'LIKE', '%' . $search . '%')
                      ->orWhere('name_en', 'LIKE', '%' . $search . '%')
                      ->orWhere('name_fr', 'LIKE', '%' . $search . '%');
                });
            }

            // Apply city filter with safe filtering
            if ($selectedCity) {
                $categoriesQuery->whereHas('serviceProviders', function ($query) use ($selectedCity) {
                    $query->where('location_id', $selectedCity->id)
                          ->where('is_active', true);
                });
            }

            $categories = $categoriesQuery->paginate(20);

            // Get only active locations for frontend
            $locations = Location::where('is_active', true)
                ->orderBy('city')
                ->get();

            // Prepare sections data for the existing view
            $sections = Category::where('is_section', true)
                ->where('is_active', true)
                ->with(['children' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            // Calculate stats
            $stats = [
                'totalSections' => $sections->count(),
                'totalCategories' => $categories->total(),
                'activeCategories' => $categories->count(),
                'totalProviders' => ServiceProvider::where('is_active', true)->count(),
            ];

            return [
                'sections' => $sections,
                'categories' => $categories,
                'locations' => $locations,
                'selectedCity' => $selectedCity,
                'search' => $search,
                'stats' => $stats
            ];
        });

        return view('categories', $data);
    }

    /**
     * Display specified category (Frontend) - SEO-friendly with slug
     */
    public function show(Category $category)
    {
        // Only show active categories on frontend
        if (!$category->is_active) {
            abort(404);
        }

        // Use caching for category details
        $cacheKey = 'category_show_' . $category->id;
        
        $data = Cache::remember($cacheKey, 300, function () use ($category) {
            $category->load([
                'parent',
                'children' => function ($query) {
                    $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
                },
                'serviceProviders' => function ($query) {
                    $query->with('location')
                          ->where('is_active', true);
                }
            ]);

            return [
                'category' => $category
            ];
        });

        return view('categories.show', $data);
    }
}
