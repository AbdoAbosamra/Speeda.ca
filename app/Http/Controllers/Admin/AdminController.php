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
use App\Support\AdminAnalyticsExclusion;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
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
    use HandlesBulkActions;

    /**
     * Derived dashboard/analytics caches that must be dropped whenever an admin
     * changes underlying data. Keys mirror AdminDashboardService + helpers.
     */
    private const ADMIN_DASHBOARD_CACHE_KEYS = [
        'admin_dash_action_center',
        'admin_dash_kpis',
        'admin_dash_funnel',
        'admin_dash_visitor_trend_14',
        'admin_dash_profile_health',
        'admin_user_ids',
        'visitor_stats',
        'live_visitors_count',
    ];

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

            // WhatsApp Analytics Calculations.
            // Every count below goes through whatsappClicksBetween() so admin
            // activity is excluded consistently — the category/provider break-
            // downs further down already did this, the headline numbers did not.
            $now = Carbon::now();

            $dailyWhatsappClicks = $this->whatsappClicksBetween($now->copy()->startOfDay(), $now);
            $yesterdayWhatsappClicks = $this->whatsappClicksBetween(
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay()
            );

            $dailyWhatsappTrend = $this->percentageChange($dailyWhatsappClicks, $yesterdayWhatsappClicks);

            // Rolling 7-day window vs the previous 7-day window (equal length =
            // a fair comparison; avoids the "partial current week vs full last
            // week" distortion that always reads negative early in the period).
            $weeklyWhatsappClicks = $this->whatsappClicksBetween($now->copy()->subDays(7), $now);
            $lastWeekWhatsappClicks = $this->whatsappClicksBetween($now->copy()->subDays(14), $now->copy()->subDays(7));
            $weeklyWhatsappTrend = $this->percentageChange($weeklyWhatsappClicks, $lastWeekWhatsappClicks);

            // Rolling 30-day window vs the previous 30-day window.
            $monthlyWhatsappClicks = $this->whatsappClicksBetween($now->copy()->subDays(30), $now);
            $lastMonthWhatsappClicks = $this->whatsappClicksBetween($now->copy()->subDays(60), $now->copy()->subDays(30));
            $monthlyWhatsappTrend = $this->percentageChange($monthlyWhatsappClicks, $lastMonthWhatsappClicks);

            $totalWhatsappClicks = $this->whatsappClicksBetween(null, null);

            $mostClickedCategoryData = DB::table('analytics')
                ->join('service_providers', 'analytics.provider_id', '=', 'service_providers.id')
                ->join('categories', 'service_providers.category_id', '=', 'categories.id')
                ->where('analytics.action_type', 'click_whatsapp')
                ->tap(fn($q) => AdminAnalyticsExclusion::apply($q, 'analytics.user_id'))
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

            // NOTE: the "Top Providers" table on the dashboard is rendered from
            // $dashboard['top_providers'] (AdminDashboardService, cached). The
            // duplicate raw-SQL ranking that used to live here was never read by
            // the view and has been removed.

            // Only the keys the dashboard view actually reads. The remaining
            // counters (active/total categories & locations, total users,
            // active notifications, new users today, 7/30/365-day visitors) were
            // computed on every page load and never rendered.
            $stats = [
                'liveVisitors' => $visitorStats['live_visitors'] ?? 0,
                'visitorsToday' => $this->getVisitorsToday(),
                'visitorsThisMonth' => $this->getVisitorsThisMonth(),
                'totalVisitors' => $visitorStats['total_visitors'] ?? 0,
                'totalProviders' => ServiceProvider::count(),
                'totalClients' => User::where('role', 'client')->count(),
                'totalBlogs' => Post::count(),
                'notificationsSent' => AdminNotification::count(),
                'pendingReviews' => Review::where('is_active', false)->whereNull('admin_approved_at')->count(),
                'totalReviews' => Review::count(),
            ];

            // Rich operations data (action center, KPIs+trends, funnel, feeds).
            $dashboard = app(\App\Services\Admin\AdminDashboardService::class)->build();

            return view('admin.dashboard', compact(
                'stats',
                'dashboard',
                'dailyWhatsappClicks',
                'dailyWhatsappTrend',
                'weeklyWhatsappClicks',
                'weeklyWhatsappTrend',
                'monthlyWhatsappClicks',
                'monthlyWhatsappTrend',
                'mostClickedCategory'
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
                'totalVisitors' => 0,
                'totalProviders' => 0,
                'totalClients' => 0,
                'totalBlogs' => 0,
                'notificationsSent' => 0,
                'pendingReviews' => 0,
                'totalReviews' => 0,
            ];
            $dashboard = [
                'action_center' => ['items' => [], 'total' => 0],
                'kpis' => [], 'funnel' => [], 'visitor_trend' => ['labels' => [], 'values' => []],
                'profile_health' => [], 'top_providers' => [], 'top_categories' => [],
                'recent_signups' => [], 'recent_reviews' => [], 'recent_admin_actions' => [],
            ];
            return view('admin.dashboard', compact('stats', 'dashboard'));
        }
    }

    /**
     * Count WhatsApp clicks in a window, excluding admin activity.
     * Pass nulls for an all-time count.
     */
    private function whatsappClicksBetween(?Carbon $from, ?Carbon $to): int
    {
        $query = DB::table('analytics')->where('action_type', 'click_whatsapp');

        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        AdminAnalyticsExclusion::apply($query);

        return (int) $query->count();
    }

    /**
     * Percentage change between two equal-length windows.
     */
    private function percentageChange(int $current, int $previous): float
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100.0 : 0.0;
    }

    /**
     * Get visitors for today
     */
    private function getVisitorsToday(): int
    {
        $canadaNow = Carbon::now(config('app.timezone', 'America/Toronto'));

        return (int) Visitor::whereBetween('visited_at', [
                $canadaNow->copy()->startOfDay(),
                $canadaNow->copy()->endOfDay(),
            ])
            ->where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhereDoesntHave('user', fn ($q) => $q->where('role', 'admin'));
            })
            ->selectRaw($this->uniqueVisitorExpression() . ' as aggregate')
            ->value('aggregate');
    }

    /**
     * Get visitors for the current Canadian calendar month.
     */
    private function getVisitorsThisMonth(): int
    {
        $canadaNow = Carbon::now(config('app.timezone', 'America/Toronto'));

        return (int) Visitor::whereBetween('visited_at', [
                $canadaNow->copy()->startOfMonth(),
                $canadaNow->copy()->endOfMonth(),
            ])
            ->where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhereDoesntHave('user', fn ($q) => $q->where('role', 'admin'));
            })
            ->selectRaw($this->uniqueVisitorExpression() . ' as aggregate')
            ->value('aggregate');
    }

    /**
     * Driver-portable "distinct (ip_hash, user_agent_hash)" count expression.
     * MySQL supports multi-column COUNT(DISTINCT ...); SQLite does not.
     */
    private function uniqueVisitorExpression(): string
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? 'COUNT(DISTINCT ip_hash, user_agent_hash)'
            : "COUNT(DISTINCT ip_hash || '|' || user_agent_hash)";
    }

    /**
     * Display list of all locations (only active ones)
     */
    public function locations()
    {
        try {
            // Show all locations to admin (active + inactive), ordering active ones first
            $locations = Location::orderByDesc('is_active')->orderBy('city')->paginate(20)->withQueryString();
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
                // Nullable fields are only touched when the form actually sent
                // them, so a cleared input clears the column and an absent input
                // leaves the stored value alone.
                $updateData = [
                    'city' => $validated['city'],
                    'is_active' => $validated['is_active'] ?? $location->is_active,
                ];

                foreach (['country', 'area', 'latitude', 'longitude', 'meta_title', 'meta_description'] as $nullable) {
                    if (array_key_exists($nullable, $validated)) {
                        $updateData[$nullable] = $validated[$nullable];
                    }
                }

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
    public function categories(Request $request)
    {
        try {
            // Sections drive the parent dropdowns and the section filter.
            $sections = Category::where('is_section', true)
                ->orderBy('sort_order')
                ->get();

            // Search / section / status are applied in SQL. They used to be
            // Alpine-only bindings that were never wired to the table rows, so
            // none of the three controls did anything.
            $search = trim((string) $request->query('search', ''));
            $sectionId = $request->query('section');
            $status = (string) $request->query('status', '');

            $allCategories = Category::with('parent')
                ->withCount('serviceProviders')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder->where('name', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%")
                            ->orWhere('name_ar', 'like', "%{$search}%")
                            ->orWhere('name_fr', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                })
                ->when($sectionId === 'root', fn ($query) => $query->whereNull('parent_id'))
                ->when($sectionId && $sectionId !== 'root', fn ($query) => $query->where('parent_id', $sectionId))
                ->when($status === 'active', fn ($query) => $query->where('is_active', true))
                ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
                ->orderByDesc('is_section')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(30)
                ->withQueryString();

            $stats = [
                'totalCategories' => Category::count(),
                'activeCategories' => Category::where('is_active', true)->count(),
                'inactiveCategories' => Category::where('is_active', false)->count(),
                'sections' => $sections->count(),
            ];

            return response()
                ->view('admin.categories.index', compact('sections', 'allCategories', 'stats', 'search', 'sectionId', 'status'))
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
                if (($validated['parent_id'] ?? null) && $validated['parent_id'] == $category->id) {
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

                // Build update data with multi-language fields.
                // Nullable fields use array_key_exists so an intentionally
                // emptied value (e.g. "None" for parent_id) actually clears the
                // column instead of silently coalescing back to the old value.
                $updateData = [
                    'slug' => $slug,
                    'is_section' => $validated['is_section'] ?? $category->is_section,
                    'is_active' => $validated['is_active'] ?? $category->is_active,
                    'sort_order' => $validated['sort_order'] ?? $category->sort_order,
                ];

                foreach (['parent_id', 'icon', 'color', 'meta_title', 'meta_description'] as $nullable) {
                    if (array_key_exists($nullable, $validated)) {
                        $updateData[$nullable] = $validated[$nullable];
                    }
                }

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
     * Toggle category active status.
     *
     * (The old separate activateCategory()/deactivateCategory() methods had no
     * routes pointing at them and have been removed — this is the single entry
     * point used by admin.categories.toggle.)
     */
    public function toggleCategoryStatus(Category $category)
    {
        try {
            $newStatus = !$category->is_active;
            $category->update(['is_active' => $newStatus]);

            $log = $this->logAction($newStatus ? 'activate' : 'deactivate', $category);
            $this->clearApplicationCaches();

            Log::info('Category status toggled by admin', [
                'category_id' => $category->id,
                'name' => $category->name,
                'new_status' => $newStatus,
                'admin_id' => Auth::id(),
            ]);

            $message = $newStatus
                ? __('admin.category_activated_successfully')
                : __('admin.category_deactivated_successfully');

            $this->flashSuccessWithUndo($message, $log);
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

    /* =====================================================================
     |  BULK USER ACTIONS
     * ===================================================================== */

    public function bulkUsers(Request $request)
    {
        return $this->runBulkAction($request, 'users');
    }

    public function bulkCategories(Request $request)
    {
        return $this->runBulkAction($request, 'categories');
    }

    public function bulkLocations(Request $request)
    {
        return $this->runBulkAction($request, 'locations');
    }

    protected function bulkActions(string $resource): array
    {
        return match ($resource) {
            'users' => [
                'activate' => __('admin.bulk_verb_activated'),
                'deactivate' => __('admin.bulk_verb_deactivated'),
                'trash' => __('admin.bulk_verb_trashed'),
            ],
            'categories', 'locations' => [
                'activate' => __('admin.bulk_verb_activated'),
                'deactivate' => __('admin.bulk_verb_deactivated'),
                'delete' => __('admin.bulk_verb_deleted'),
            ],
            default => [],
        };
    }

    protected function bulkQuery(string $resource): Builder
    {
        return match ($resource) {
            'users' => User::query(),
            // withCount so the delete guards do not fire a query per row.
            'categories' => Category::query()->withCount(['serviceProviders', 'allChildren']),
            'locations' => Location::query()->withCount('serviceProviders'),
            default => User::query()->whereRaw('1 = 0'),
        };
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $model)
    {
        return match ($resource) {
            'users' => $this->applyBulkUserAction($action, $model),
            'categories' => $this->applyBulkCategoryAction($action, $model),
            'locations' => $this->applyBulkLocationAction($action, $model),
            default => __('admin.bulk_reason_failed'),
        };
    }

    /**
     * Bulk user actions run through the SAME protections as the single-item
     * routes: you can never act on yourself, and you can never remove the last
     * active admin — the batch just reports those rows as skipped.
     *
     * @return true|string
     */
    private function applyBulkUserAction(string $action, User $user)
    {
        if ($user->id === Auth::id()) {
            return __('admin.bulk_reason_self');
        }

        return match ($action) {
            'activate' => $this->bulkActivateUser($user),
            'deactivate' => $this->bulkDeactivateUser($user),
            'trash' => $this->bulkTrashUser($user),
            default => __('admin.bulk_reason_failed'),
        };
    }

    /**
     * Categories keep the "deactivate before delete" workflow, and a category
     * that still has children or providers can never be removed in bulk.
     *
     * @return true|string
     */
    private function applyBulkCategoryAction(string $action, Category $category)
    {
        switch ($action) {
            case 'activate':
                if ($category->is_active) {
                    return __('admin.bulk_reason_already_active');
                }
                $category->update(['is_active' => true]);
                $this->logAction('activate', $category);
                break;

            case 'deactivate':
                if (!$category->is_active) {
                    return __('admin.bulk_reason_already_inactive');
                }
                $category->update(['is_active' => false]);
                $this->logAction('deactivate', $category);
                break;

            case 'delete':
                if ($category->is_active) {
                    return __('admin.bulk_reason_must_deactivate_first');
                }
                if ((int) ($category->all_children_count ?? 0) > 0) {
                    return __('admin.bulk_reason_has_children');
                }
                if ((int) ($category->service_providers_count ?? 0) > 0) {
                    return __('admin.bulk_reason_has_providers');
                }
                $this->logAction('delete', $category, ['deleted' => $category->toArray()]);
                $category->delete();
                break;

            default:
                return __('admin.bulk_reason_failed');
        }

        $this->clearApplicationCaches();

        return true;
    }

    /**
     * @return true|string
     */
    private function applyBulkLocationAction(string $action, Location $location)
    {
        switch ($action) {
            case 'activate':
                if ($location->is_active) {
                    return __('admin.bulk_reason_already_active');
                }
                $location->update(['is_active' => true]);
                $this->logAction('activate', $location);
                break;

            case 'deactivate':
                if (!$location->is_active) {
                    return __('admin.bulk_reason_already_inactive');
                }
                $location->update(['is_active' => false]);
                $this->logAction('deactivate', $location);
                break;

            case 'delete':
                if ($location->is_active) {
                    return __('admin.bulk_reason_must_deactivate_first');
                }
                if ((int) ($location->service_providers_count ?? 0) > 0) {
                    return __('admin.bulk_reason_has_providers');
                }
                if ($location->image && Storage::disk('public')->exists($location->image)) {
                    Storage::disk('public')->delete($location->image);
                }
                $this->logAction('delete', $location, ['deleted' => $location->toArray()]);
                $location->delete();
                break;

            default:
                return __('admin.bulk_reason_failed');
        }

        $this->clearApplicationCaches();

        return true;
    }

    private function bulkActivateUser(User $user)
    {
        if ($user->is_active) {
            return __('admin.bulk_reason_already_active');
        }

        $user->update(['is_active' => true]);
        $this->logAction('activate', $user);

        return true;
    }

    private function bulkDeactivateUser(User $user)
    {
        if (!$user->is_active) {
            return __('admin.bulk_reason_already_inactive');
        }

        if ($this->wouldRemoveLastActiveAdmin($user)) {
            return __('admin.bulk_reason_last_admin');
        }

        $user->update(['is_active' => false]);
        $this->logAction('deactivate', $user);

        return true;
    }

    private function bulkTrashUser(User $user)
    {
        if ($user->trashed()) {
            return __('admin.bulk_reason_already_trashed');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->where('id', '!=', $user->id)->count() === 0) {
            return __('admin.bulk_reason_last_admin');
        }

        // Mirrors deleteUser(): deactivate + soft delete, nothing destroyed.
        $user->update(['is_active' => false]);
        $this->logAction('delete', $user, [
            'trashed_user_email' => $user->email,
            'trashed_user_name' => $user->name,
        ]);
        $user->delete();

        return true;
    }

    /**
     * Would deactivating this user leave the platform with no active admin?
     */
    private function wouldRemoveLastActiveAdmin(User $user): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return User::where('role', 'admin')
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->count() === 0;
    }

    /**
     * Display list of all users with search, role filter, status filter, and sorting
     */
    public function users(Request $request)
    {
        try {
            $allowedSortFields = ['name', 'email', 'role', 'is_active', 'created_at'];
            $sortField = in_array($request->get('sortField', 'created_at'), $allowedSortFields)
                ? $request->get('sortField', 'created_at') : 'created_at';
            $sortDirection = $request->get('sortDirection', 'desc') === 'asc' ? 'asc' : 'desc';

            // withCount feeds the "Activity" column in the users table.
            $query = User::with('serviceProvider')->withCount(['reviews', 'comments']);

            // Search filter
            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Role filter
            if ($role = $request->get('role')) {
                $query->where('role', $role);
            }

            // Status filter
            if ($status = $request->get('status')) {
                $hasActiveColumn = Schema::hasColumn('users', 'is_active');
                if ($hasActiveColumn) {
                    $query->where('is_active', $status === 'active');
                }
            }

            $users = $query->orderBy($sortField, $sortDirection)
                 ->paginate(20)
                 ->withQueryString();

            // Stats
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
            // Never let an admin lock themselves out of the panel.
            if ($user->id === Auth::id()) {
                ErrorHelper::flashNotification(
                    __('admin.cannot_deactivate_yourself'),
                    'error'
                );
                return redirect()->back();
            }

            // Prevent deactivating the last remaining active admin.
            // (Guard applies when the user is currently ACTIVE, i.e. about to be turned off.)
            if ($user->isAdmin() && $user->is_active) {
                $otherActiveAdmins = User::where('role', 'admin')
                    ->where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($otherActiveAdmins === 0) {
                    ErrorHelper::flashNotification(
                        __('admin.cannot_deactivate_last_admin'),
                        'error'
                    );
                    return redirect()->back();
                }
            }

            $newStatus = !$user->is_active;
            $user->update(['is_active' => $newStatus]);

            $this->logAction($newStatus ? 'activate' : 'deactivate', $user);
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
     * Move a user to the trash bin (reversible soft delete).
     *
     * Nothing is destroyed here: the account is deactivated and soft deleted so
     * it disappears from the public site while staying fully restorable from
     * admin/users/trash. Irreversible cleanup lives in forceDeleteUser().
     */
    public function deleteUser(User $user)
    {
        try {
            if ($guard = $this->guardUserRemoval($user)) {
                return $guard;
            }

            return DB::transaction(function () use ($user) {
                $userName = $user->name;

                // Public provider listings filter on users.is_active and skip
                // trashed users, so deactivating + soft deleting is enough to
                // pull the profile off the site without destroying anything.
                $user->update(['is_active' => false]);

                $log = $this->logAction('delete', $user, [
                    'trashed_user_email' => $user->email,
                    'trashed_user_name' => $userName,
                ]);

                $user->delete(); // soft delete

                $this->clearApplicationCaches();

                Log::info('User moved to trash by admin', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'admin_id' => Auth::id(),
                ]);

                $this->flashSuccessWithUndo(
                    __('admin.user_moved_to_trash', ['name' => $userName]),
                    $log
                );

                return redirect()->back();
            });
        } catch (\Exception $e) {
            Log::error('Moving user to trash failed: ' . $e->getMessage());
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Shared guard for both trashing and permanently deleting a user.
     * Returns a redirect when the removal must be blocked, null otherwise.
     */
    protected function guardUserRemoval(User $user)
    {
        if ($user->id === Auth::id()) {
            ErrorHelper::flashNotification(__('admin.cannot_delete_yourself'), 'error');
            return redirect()->back();
        }

        if ($user->isAdmin()) {
            $otherAdmins = User::where('role', 'admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherAdmins === 0) {
                ErrorHelper::flashNotification(__('admin.cannot_delete_last_admin'), 'error');
                return redirect()->back();
            }
        }

        return null;
    }

    /**
     * Display soft deleted users
     */
    public function usersTrash()
    {
        $users = User::onlyTrashed()
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,client,service_provider',
            'is_active' => 'nullable|boolean',
        ]);

        $isSelf = $user->id === Auth::id();
        $newRole = $validated['role'];
        $newStatus = $request->boolean('is_active');

        // You may never change your own role or status from this form; the view
        // locks both fields, this is the server-side counterpart.
        if ($isSelf) {
            $newRole = $user->role;
            $newStatus = $user->is_active;
        }

        // Demoting or deactivating the last remaining active admin would lock
        // everyone out of the panel.
        $losesAdmin = $user->isAdmin() && ($newRole !== 'admin' || $newStatus === false);
        if ($losesAdmin) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins === 0) {
                ErrorHelper::flashNotification(__('admin.cannot_deactivate_last_admin'), 'error');
                return redirect()->back()->withInput();
            }
        }

        $oldValues = $user->getOriginal();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $newRole,
            'is_active' => $newStatus,
        ]);

        $this->logUpdate($user, $oldValues);
        $this->clearApplicationCaches();

        ErrorHelper::flashNotification(__('admin.user_updated_successfully'), 'success');
        return redirect()->route('admin.users');
    }

    /**
     * Restore a soft deleted user
     */
    public function restoreUser($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            DB::transaction(function () use ($user) {
                $user->restore();
                $user->update(['is_active' => true]);

                $this->logAction('restore', $user);
            });

            $this->clearApplicationCaches();

            ErrorHelper::flashNotification(__('admin.user_restored_successfully'), 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Permanently delete a user and every piece of data they own.
     *
     * This is the irreversible half of the trash workflow and is only reachable
     * from admin/users/trash. Files on disk are removed too, so there is no undo.
     */
    public function forceDeleteUser($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            if ($guard = $this->guardUserRemoval($user)) {
                return $guard;
            }

            DB::transaction(function () use ($user) {
                $userName = $user->name;
                $userEmail = $user->email;

                // 1. Service provider profile, media and uploaded documents.
                // (ServiceProvider does not use SoftDeletes, so no withTrashed here.)
                $provider = $user->serviceProvider;

                if ($provider) {
                    if (method_exists($provider, 'clearMediaCollection')) {
                        $provider->clearMediaCollection('gallery');
                    }

                    foreach ([$provider->profile_image, $provider->business_license] as $file) {
                        if ($file && Storage::disk('public')->exists($file)) {
                            Storage::disk('public')->delete($file);
                        }
                    }

                    $provider->reviews()->delete();
                    $provider->endorsements()->delete();
                    $provider->delete();
                }

                // 2. Content authored by the user.
                $user->reviews()->delete();
                $user->comments()->withTrashed()->forceDelete();
                $user->endorsements()->delete();
                $user->bookings()->delete();

                // 3. Pivot tables.
                $user->savedProviders()->detach();
                $user->readAdminNotifications()->detach();

                // 4. Audit trail (kept even though the row disappears).
                $this->logAction('permanent_delete', $user, [
                    'deleted_user_email' => $userEmail,
                    'deleted_user_name' => $userName,
                    'data_cleaned' => ['provider', 'media', 'reviews', 'comments', 'endorsements', 'bookings', 'bookmarks'],
                ]);

                $user->forceDelete();

                Log::warning('User permanently deleted by admin', [
                    'deleted_user_email' => $userEmail,
                    'admin_id' => Auth::id(),
                ]);
            });

            $this->clearApplicationCaches();

            ErrorHelper::flashNotification(__('admin.user_permanently_deleted'), 'success');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Permanent user deletion failed: ' . $e->getMessage());
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
        // The undo affordance is rendered by <x-error-handler /> from the log id,
        // so the flash payload stays plain text (no HTML injected into the session).
        ErrorHelper::flashNotification($message, 'success', $log->id);
    }

    /**
     * Clear common caches (views, routes, config, app cache)
     */
    public function clearCache(Request $request)
    {
        try {
            $this->flushAllCaches();
            ErrorHelper::flashNotification(__('admin.cache_cleared'), 'success');
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
    /**
     * Invalidate only the caches an admin write can actually invalidate.
     *
     * This deliberately does NOT call view:clear / route:clear / config:clear:
     * those wipe the production optimisation caches (making every subsequent
     * request slow until `optimize` runs again) and `cache:clear` would also
     * flush unrelated stores — including sessions when the session driver
     * shares the cache backend.
     */
    protected function clearApplicationCaches(): void
    {
        try {
            app(CategoryCacheService::class)->invalidateCache();
            app(LocationCacheService::class)->invalidateCache();

            foreach (self::ADMIN_DASHBOARD_CACHE_KEYS as $key) {
                Cache::forget($key);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear application caches', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Full cache flush, only triggered by the explicit "Clear Caches"
     * maintenance button — never as a side effect of a CRUD write.
     */
    protected function flushAllCaches(): void
    {
        $this->clearApplicationCaches();

        try {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        } catch (\Exception $e) {
            Log::warning('Failed to flush application caches', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
