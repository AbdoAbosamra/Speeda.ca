<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ErrorHelper;
use App\Models\ServiceProvider;
use App\Models\SiteTestimonial;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminTestimonialController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;

    public function bulk(Request $request)
    {
        $response = $this->runBulkAction($request, 'testimonials');
        $this->warnIfNotThreeActive();

        return $response;
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'activate' => __('admin.bulk_verb_activated'),
            'deactivate' => __('admin.bulk_verb_deactivated'),
            'delete' => __('admin.bulk_verb_deleted'),
        ];
    }

    protected function bulkQuery(string $resource): \Illuminate\Database\Eloquent\Builder
    {
        return SiteTestimonial::query();
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $testimonial)
    {
        switch ($action) {
            case 'activate':
                if ($testimonial->is_active) {
                    return __('admin.bulk_reason_already_active');
                }
                $testimonial->update(['is_active' => true]);
                $this->logAction('activate', $testimonial, null, $testimonial->provider_name);
                break;

            case 'deactivate':
                if (!$testimonial->is_active) {
                    return __('admin.bulk_reason_already_inactive');
                }
                $testimonial->update(['is_active' => false]);
                $this->logAction('deactivate', $testimonial, null, $testimonial->provider_name);
                break;

            case 'delete':
                $this->logAction('delete', $testimonial, ['deleted' => $testimonial->toArray()], $testimonial->provider_name);
                $testimonial->delete();
                break;

            default:
                return __('admin.bulk_reason_failed');
        }

        return true;
    }

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * List all testimonials with the "3 active or none" status hint.
     */
    public function index()
    {
        $testimonials = SiteTestimonial::withDisplayRelations()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $activeCount = $testimonials->where('is_active', true)->count();

        // Options for the provider dropdown — admins pick a real provider
        // instead of retyping a name.
        $providers = ServiceProvider::query()
            ->with(['user:id,name', 'location:id,city'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->orderBy('company_name')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->company_name ?: ($p->user?->name ?: 'Provider #' . $p->id),
                'city' => $p->location?->city,
            ])
            ->values();

        $nextSortOrder = SiteTestimonial::nextSortOrder();

        return view('admin.testimonials.index', compact(
            'testimonials',
            'activeCount',
            'providers',
            'nextSortOrder'
        ));
    }

    /**
     * Store a new testimonial.
     */
    public function store(Request $request)
    {
        $validated = $this->validateTestimonial($request);

        try {
            $testimonial = SiteTestimonial::create($validated);
            $this->logCreate($testimonial, $testimonial->provider_name);

            Log::info('Site testimonial created', ['testimonial_id' => $testimonial->id]);
            ErrorHelper::flashNotification('Testimonial created successfully.', 'success');
            $this->warnIfNotThreeActive();

            return redirect()->route('admin.testimonials.index');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Update an existing testimonial.
     */
    public function update(Request $request, SiteTestimonial $testimonial)
    {
        $validated = $this->validateTestimonial($request);

        try {
            $oldValues = $testimonial->getOriginal();
            $testimonial->update($validated);
            $this->logUpdate($testimonial, $oldValues, $testimonial->provider_name);

            Log::info('Site testimonial updated', ['testimonial_id' => $testimonial->id]);
            ErrorHelper::flashNotification('Testimonial updated successfully.', 'success');
            $this->warnIfNotThreeActive();

            return redirect()->route('admin.testimonials.index');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Toggle a testimonial's active (published) status.
     */
    public function toggle(SiteTestimonial $testimonial)
    {
        try {
            $newStatus = !$testimonial->is_active;
            $testimonial->update(['is_active' => $newStatus]);
            $this->logAction($newStatus ? 'activate' : 'deactivate', $testimonial, null, $testimonial->provider_name);

            ErrorHelper::flashNotification('Testimonial status updated.', 'success');
            $this->warnIfNotThreeActive();

            return redirect()->route('admin.testimonials.index');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Delete a testimonial.
     */
    public function destroy(SiteTestimonial $testimonial)
    {
        try {
            $this->logAction('delete', $testimonial, ['deleted' => $testimonial->toArray()], $testimonial->provider_name);
            $testimonial->delete();

            ErrorHelper::flashNotification('Testimonial deleted successfully.', 'success');
            $this->warnIfNotThreeActive();

            return redirect()->route('admin.testimonials.index');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->back();
        }
    }

    /**
     * Shared validation rules.
     */
    private function validateTestimonial(Request $request): array
    {
        $validated = $request->validate([
            'service_provider_id' => 'required|exists:service_providers,id',
            'provider_title' => 'nullable|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'testimonial_text' => 'required|string|max:1000',
            'is_active' => 'nullable|boolean',
            // 1-based: position 0 reads wrong in the UI.
            'sort_order' => 'nullable|integer|min:1|max:65535',
        ], [
            'service_provider_id.required' => __('admin.testimonial_provider_required'),
            'service_provider_id.exists' => __('admin.testimonial_provider_required'),
        ]);

        // Normalize the checkbox and default the sort order to the end of the list.
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = max(1, (int) $request->input('sort_order', SiteTestimonial::nextSortOrder()));

        // The name is derived from the linked provider; the legacy column is
        // kept in sync so anything still reading it stays correct.
        $validated['provider_name'] = ServiceProvider::with('user')
            ->find($validated['service_provider_id'])
            ?->company_name ?: null;

        return $validated;
    }

    /**
     * Flash a reminder when the active count is not exactly 3, since the
     * home section only renders with exactly 3 active testimonials.
     */
    private function warnIfNotThreeActive(): void
    {
        $activeCount = SiteTestimonial::where('is_active', true)->count();

        if ($activeCount !== SiteTestimonial::DISPLAY_COUNT) {
            ErrorHelper::flashNotification(
                "Home section needs exactly 3 active testimonials to show (currently {$activeCount}).",
                'warning'
            );
        }
    }
}
