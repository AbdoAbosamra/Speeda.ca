<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CalculateProfileCompletionAction;
use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Services\AdminProviderActivityMonitorService;
use App\Services\LocationClusterService;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
    use HandlesBulkActions;
    use LogsAdminActions;

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

        if (! isset($map[$action])) {
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
        $serviceProvider->load(['user', 'category', 'location', 'photoConsentAdmin', 'locations']);

        $filterableLocations = app(LocationClusterService::class)->getFilterableLocations();
        $galleryMedia = $serviceProvider->getMedia('gallery');

        return view('admin.providers.edit', [
            'provider' => $serviceProvider,
            'categories' => Category::active()->orderBy('name')->get(),
            'locations' => Location::active()->orderBy('city')->get(),
            'filterableLocations' => $filterableLocations,
            'serviceAreaIds' => $serviceProvider->locations->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'galleryMedia' => $galleryMedia,
            'galleryMax' => ServiceProvider::MAX_GALLERY_IMAGES,
            'languageOptions' => [
                'ar' => __('service_provider.arabic'),
                'en' => __('service_provider.english'),
                'fr' => __('service_provider.french'),
            ],
        ]);
    }

    public function update(Request $request, ServiceProvider $serviceProvider): RedirectResponse
    {
        $filterableIds = app(LocationClusterService::class)->getFilterableLocationIds();

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
            'is_certified' => ['nullable', 'boolean'],
            'services_offered' => ['nullable', 'string', 'max:1000'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'in:ar,en,fr'],
            'service_areas' => ['nullable', 'array', 'max:50'],
            'service_areas.*' => ['integer', 'distinct', Rule::in($filterableIds)],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            // Optional evidence (e.g. a screenshot of the provider agreeing
            // over WhatsApp) attached alongside the photo. Never required —
            // the photo update takes effect regardless of whether proof is
            // attached; this is a record for the provider and admin to see,
            // not a gate on the upload.
            'photo_consent_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:5120'],
            'remove_consent_proof' => ['nullable', 'boolean'],
            'certification' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'remove_certification' => ['nullable', 'boolean'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_gallery_ids' => ['nullable', 'array'],
            'remove_gallery_ids.*' => ['integer'],
        ]);

        try {
            $oldValues = $serviceProvider->getOriginal();
            $uploadedNewPhoto = false;
            $proofPath = null;
            $galleryChanged = false;

            DB::transaction(function () use (
                $request,
                $serviceProvider,
                $validated,
                &$uploadedNewPhoto,
                &$proofPath,
                &$galleryChanged
            ) {
                $data = [
                    'company_name' => $validated['company_name'],
                    'is_active' => $request->boolean('is_active'),
                    'languages' => array_values($validated['languages'] ?? []),
                ];

                if ($request->has('is_certified')) {
                    $data['is_certified'] = $request->boolean('is_certified');
                }

                // Nullable fields are only written when submitted, so clearing an
                // input clears the column instead of silently keeping the old value.
                foreach (['category_id', 'location_id', 'phone', 'whatsapp_number', 'address', 'bio', 'experience_years'] as $field) {
                    if (array_key_exists($field, $validated)) {
                        $data[$field] = $validated[$field];
                    }
                }

                if (array_key_exists('services_offered', $validated)) {
                    $raw = trim((string) ($validated['services_offered'] ?? ''));
                    $data['services_offered'] = $raw === ''
                        ? []
                        : array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($s) => $s !== ''));
                }

                if ($request->hasFile('profile_image')) {
                    // Store first, delete second. Deleting the old file up front
                    // means a failed write leaves the provider with no photo at
                    // all — and file deletes are not covered by this transaction,
                    // so the rollback cannot bring it back.
                    $previousImage = $serviceProvider->profile_image;

                    $data['profile_image'] = $request->file('profile_image')->store('service-providers/profiles', 'public');
                    $uploadedNewPhoto = true;

                    if ($previousImage
                        && $previousImage !== $data['profile_image']
                        && Storage::disk('public')->exists($previousImage)) {
                        Storage::disk('public')->delete($previousImage);
                    }
                }

                if ($request->boolean('remove_consent_proof') && $serviceProvider->photo_consent_proof_path) {
                    if (Storage::disk('public')->exists($serviceProvider->photo_consent_proof_path)) {
                        Storage::disk('public')->delete($serviceProvider->photo_consent_proof_path);
                    }
                    $serviceProvider->forceFill([
                        'photo_consent_proof_path' => null,
                    ])->save();
                }

                if ($request->hasFile('photo_consent_proof')) {
                    $previousProof = $serviceProvider->photo_consent_proof_path;

                    $proofPath = $request->file('photo_consent_proof')->store('service-providers/photo-consent-proofs', 'public');

                    if ($previousProof
                        && $previousProof !== $proofPath
                        && Storage::disk('public')->exists($previousProof)) {
                        Storage::disk('public')->delete($previousProof);
                    }

                    $serviceProvider->forceFill([
                        'photo_consent_proof_path' => $proofPath,
                        'photo_consent_admin_id' => auth()->id(),
                        'photo_uploaded_by_admin_at' => $serviceProvider->photo_uploaded_by_admin_at ?? now(),
                    ])->save();
                }

                if ($request->boolean('remove_certification') && $serviceProvider->certification) {
                    if (Storage::disk('public')->exists($serviceProvider->certification)) {
                        Storage::disk('public')->delete($serviceProvider->certification);
                    }
                    $data['certification'] = null;
                    $data['is_certified'] = false;
                }

                if ($request->hasFile('certification')) {
                    $previousCertification = $serviceProvider->certification;

                    $data['certification'] = $request->file('certification')->store('service-providers/certifications', 'public');
                    $data['is_certified'] = true;

                    if ($previousCertification
                        && $previousCertification !== $data['certification']
                        && Storage::disk('public')->exists($previousCertification)) {
                        Storage::disk('public')->delete($previousCertification);
                    }
                }

                $serviceProvider->update($data);

                // Separate from update() above: this records who uploaded the
                // photo and is deliberately not mass-assignable, so it is only
                // ever set here, right after a photo the admin chose to upload.
                if ($uploadedNewPhoto) {
                    $serviceProvider->recordAdminPhotoUpload(auth()->id(), $proofPath ?? $serviceProvider->photo_consent_proof_path);
                }

                // The hidden has_service_areas marker distinguishes "no change"
                // from "cleared all", matching the provider-facing form.
                if ($request->has('has_service_areas')) {
                    $primaryLocationId = (int) ($data['location_id'] ?? $serviceProvider->location_id);
                    $filterableIds = app(LocationClusterService::class)->getFilterableLocationIds();

                    $areaIds = collect($validated['service_areas'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->filter(fn ($id) => in_array($id, $filterableIds, true))
                        ->reject(fn ($id) => $id === $primaryLocationId)
                        ->unique()
                        ->values();

                    $serviceProvider->locations()->sync(
                        $areaIds->mapWithKeys(fn ($id) => [$id => ['is_active' => true]])->all()
                    );
                }

                $removeIds = collect($validated['remove_gallery_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values();

                if ($removeIds->isNotEmpty()) {
                    $serviceProvider->getMedia('gallery')
                        ->whereIn('id', $removeIds->all())
                        ->each(function (Media $media) {
                            $media->delete();
                        });
                    $galleryChanged = true;
                }

                if ($request->hasFile('gallery_images')) {
                    $remainingSlots = ServiceProvider::MAX_GALLERY_IMAGES - $serviceProvider->getMedia('gallery')->count();
                    $files = collect($request->file('gallery_images', []))
                        ->filter()
                        ->take(max(0, $remainingSlots));

                    foreach ($files as $file) {
                        try {
                            $serviceProvider->addMedia($file)->toMediaCollection('gallery');
                        } catch (\Throwable $conversionError) {
                            // Spatie may throw after the original is stored when image
                            // conversions hit a driver mismatch — keep the original.
                            Log::warning('Admin gallery upload conversion failed: '.$conversionError->getMessage());
                        }
                        $galleryChanged = true;
                    }
                }
            });

            if ($galleryChanged || $uploadedNewPhoto || array_key_exists('services_offered', $validated)) {
                app(CalculateProfileCompletionAction::class)->execute($serviceProvider->fresh());
            }

            $this->logUpdate($serviceProvider->fresh(), $oldValues, $serviceProvider->company_name);

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
            Log::error('Provider deletion failed: '.$e->getMessage());
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
            $newValue = ! $provider->{$column};
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
