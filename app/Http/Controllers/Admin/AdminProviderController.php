<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Services\AdminProviderActivityMonitorService;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Provider management.
 *
 * The provider activity monitor is read-only analytics; this controller supplies
 * the write actions the admin panel was missing entirely — showing/hiding a
 * listing, verifying it, featuring it, editing the profile, and removing it.
 *
 * Listing visibility (service_providers.is_active) is deliberately separate from
 * account status (users.is_active): an admin can pull a listing off the site
 * without locking the owner out of their dashboard.
 */
class AdminProviderController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /* =====================================================================
     |  BULK ACTIONS
     * ===================================================================== */

    public function bulk(Request $request)
    {
        $response = $this->runBulkAction($request, 'providers');
        $this->forgetProviderCaches();

        return $response;
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'show' => __('admin.bulk_verb_shown'),
            'hide' => __('admin.bulk_verb_hidden'),
        ];
    }

    protected function bulkQuery(string $resource): Builder
    {
        return ServiceProvider::query()->with('user');
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $provider)
    {
        $map = [
            'show' => ['is_active', true, 'bulk_reason_already_active'],
            'hide' => ['is_active', false, 'bulk_reason_already_inactive'],
        ];

        if (!isset($map[$action])) {
            return __('admin.bulk_reason_failed');
        }

        [$column, $target, $noopReason] = $map[$action];

        if ((bool) $provider->{$column} === $target) {
            return __("admin.{$noopReason}");
        }

        $provider->update([$column => $target]);

        $this->logAction(
            $target ? 'activate' : 'deactivate',
            $provider,
            ['field' => $column, 'value' => $target],
            $provider->company_name
        );

        return true;
    }

    public function edit(ServiceProvider $serviceProvider): View
    {
        $serviceProvider->load(['user', 'category', 'location', 'photoConsentAdmin']);

        return view('admin.providers.edit', [
            'provider' => $serviceProvider,
            'categories' => Category::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('city')->get(),
        ]);
    }

    public function update(Request $request, ServiceProvider $serviceProvider): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'is_active' => ['nullable', 'boolean'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            // Optional evidence (e.g. a screenshot of the provider agreeing
            // over WhatsApp) attached alongside the photo. Never required —
            // the photo update takes effect regardless of whether proof is
            // attached; this is a record for the provider and admin to see,
            // not a gate on the upload.
            'photo_consent_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:5120'],
        ]);

        try {
            $oldValues = $serviceProvider->getOriginal();

            $data = [
                'company_name' => $validated['company_name'],
                'is_active' => $request->boolean('is_active'),
            ];

            // Nullable fields are only written when submitted, so clearing an
            // input clears the column instead of silently keeping the old value.
            foreach (['category_id', 'location_id', 'phone', 'whatsapp_number', 'address', 'bio', 'experience_years'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $data[$field] = $validated[$field];
                }
            }

            $uploadedNewPhoto = false;
            $proofPath = null;

            if ($request->hasFile('profile_image')) {
                if ($serviceProvider->profile_image && Storage::disk('public')->exists($serviceProvider->profile_image)) {
                    Storage::disk('public')->delete($serviceProvider->profile_image);
                }

                $data['profile_image'] = $request->file('profile_image')->store('service-providers/profiles', 'public');
                $uploadedNewPhoto = true;

                if ($request->hasFile('photo_consent_proof')) {
                    $proofPath = $request->file('photo_consent_proof')->store('service-providers/photo-consent-proofs', 'public');
                }
            }

            $serviceProvider->update($data);

            // Separate from update() above: this records who uploaded the
            // photo and is deliberately not mass-assignable, so it is only
            // ever set here, right after a photo the admin chose to upload.
            if ($uploadedNewPhoto) {
                $serviceProvider->recordAdminPhotoUpload(auth()->id(), $proofPath);
            }

            $this->logUpdate($serviceProvider, $oldValues, $serviceProvider->company_name);

            if ($uploadedNewPhoto) {
                $this->logAction('update', $serviceProvider, [
                    'field' => 'profile_image',
                    'consent_proof_attached' => $proofPath !== null,
                ], $serviceProvider->company_name);
            }

            $this->forgetProviderCaches();

            ErrorHelper::flashNotification(__('admin.provider_updated_successfully'), 'success');

            return redirect()->route('admin.providers.edit', $serviceProvider);
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back()->withInput();
        }
    }

    /**
     * Show/hide the public listing without touching the owner's account.
     */
    public function toggleActive(ServiceProvider $serviceProvider): RedirectResponse
    {
        return $this->toggleFlag(
            $serviceProvider,
            'is_active',
            fn (bool $on) => $on ? __('admin.provider_shown') : __('admin.provider_hidden')
        );
    }

    /**
     * Permanently remove a provider profile (the user account is kept).
     *
     * Reviews and endorsements attached to the profile go with it, and uploaded
     * media is cleaned off disk, so this is deliberately not undoable.
     */
    public function destroy(ServiceProvider $serviceProvider): RedirectResponse
    {
        try {
            $name = $serviceProvider->company_name;

            DB::transaction(function () use ($serviceProvider, $name) {
                $this->logAction('delete', $serviceProvider, [
                    'deleted' => [
                        'company_name' => $name,
                        'user_id' => $serviceProvider->user_id,
                    ],
                ], $name);

                if (method_exists($serviceProvider, 'clearMediaCollection')) {
                    $serviceProvider->clearMediaCollection('gallery');
                }

                foreach ([$serviceProvider->profile_image, $serviceProvider->business_license] as $file) {
                    if ($file && Storage::disk('public')->exists($file)) {
                        Storage::disk('public')->delete($file);
                    }
                }

                $serviceProvider->reviews()->delete();
                $serviceProvider->endorsements()->delete();
                $serviceProvider->delete();
            });

            $this->forgetProviderCaches();

            Log::warning('Provider profile deleted by admin', [
                'provider' => $name,
                'admin_id' => auth()->id(),
            ]);

            ErrorHelper::flashNotification(__('admin.provider_deleted_successfully'), 'success');

            return redirect()->route('admin.provider_activity_monitor.index');
        } catch (\Exception $e) {
            Log::error('Provider deletion failed: ' . $e->getMessage());
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back();
        }
    }

    /**
     * Shared toggle: flip a boolean column, log it, offer an undo.
     */
    private function toggleFlag(ServiceProvider $provider, string $column, callable $message): RedirectResponse
    {
        try {
            $newValue = !$provider->{$column};
            $provider->update([$column => $newValue]);

            $log = $this->logAction(
                $newValue ? 'activate' : 'deactivate',
                $provider,
                ['field' => $column, 'value' => $newValue],
                $provider->company_name
            );

            $this->forgetProviderCaches();

            ErrorHelper::flashNotification($message($newValue), 'success', $log->id);

            return redirect()->back();
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back();
        }
    }

    /**
     * Drop the caches that surface providers on the public site and dashboard.
     */
    private function forgetProviderCaches(): void
    {
        foreach ([
            'home_featured_providers',
            'home_latest_providers',
            'admin_dash_kpis',
            'admin_dash_profile_health',
            'admin_dash_action_center',
        ] as $key) {
            Cache::forget($key);
        }

        // The monitor's headline counters are cached; a listing change must be
        // reflected immediately rather than after the TTL expires.
        AdminProviderActivityMonitorService::forgetSummaryCache();
    }
}
