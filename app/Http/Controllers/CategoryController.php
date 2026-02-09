<?php

// app/Http/Controllers/CategoryController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display all main sections and stats
     */
    public function index(Request $request)
    {
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

        // For views we prefer passing a simple city name (string) instead of model objects
        $selectedCityName = $selectedCity ? ($selectedCity->city ?? (string) $selectedCity) : null;

        // Build query for sections
        $sectionsQuery = Category::with(['children' => function ($query) use ($search) {
            $query->withCount('serviceProviders')
                ->active()
                ->orderBy('name');

            // Apply search filter to subcategories if search term provided
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('slug', 'LIKE', "%{$search}%");
                });
            }
        }])
            ->sections()
            ->active()
            ->orderBy('sort_order');

        // Apply search filter to sections if search term provided
        if ($search) {
            $sectionsQuery->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('slug', 'LIKE', "%{$search}%")
                      ->orWhereHas('children', function($q) use ($search) {
                          $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%")
                            ->orWhere('slug', 'LIKE', "%{$search}%");
                      });
            });
        }

        $sections = $sectionsQuery->get();        // Stats for UI counters
        $stats = [
            'totalSections' => Category::sections()->active()->count(),
            'totalCategories' => Category::subcategories()->active()->count(),
            'totalProviders' => ServiceProvider::verified()->count(),
            'totalLocations' => Location::active()->count(),
        ];

        return view('categories', compact('sections', 'stats'))
            ->with('selectedCity', $selectedCityName)
            ->with('search', $search);
    }
}
