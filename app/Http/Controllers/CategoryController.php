<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * Display all main sections and stats (Frontend)
     */
    public function index(Request $request, \App\Domain\SEO\Services\SeoMetaService $seoService)
    {
        // Use caching for better performance
        $cacheKey = 'categories_frontend_v2_'.app()->getLocale().'_'.md5($request->fullUrl());

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
            $categoriesQuery = Category::query()
                ->filterGroups();

            // Apply search filter
            if ($search) {
                $categoriesQuery->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%'.$search.'%')
                        ->orWhere('name_ar', 'LIKE', '%'.$search.'%')
                        ->orWhere('name_en', 'LIKE', '%'.$search.'%')
                        ->orWhere('name_fr', 'LIKE', '%'.$search.'%');
                });
            }

            // Apply city filter with safe filtering
            if ($selectedCity) {
                $categoriesQuery->where(function ($query) use ($selectedCity) {
                    $query->whereHas('serviceProviders', function ($providerQuery) use ($selectedCity) {
                        $providerQuery->where('location_id', $selectedCity->id)
                            ->where('is_active', true);
                    })->orWhereHas('children.serviceProviders', function ($providerQuery) use ($selectedCity) {
                        $providerQuery->where('location_id', $selectedCity->id)
                            ->where('is_active', true);
                    });
                });
            }

            $categories = $categoriesQuery->paginate(20)->withQueryString();

            // Get only active locations for frontend
            $locations = Location::where('is_active', true)
                ->orderBy('city')
                ->get();

            // Prepare sections data for the existing view
            $sections = Category::where('is_section', true)
                ->where('is_active', true)
                ->where('slug', '!=', 'others-1')
                ->with(['children' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->with(['children' => function ($childQuery) {
                            $childQuery->where('is_active', true)
                                ->orderBy('sort_order')
                                ->orderBy('name');
                        }]);
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return [
                'sections' => $sections,
                'categories' => $categories,
                'locations' => $locations,
                'selectedCity' => $selectedCity,
                'search' => $search,
            ];
        });

        // Apply SEO
        $seoService->apply('category');

        return view('categories', $data);
    }

    /**
     * Display specified category (Frontend) - SEO-friendly with slug
     */
    public function show(Category $category)
    {
        if (! $category->is_active) {
            if ($category->slug === 'construction-services') {
                return redirect()->route('service-providers.index', ['category' => 'renovation-construction'])->setStatusCode(301);
            }

            abort(404);
        }

        return redirect()->route('service-providers.index', ['category' => $category->slug])->setStatusCode(301);
    }
}
