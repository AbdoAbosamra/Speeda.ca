<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ErrorHelper;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Traits\ClearsAdminCaches;
use App\Traits\FlashesUndoableSuccess;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminCategoryController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;
    use ClearsAdminCaches;
    use FlashesUndoableSuccess;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
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

    /* =====================================================================
     |  BULK CATEGORY ACTIONS
     * ===================================================================== */

    public function bulkCategories(Request $request)
    {
        return $this->runBulkAction($request, 'categories');
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'activate' => __('admin.bulk_verb_activated'),
            'deactivate' => __('admin.bulk_verb_deactivated'),
            'delete' => __('admin.bulk_verb_deleted'),
        ];
    }

    protected function bulkQuery(string $resource): Builder
    {
        // withCount so the delete guards do not fire a query per row.
        return Category::query()->withCount(['serviceProviders', 'allChildren']);
    }

    /**
     * Categories keep the "deactivate before delete" workflow, and a category
     * that still has children or providers can never be removed in bulk.
     *
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $category)
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
}
