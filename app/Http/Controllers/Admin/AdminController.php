<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ErrorHelper;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Models\Category;
use App\Models\Visitor;
use App\Models\Review;
use App\Models\Post;
use App\Models\ServiceProvider;
use App\Models\AdminNotification;
use App\Models\User;
use App\Services\CategoryCacheService;
use App\Services\LocationCacheService;
use App\Services\VisitorTrackingService;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\AdminLog;
use Carbon\Carbon;

class AdminController extends Controller
{
    use LogsAdminActions;
    protected VisitorTrackingService $visitorService;

    /**
     * Create a new controller instance.
     */
    public function __construct(VisitorTrackingService $visitorService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->visitorService = $visitorService;
    }

    /**
     * Display admin dashboard with visitor statistics
     */
    public function dashboard()
    {
        try {
            // Get visitor statistics
            $visitorStats = $this->visitorService->getStatistics();

            // WhatsApp Analytics Calculations
            $now = Carbon::now();
            
            $dailyWhatsappClicks = \App\Models\ProviderAnalytics::where('action_type', 'click_whatsapp')
                ->whereDate('created_at', $now->toDateString())
                ->count();

            $yesterdayWhatsappClicks = \App\Models\ProviderAnalytics::where('action_type', 'click_whatsapp')
                ->whereDate('created_at', $now->copy()->subDay()->toDateString())
                ->count();
                
            $dailyWhatsappTrend = $yesterdayWhatsappClicks > 0 
                ? (($dailyWhatsappClicks - $yesterdayWhatsappClicks) / $yesterdayWhatsappClicks) * 100 
                : ($dailyWhatsappClicks > 0 ? 100 : 0);

            $weeklyWhatsappClicks = \App\Models\ProviderAnalytics::where('action_type', 'click_whatsapp')
                ->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                ->count();

            $lastWeekWhatsappClicks = \App\Models\ProviderAnalytics::where('action_type', 'click_whatsapp')
                ->whereBetween('created_at', [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()])
                ->count();
                
            $weeklyWhatsappTrend = $lastWeekWhatsappClicks > 0 
                ? (($weeklyWhatsappClicks - $lastWeekWhatsappClicks) / $lastWeekWhatsappClicks) * 100 
                : ($weeklyWhatsappClicks > 0 ? 100 : 0);

            $monthlyWhatsappClicks = \App\Models\ProviderAnalytics::where('action_type', 'click_whatsapp')
                ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
                ->count();

            $lastMonthWhatsappClicks = \App\Models\ProviderAnalytics::where('action_type', 'click_whatsapp')
                ->whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])
                ->count();
                
            $monthlyWhatsappTrend = $lastMonthWhatsappClicks > 0 
                ? (($monthlyWhatsappClicks - $lastMonthWhatsappClicks) / $lastMonthWhatsappClicks) * 100 
                : ($monthlyWhatsappClicks > 0 ? 100 : 0);

            $totalWhatsappClicks = \App\Models\ProviderAnalytics::where('action_type', 'click_whatsapp')->count();

            $mostClickedCategoryData = DB::table('analytics')
                ->join('service_providers', 'analytics.provider_id', '=', 'service_providers.id')
                ->join('categories', 'service_providers.category_id', '=', 'categories.id')
                ->where('analytics.action_type', 'click_whatsapp')
                ->select('categories.name_en as name', DB::raw('count(analytics.id) as total_clicks'))
                ->groupBy('categories.id', 'categories.name_en')
                ->orderByDesc('total_clicks')
                ->first();
                
            $mostClickedCategory = null;
            if ($mostClickedCategoryData) {
                $mostClickedCategory = [
                    'name' => $mostClickedCategoryData->name ?: 'Unknown',
                    'clicks' => $mostClickedCategoryData->total_clicks,
                    'percentage' => $totalWhatsappClicks > 0 
                        ? round(($mostClickedCategoryData->total_clicks / $totalWhatsappClicks) * 100, 1) 
                        : 0
                ];
            }

            $topProvidersPerformance = DB::table('service_providers')
                ->join('analytics', 'service_providers.id', '=', 'analytics.provider_id')
                ->where('analytics.created_at', '>=', $now->copy()->subDays(30))
                ->select('service_providers.id', 'service_providers.company_name',
                    DB::raw('SUM(CASE WHEN analytics.action_type = "view" THEN 1 ELSE 0 END) as total_views'),
                    DB::raw('SUM(CASE WHEN analytics.action_type = "click_whatsapp" THEN 1 ELSE 0 END) as total_whatsapp_clicks')
                )
                ->groupBy('service_providers.id', 'service_providers.company_name')
                ->havingRaw('SUM(CASE WHEN analytics.action_type = "view" THEN 1 ELSE 0 END) > 0')
                ->orderByRaw('(SUM(CASE WHEN analytics.action_type = "click_whatsapp" THEN 1 ELSE 0 END) / SUM(CASE WHEN analytics.action_type = "view" THEN 1 ELSE 0 END)) DESC')
                ->limit(5)
                ->get()
                ->map(function($provider) {
                    $rate = ($provider->total_whatsapp_clicks / $provider->total_views) * 100;
                    $provider->performance_rate = round($rate, 1);
                    return $provider;
                });

            $stats = [
                'liveVisitors' => $visitorStats['live_visitors'] ?? 0,
                'visitorsToday' => $this->getVisitorsToday(),
                'visitorsThisMonth' => $this->getVisitorsThisMonth(),
                'last7Days' => $visitorStats['last_7_days'] ?? 0,
                'last30Days' => $visitorStats['last_30_days'] ?? 0,
                'last12Months' => $visitorStats['last_12_months'] ?? 0,
                'totalVisitors' => $visitorStats['total_visitors'] ?? 0,
                'activeLocations' => Location::where('is_active', true)->count(),
                'activeCategories' => Category::where('is_active', true)->count(),
                'totalLocations' => Location::count(),
                'totalCategories' => Category::count(),
                'totalUsers' => User::count(),
                'totalProviders' => ServiceProvider::count(),
                'totalClients' => User::where('role', 'client')->count(),
                'totalBlogs' => Post::count(),
                'notificationsSent' => AdminNotification::count(),
                'activeNotifications' => AdminNotification::active()->count(),
                // Pending moderation counts
                'pendingReviews' => Review::where('is_active', false)->whereNull('admin_approved_at')->count(),
                'totalReviews' => Review::count(),
                'newUsersToday' => User::whereDate('created_at', today())->count(),
            ];

            return view('admin.dashboard', compact(
                'stats', 
                'dailyWhatsappClicks', 
                'dailyWhatsappTrend', 
                'weeklyWhatsappClicks', 
                'weeklyWhatsappTrend',
                'monthlyWhatsappClicks',
                'monthlyWhatsappTrend',
                'mostClickedCategory',
                'topProvidersPerformance'
            ));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            Log::error('Admin dashboard error: ' . $e->getMessage());
            ErrorHelper::flashNotification($error['message'], $error['type']);

            // Return view with empty stats to avoid redirect loop
            $stats = [
                'liveVisitors' => 0,
                'visitorsToday' => 0,
                'visitorsThisMonth' => 0,
                'last7Days' => 0,
                'last30Days' => 0,
                'last12Months' => 0,
                'totalVisitors' => 0,
                'activeLocations' => 0,
                'activeCategories' => 0,
                'totalLocations' => 0,
                'totalCategories' => 0,
                'totalUsers' => 0,
                'totalProviders' => 0,
                'totalClients' => 0,
                'totalBlogs' => 0,
                'notificationsSent' => 0,
                'activeNotifications' => 0,
                'pendingReviews' => 0,
                'totalReviews' => 0,
                'newUsersToday' => 0,
            ];
            return view('admin.dashboard', compact('stats'));
        }
    }

    /**
     * Get visitors for today
     */
    private function getVisitorsToday(): int
    {
        $canadaNow = Carbon::now(config('app.timezone', 'America/Toronto'));

        return Visitor::whereBetween('visited_at', [
                $canadaNow->copy()->startOfDay(),
                $canadaNow->copy()->endOfDay(),
            ])
            ->selectRaw('DISTINCT ip_hash, user_agent_hash')
            ->count();
    }

    /**
     * Get visitors for the current Canadian calendar month.
     */
    private function getVisitorsThisMonth(): int
    {
        $canadaNow = Carbon::now(config('app.timezone', 'America/Toronto'));

        return Visitor::whereBetween('visited_at', [
                $canadaNow->copy()->startOfMonth(),
                $canadaNow->copy()->endOfMonth(),
            ])
            ->selectRaw('DISTINCT ip_hash, user_agent_hash')
            ->count();
    }

    /**
     * Display list of all locations (only active ones)
     */
    public function locations()
    {
        try {
            // Show all locations to admin (active + inactive), ordering active ones first
            $locations = Location::orderByDesc('is_active')->orderBy('city')->paginate(20);
            return view('admin.locations.index', compact('locations'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Store a new location
     */
    public function storeLocation(StoreLocationRequest $request)
    {
        try {
            $validated = $request->validated();

            return DB::transaction(function () use ($request, $validated) {
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $filename = 'location_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('location-images', $filename, 'public');
                }

                $location = Location::create([
                    'city' => $validated['city'],
                    'country' => $validated['country'] ?? null,
                    'area' => $validated['area'] ?? null,
                    'latitude' => $validated['latitude'] ?? null,
                    'longitude' => $validated['longitude'] ?? null,
                    'image' => $imagePath,
                    'is_active' => $validated['is_active'] ?? true,
                    'meta_title' => $validated['meta_title'] ?? null,
                    'meta_description' => $validated['meta_description'] ?? null,
                ]);

                Log::info('Location created by admin', [
                    'location_id' => $location->id,
                    'city' => $location->city,
                    'admin_id' => Auth::id(),
                ]);

                $log = $this->logAction('create', $location);
                $this->clearApplicationCaches();

                $this->flashSuccessWithUndo(__('admin.location_created_successfully'), $log);

                return redirect()->route('admin.locations');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Update a location
     */
    public function updateLocation(UpdateLocationRequest $request, Location $location)
    {
        try {
            $validated = $request->validated();

            return DB::transaction(function () use ($request, $validated, $location) {
                $updateData = [
                    'city' => $validated['city'],
                    'country' => $validated['country'] ?? $location->country,
                    'area' => $validated['area'] ?? $location->area,
                    'latitude' => $validated['latitude'] ?? $location->latitude,
                    'longitude' => $validated['longitude'] ?? $location->longitude,
                    'is_active' => $validated['is_active'] ?? $location->is_active,
                    'meta_title' => $validated['meta_title'] ?? $location->meta_title,
                    'meta_description' => $validated['meta_description'] ?? $location->meta_description,
                ];

                // Handle image upload
                if ($request->hasFile('image')) {
                    // Note: We do NOT delete the old image here to allow "Undo" functionality to work.
                    // If we delete it, restoring the old path in DB would result in a broken image.
                    // Old images can be cleaned up via a scheduled job if needed.
                    /*
                    if ($location->image && Storage::disk('public')->exists($location->image)) {
                        Storage::disk('public')->delete($location->image);
                    }
                    */

                    $image = $request->file('image');
                    $filename = 'location_' . $location->id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('location-images', $filename, 'public');
                    $updateData['image'] = $imagePath;
                }


                $oldValues = $location->getOriginal(); // Capture old values BEFORE update
                $location->update($updateData);

                $log = $this->logUpdate($location, $oldValues);
                $this->clearApplicationCaches();

                $this->flashSuccessWithUndo(__('admin.location_updated_successfully'), $log);

                return redirect()->route('admin.locations');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Delete a location
     */
    public function deleteLocation(Location $location)
    {
        try {
            // Workflow: Active -> Deactivate -> Delete
            if ($location->is_active) {
                ErrorHelper::flashNotification(
                    __('admin.cannot_delete_active_location'),
                    'error'
                );
                return redirect()->back();
            }

            // Check if location has service providers
            $providersCount = $location->serviceProviders()->count();
            if ($providersCount > 0) {
                ErrorHelper::flashNotification(
                    __('admin.location_has_providers', ['count' => $providersCount]),
                    'error'
                );
                return redirect()->back();
            }

            return DB::transaction(function () use ($location) {
                $cityName = $location->city;

                // Delete location image if exists
                if ($location->image && Storage::disk('public')->exists($location->image)) {
                    Storage::disk('public')->delete($location->image);
                }

                $log = $this->logAction('delete', $location, ['deleted' => $location->toArray()]);
                $location->delete();
                $this->clearApplicationCaches();

                $this->flashSuccessWithUndo(__('admin.location_deleted_successfully', ['city' => $cityName]), $log);

                return redirect()->route('admin.locations');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Display list of all categories
     */
    public function categories()
    {
        try {
            $sections = Category::where('is_section', true)
                ->with([
                    'children' => function ($query) {
                        $query->orderBy('name');
                    }
                ])
                ->orderBy('sort_order')
                ->get();

            $allCategories = Category::with('parent')
                ->orderBy('name')
                ->get();

            return response()
                ->view('admin.categories.index', compact('sections', 'allCategories'))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Show the form for editing a category
     */
    public function editCategory(Category $category)
    {
        try {
            $sections = Category::where('is_section', true)
                ->where('id', '!=', $category->id)
                ->orderBy('sort_order')
                ->get();

            return response()
                ->view('admin.categories.edit', compact('category', 'sections'))
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.categories');
        }
    }

    /**
     * Generate a unique slug from the given name
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        // Generate base slug using Str::slug (supports Arabic)
        $baseSlug = \Illuminate\Support\Str::slug($name, '-', null);
        
        // If empty slug (happens with non-Latin characters sometimes), use transliteration
        if (empty($baseSlug)) {
            $baseSlug = \Illuminate\Support\Str::slug($name, '-', 'ar');
        }
        
        // If still empty, use a safe fallback
        if (empty($baseSlug)) {
            $baseSlug = 'category-' . time();
        }
        
        $slug = $baseSlug;
        $counter = 1;
        
        // Check for uniqueness
        while (Category::where('slug', $slug)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Store a new category with multi-language support
     */
    public function storeCategory(StoreCategoryRequest $request)
    {
        try {
            $validated = $request->validated();

            return DB::transaction(function () use ($validated) {
                // Generate slug from English name if available, otherwise from Arabic
                $slugSource = !empty($validated['name_en']) ? $validated['name_en'] : ($validated['name_ar'] ?? '');
                $slug = $this->generateUniqueSlug($slugSource);

                // Build data array with multi-language fields
                $data = [
                    'name' => $validated['name_ar'] ?? $validated['name_en'] ?? '',
                    'name_ar' => $validated['name_ar'] ?? null,
                    'name_en' => $validated['name_en'] ?? null,
                    'name_fr' => $validated['name_fr'] ?? null,
                    'slug' => $slug,
                    'description' => $validated['description_ar'] ?? $validated['description_en'] ?? null,
                    'description_ar' => $validated['description_ar'] ?? null,
                    'description_en' => $validated['description_en'] ?? null,
                    'description_fr' => $validated['description_fr'] ?? null,
                    'parent_id' => $validated['parent_id'] ?? null,
                    'is_section' => $validated['is_section'] ?? false,
                    'is_active' => $validated['is_active'] ?? true,
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'icon' => $validated['icon'] ?? null,
                    'color' => $validated['color'] ?? null,
                    'meta_title' => $validated['meta_title'] ?? null,
                    'meta_description' => $validated['meta_description'] ?? null,
                ];

                $category = Category::create($data);

                Log::info('Category created by admin with multi-language', [
                    'category_id' => $category->id,
                    'name_ar' => $category->name_ar,
                    'name_en' => $category->name_en,
                    'slug' => $category->slug,
                    'admin_id' => Auth::id(),
                ]);

                $log = $this->logAction('create', $category);
                $this->clearApplicationCaches();
                Cache::forget('categories:tree');
                Cache::forget('categories:sections');
                Cache::forget('categories:active');

                $this->flashSuccessWithUndo(__('admin.category_created_successfully'), $log);

                return redirect()->route('admin.categories');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Update a category with multi-language support
     */
    public function updateCategory(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $validated = $request->validated();

            return DB::transaction(function () use ($validated, $category) {
                $oldValues = $category->getOriginal();
                
                // Prevent setting parent to itself
                if ($validated['parent_id'] && $validated['parent_id'] == $category->id) {
                    throw new \Exception(__('admin.cannot_set_self_as_parent'));
                }

                // Regenerate slug if English name changed (or Arabic if English is empty)
                $slug = $category->slug;
                $nameEnChanged = isset($validated['name_en']) && $validated['name_en'] !== $category->name_en;
                $nameArChanged = isset($validated['name_ar']) && $validated['name_ar'] !== $category->name_ar;
                
                if ($nameEnChanged || $nameArChanged) {
                    $slugSource = !empty($validated['name_en']) ? $validated['name_en'] : ($validated['name_ar'] ?? '');
                    if (!empty($slugSource)) {
                        $slug = $this->generateUniqueSlug($slugSource, $category->id);
                    }
                }

                // Build update data with multi-language fields
                $updateData = [
                    'slug' => $slug,
                    'parent_id' => $validated['parent_id'] ?? $category->parent_id,
                    'is_section' => $validated['is_section'] ?? $category->is_section,
                    'is_active' => $validated['is_active'] ?? $category->is_active,
                    'sort_order' => $validated['sort_order'] ?? $category->sort_order,
                    'icon' => $validated['icon'] ?? $category->icon,
                    'color' => $validated['color'] ?? $category->color,
                ];

                // Update name fields if provided
                if (isset($validated['name_ar'])) {
                    $updateData['name_ar'] = $validated['name_ar'];
                    $updateData['name'] = $validated['name_ar']; // Keep legacy field in sync
                }
                if (isset($validated['name_en'])) {
                    $updateData['name_en'] = $validated['name_en'];
                }
                if (isset($validated['name_fr'])) {
                    $updateData['name_fr'] = $validated['name_fr'];
                }

                // Update description fields if provided
                if (isset($validated['description_ar'])) {
                    $updateData['description_ar'] = $validated['description_ar'];
                    $updateData['description'] = $validated['description_ar']; // Keep legacy field in sync
                }
                if (isset($validated['description_en'])) {
                    $updateData['description_en'] = $validated['description_en'];
                }
                if (isset($validated['description_fr'])) {
                    $updateData['description_fr'] = $validated['description_fr'];
                }

                $category->update($updateData);

                $log = $this->logUpdate($category, $oldValues);
                $this->clearApplicationCaches();
                Cache::forget('categories:tree');
                Cache::forget('categories:sections');
                Cache::forget('categories:active');

                $this->flashSuccessWithUndo(__('admin.category_updated_successfully'), $log);

                return redirect()
                    ->route('admin.categories')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory(Category $category)
    {
        try {
            // Workflow: Active -> Deactivate -> Delete
            if ($category->is_active) {
                ErrorHelper::flashNotification(
                    __('admin.cannot_delete_active_category'),
                    'error'
                );
                return redirect()->back();
            }

            // Check if category has children (including inactive)
            if ($category->allChildren()->count() > 0) {
                ErrorHelper::flashNotification(
                    __('admin.category_has_children'),
                    'error'
                );
                return redirect()->back();
            }

            // Check if category has service providers
            if ($category->serviceProviders()->count() > 0) {
                ErrorHelper::flashNotification(
                    __('admin.category_has_providers', ['count' => $category->serviceProviders()->count()]),
                    'error'
                );
                return redirect()->back();
            }

            return DB::transaction(function () use ($category) {
                $categoryName = $category->name;
                $log = $this->logAction('delete', $category, ['deleted' => $category->toArray()]);
                $category->delete();
                $this->clearApplicationCaches();

                $this->flashSuccessWithUndo(__('admin.category_deleted_successfully'), $log);

                return redirect()->route('admin.categories');
            });
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Deactivate a category
     */
    public function deactivateCategory(Category $category)
    {
        try {
            $category->update(['is_active' => false]);
            $log = $this->logAction('deactivate', $category);
            $this->clearApplicationCaches();

            $this->flashSuccessWithUndo(__('admin.category_updated_successfully'), $log);
            return redirect()->back();
        } catch (\Exception $e) {
            ErrorHelper::flashNotification($e->getMessage(), 'error');
            return redirect()->back();
        }
    }

    /**
     * Activate a category
     */
    public function activateCategory(Category $category)
    {
        try {
            $category->update(['is_active' => true]);
            $log = $this->logAction('activate', $category);
            $this->clearApplicationCaches();

            $this->flashSuccessWithUndo(__('admin.category_updated_successfully'), $log);
            return redirect()->back();
        } catch (\Exception $e) {
            ErrorHelper::flashNotification($e->getMessage(), 'error');
            return redirect()->back();
        }
    }

    /**
     * Toggle category active status (convenience method)
     */
    public function toggleCategoryStatus(Category $category)
    {
        try {
            $newStatus = !$category->is_active;
            $category->update(['is_active' => $newStatus]);

            // Clear category-specific caches
            $this->clearApplicationCaches();
            Cache::forget('categories:tree');
            Cache::forget('categories:sections');
            Cache::forget('categories:active');

            // Log the action
            Log::info('Category status toggled by admin', [
                'category_id' => $category->id,
                'name' => $category->name,
                'new_status' => $newStatus,
                'admin_id' => Auth::id(),
            ]);

            $message = $newStatus
                ? __('admin.category_activated_successfully')
                : __('admin.category_deactivated_successfully');

            ErrorHelper::flashNotification($message, 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Deactivate a location
     */
    public function deactivateLocation(Location $location)
    {
        try {
            $location->update(['is_active' => false]);
            $log = $this->logAction('deactivate', $location);
            $this->clearApplicationCaches();

            $this->flashSuccessWithUndo(__('admin.location_updated_successfully'), $log);
            return redirect()->back();
        } catch (\Exception $e) {
            ErrorHelper::flashNotification($e->getMessage(), 'error');
            return redirect()->back();
        }
    }

    /**
     * Activate a location
     */
    public function activateLocation(Location $location)
    {
        try {
            $location->update(['is_active' => true]);
            $log = $this->logAction('activate', $location);
            $this->clearApplicationCaches();

            $this->flashSuccessWithUndo(__('admin.location_updated_successfully'), $log);
            return redirect()->back();
        } catch (\Exception $e) {
            ErrorHelper::flashNotification($e->getMessage(), 'error');
            return redirect()->back();
        }
    }

    /**
     * Display list of all users with status management
     */
    public function users(Request $request)
    {
        try {
            $users = User::with('serviceProvider')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Stats for dashboard - with safe checks for is_active column
            $hasActiveColumn = Schema::hasColumn('users', 'is_active');
            
            $stats = [
                'total' => User::count(),
                'active' => $hasActiveColumn ? User::where('is_active', true)->count() : User::count(),
                'inactive' => $hasActiveColumn ? User::where('is_active', false)->count() : 0,
                'clients' => User::where('role', 'client')->count(),
                'providers' => User::where('role', 'service_provider')->count(),
            ];

            return view('admin.users.index', compact('users', 'stats'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleUserStatus(User $user)
    {
        try {
            // Prevent deactivating the last admin
            if ($user->isAdmin() && !$user->is_active) {
                $activeAdmins = User::where('role', 'admin')
                    ->where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->count();
                
                if ($activeAdmins === 0) {
                    ErrorHelper::flashNotification(
                        __('admin.cannot_deactivate_last_admin'),
                        'error'
                    );
                    return redirect()->back();
                }
            }

            $newStatus = !$user->is_active;
            $user->update(['is_active' => $newStatus]);

            // Clear caches to reflect changes immediately
            $this->clearApplicationCaches();

            // Log the action
            Log::info('User status toggled by admin', [
                'user_id' => $user->id,
                'email' => $user->email,
                'new_status' => $newStatus,
                'admin_id' => Auth::id(),
            ]);

            $message = $newStatus 
                ? __('admin.user_activated_successfully') 
                : __('admin.user_deactivated_successfully');

            ErrorHelper::flashNotification($message, 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Delete a user account permanently with all related data (Hard Delete)
     */
    public function deleteUser(User $user)
    {
        try {
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                ErrorHelper::flashNotification(
                    __('admin.cannot_delete_yourself'),
                    'error'
                );
                return redirect()->back();
            }

            // Prevent deleting the last admin
            if ($user->isAdmin()) {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    ErrorHelper::flashNotification(
                        __('admin.cannot_delete_last_admin'),
                        'error'
                    );
                    return redirect()->back();
                }
            }

            return DB::transaction(function () use ($user) {
                $userName = $user->name;
                $userEmail = $user->email;

                // 1. Cleanup Service Provider Profile and Media
                if ($user->serviceProvider) {
                    $provider = $user->serviceProvider;
                    
                    // Cleanup Spatie Media Library collection
                    if (method_exists($provider, 'clearMediaCollection')) {
                        $provider->clearMediaCollection('gallery');
                    }
                    
                    // Cleanup manually managed profile image
                    if ($provider->profile_image && Storage::disk('public')->exists($provider->profile_image)) {
                        Storage::disk('public')->delete($provider->profile_image);
                    }

                    // Cleanup business license file if exists
                    if ($provider->business_license && Storage::disk('public')->exists($provider->business_license)) {
                        Storage::disk('public')->delete($provider->business_license);
                    }
                    
                    $provider->delete();
                }

                // 2. Cleanup User-authored content
                // These use the relationships added to User model
                $user->reviews()->delete();
                $user->comments()->forceDelete(); // Comments use SoftDeletes, so we forceDelete
                $user->endorsements()->delete();
                $user->bookings()->delete();
                
                // 3. Cleanup Pivot tables
                $user->savedProviders()->detach();
                $user->readAdminNotifications()->detach();

                // 4. Log the action before deletion for audit purposes
                // Note: The log will contain the email for future reference
                $this->logAction('permanent_delete', $user, [
                    'deleted_user_email' => $userEmail,
                    'deleted_user_name' => $userName,
                    'data_cleaned' => ['provider', 'reviews', 'comments', 'endorsements', 'bookings', 'bookmarks']
                ]);

                // 5. Finally, delete the user record
                $user->delete();

                // Clear caches
                $this->clearApplicationCaches();

                Log::info('User permanently deleted by admin', [
                    'deleted_user_id' => $user->id,
                    'deleted_user_email' => $userEmail,
                    'admin_id' => Auth::id(),
                ]);

                ErrorHelper::flashNotification(
                    __('admin.user_deleted_successfully', ['name' => $userName]),
                    'success'
                );
                return redirect()->back();
            });
        } catch (\Exception $e) {
            Log::error('Permanent user deletion failed: ' . $e->getMessage());
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Display soft deleted users
     */
    public function usersTrash()
    {
        $users = User::onlyTrashed()->latest()->paginate(20);
        return view('admin.users.trash', compact('users'));
    }

    /**
     * Edit user data and role
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user data and role
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,client,service_provider',
            'is_active' => 'boolean',
        ]);

        $user->update($request->only('name', 'email', 'role', 'is_active'));

        ErrorHelper::flashNotification(__('admin.user_updated_successfully'), 'success');
        return redirect()->route('admin.users');
    }

    /**
     * Restore a soft deleted user
     */
    public function restoreUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        ErrorHelper::flashNotification(__('admin.user_restored_successfully'), 'success');
        return redirect()->back();
    }

    /**
     * Permanently delete a user
     */
    public function forceDeleteUser($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $user = User::withTrashed()->findOrFail($id);
                
                if ($user->id === auth()->id()) {
                    throw new \Exception(__('admin.cannot_delete_yourself'));
                }

                // Cleanup related data (similar to deleteUser logic but for force delete)
                if ($user->isServiceProvider()) {
                    $provider = $user->serviceProvider;
                    if ($provider) {
                        $provider->reviews()->delete();
                        $provider->endorsements()->delete();
                        $provider->delete();
                    }
                }

                $user->forceDelete();
            });

            ErrorHelper::flashNotification(__('admin.user_permanently_deleted'), 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Flash success message with undo link
     */
    protected function flashSuccessWithUndo(string $message, AdminLog $log)
    {
        $undoLink = route('admin.undo', $log->id);
        $undoHtml = ' <a href="#" class="alert-link ms-2 fw-bold" onclick="event.preventDefault(); document.getElementById(\'undo-form-' . $log->id . '\').submit();">' . __('admin.undo') . '</a>' .
            '<form id="undo-form-' . $log->id . '" action="' . $undoLink . '" method="POST" style="display: none;">' . csrf_field() . '</form>';

        ErrorHelper::flashNotification($message . $undoHtml, 'success');
    }

    /**
     * Clear common caches (views, routes, config, app cache)
     */
    public function clearCache(Request $request)
    {
        try {
            $this->clearApplicationCaches();
            ErrorHelper::flashNotification(__('admin.cache_cleared', [], app()->getLocale()), 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Clear all application caches safely
     */
    protected function clearApplicationCaches(): void
    {
        try {
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            // PERFORMANCE: Invalidate category and location caches
            app(CategoryCacheService::class)->invalidateCache();
            app(LocationCacheService::class)->invalidateCache();
        } catch (\Exception $e) {
            Log::warning('Failed to clear application caches', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
