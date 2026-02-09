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
use App\Models\Comment;
use App\Models\User;
use App\Services\VisitorTrackingService;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\AdminLog;

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

            $stats = [
                'liveVisitors' => $visitorStats['live_visitors'] ?? 0,
                'visitorsToday' => $this->getVisitorsToday(),
                'last7Days' => $visitorStats['last_7_days'] ?? 0,
                'last30Days' => $visitorStats['last_30_days'] ?? 0,
                'last12Months' => $visitorStats['last_12_months'] ?? 0,
                'totalVisitors' => $visitorStats['total_visitors'] ?? 0,
                'activeLocations' => Location::where('is_active', true)->count(),
                'activeCategories' => Category::where('is_active', true)->count(),
                'totalLocations' => Location::count(),
                'totalCategories' => Category::count(),
                // Pending moderation counts
                'pendingReviews' => Review::where('is_active', false)->whereNull('admin_approved_at')->count(),
                'pendingComments' => Comment::pending()->count(),
                'newUsersToday' => User::whereDate('created_at', today())->count(),
            ];

            return view('admin.dashboard', compact('stats'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            Log::error('Admin dashboard error: ' . $e->getMessage());
            ErrorHelper::flashNotification($error['message'], $error['type']);

            // Return view with empty stats to avoid redirect loop
            $stats = [
                'liveVisitors' => 0,
                'visitorsToday' => 0,
                'last7Days' => 0,
                'last30Days' => 0,
                'last12Months' => 0,
                'totalVisitors' => 0,
                'activeLocations' => 0,
                'activeCategories' => 0,
                'totalLocations' => 0,
                'totalCategories' => 0,
                'pendingReviews' => 0,
                'pendingComments' => 0,
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
        return Visitor::whereDate('visited_at', today())
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

            return view('admin.categories.index', compact('sections', 'allCategories'));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('admin.dashboard');
        }
    }

    /**
     * Store a new category
     */
    public function storeCategory(StoreCategoryRequest $request)
    {
        try {
            $validated = $request->validated();

            return DB::transaction(function () use ($validated) {
                $category = Category::create([
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?? null,
                    'parent_id' => $validated['parent_id'] ?? null,
                    'is_section' => $validated['is_section'] ?? false,
                    'is_active' => $validated['is_active'] ?? true,
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'icon' => $validated['icon'] ?? null,
                    'color' => $validated['color'] ?? null,
                    'meta_title' => $validated['meta_title'] ?? null,
                    'meta_description' => $validated['meta_description'] ?? null,
                ]);

                Log::info('Category created by admin', [
                    'category_id' => $category->id,
                    'name' => $category->name,
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
     * Update a category
     */
    public function updateCategory(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $validated = $request->validated();

            return DB::transaction(function () use ($validated, $category) {
                $oldValues = $category->getOriginal(); // Capture old values before update
                // Prevent setting parent to itself (should already be validated, but double-check)
                if ($validated['parent_id'] && $validated['parent_id'] == $category->id) {
                    throw new \Exception(__('admin.cannot_set_self_as_parent'));
                }

                $category->update([
                    'name' => $validated['name'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?? $category->description,
                    'parent_id' => $validated['parent_id'] ?? $category->parent_id,
                    'is_section' => $validated['is_section'] ?? $category->is_section,
                    'is_active' => $validated['is_active'] ?? $category->is_active,
                    'sort_order' => $validated['sort_order'] ?? $category->sort_order,
                    'icon' => $validated['icon'] ?? $category->icon,
                    'color' => $validated['color'] ?? $category->color,
                    'meta_title' => $validated['meta_title'] ?? $category->meta_title,
                    'meta_description' => $validated['meta_description'] ?? $category->meta_description,
                ]);

                $log = $this->logUpdate($category, $oldValues);
                $this->clearApplicationCaches();

                $this->flashSuccessWithUndo(__('admin.category_updated_successfully'), $log);

                return redirect()->route('admin.categories');
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
        } catch (\Exception $e) {
            Log::warning('Failed to clear application caches', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
