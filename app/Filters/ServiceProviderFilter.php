<?php

namespace App\Filters;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ServiceProviderFilter
{
    public static function apply(Builder $query, Request $request): Builder
    {
        $query->where('is_verified', true)
              ->with(['user', 'location', 'category', 'reviews']);

        // Location filters
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        
        if ($request->filled('city')) {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('city', 'like', '%' . $request->city . '%');
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $category = Category::resolveFilterValue($request->category_id);

            if ($category) {
                $query->whereIn('category_id', $category->providerCategoryIds());
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Availability filters
        if ($request->boolean('available_weekends')) {
            $query->availableWeekends();
        }
        
        if ($request->boolean('available_evenings')) {
            $query->availableEvenings();
        }
        
        if ($request->boolean('emergency_available')) {
            $query->emergencyAvailable();
        }

        // Rating filter
        if ($request->filled('min_rating')) {
            $query->where('average_rating', '>=', $request->min_rating);
        }

        // Price filter
        if ($request->filled('max_hourly_rate')) {
            $query->where('hourly_rate', '<=', $request->max_hourly_rate);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('profession', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        self::applySorting($query, $request);

        return $query;
    }

    protected static function applySorting(Builder $query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'average_rating');
        $sortOrder = $request->get('sort_order', 'desc');

        $sortMap = [
            'rating' => 'average_rating',
            'price' => 'hourly_rate', 
            'experience' => 'experience_years',
            'reviews' => 'total_reviews',
        ];

        $sortField = $sortMap[$sortBy] ?? 'average_rating';
        $query->orderBy($sortField, $sortOrder);
    }
}
