<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorHelper;
use App\Http\Requests\UpdateServiceProviderProfileRequest;
use App\Models\Category;
use App\Models\Location;
use App\Models\Review;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Actions\CalculateProfileCompletionAction;
use App\Actions\TrackProviderViewAction;
use App\Services\CategoryCacheService;
use App\Services\FacebookConversionService;
use App\Services\LocationCacheService;
use App\Services\LocationClusterService;
use App\Support\MergedCategoryFilters;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ServiceProviderController extends Controller
{
    /**
     * Display a listing of service providers (public index page)
     */
    public function index(Request $request, \App\Domain\SEO\Services\SeoMetaService $seoService)
    {
        if ($request->input('category') === 'construction-services') {
            $redirectParams = $request->query();
            $redirectParams['category'] = 'renovation-construction';

            return redirect()->route('service-providers.index', $redirectParams)->setStatusCode(301);
        }

        // Apply SEO
        if ($request->filled('search') || $request->filled('city')) {
            $seoService->apply('search');
        } elseif ($request->filled('category')) {
            $cat = Category::resolveFilterValue($request->input('category'));
            $seoService->apply('category', $cat);
        } else {
            $seoService->apply('category');
        }

        // Eager-load Spatie media to avoid N+1 queries in provider cards.
        // PERFORMANCE: Use withExists for endorsement check to avoid N+1 in view loop
        // PERFORMANCE: Use cached calculated_rating column instead of live subquery
        $query = ServiceProvider::with(['user', 'category.parent.parent', 'location', 'media'])
            ->withCount(['activeReviews as reviews_count', 'endorsements as endorsements_count'])
            ->when(auth()->check() && auth()->user()->isClient(), function ($q) {
                // PERFORMANCE: Preload whether current user has endorsed each provider (avoids N+1)
                $q->withExists(['endorsements as is_endorsed' => function ($query) {
                    $query->where('user_id', auth()->id());
                }]);
            });

        // Only show providers whose owner account AND profile are both active.
        $query->publiclyVisible();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'LIKE', "%{$search}%")
                    ->orWhere('bio', 'LIKE', "%{$search}%")
                    ->orWhere('services_offered', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Category filter (supports merged filter options that span several categories)
        if ($request->filled('category')) {
            $categoryValue = (string) $request->input('category');

            if (MergedCategoryFilters::isMergedKey($categoryValue)) {
                $query->whereIn('category_id', MergedCategoryFilters::providerCategoryIds($categoryValue));
            } else {
                $selectedCategory = Category::resolveFilterValue($categoryValue);

                if ($selectedCategory) {
                    $query->whereIn('category_id', $selectedCategory->providerCategoryIds());
                } else {
                    $query->whereNull('id');
                }
            }
        }

        // Location filter — with cluster mapping
        // @change 2026-04-12 TASK-3 | Switched public location filter to two named clusters with legacy ID fallback | Restrict dropdown choices without breaking existing cluster resolution | risk:LOW
        // @change 2026-06-07 | Added 6 Ontario GTA clusters to public listing filter | risk:LOW
        $clusterService = app(LocationClusterService::class);
        $locationClusters = $clusterService->getPublicFilterClusters();


        // @change 2026-06-28 | Location filter now also matches providers' added service areas | risk:LOW
        $activeLocationIds = [];
        if ($request->filled('location')) {
            $selectedLocation = (string) $request->input('location');

            if (array_key_exists($selectedLocation, $locationClusters)) {
                $clusterIds = $clusterService->getClusterIdsByKey($selectedLocation);
            } elseif (ctype_digit($selectedLocation)) {
                // @change 2026-05-04 | Added exact_location bypass to support individual city selection from homepage without clustering | risk:LOW
                if ($request->filled('exact_location')) {
                    $clusterIds = [(int) $selectedLocation];
                } else {
                    $clusterIds = $clusterService->getClusterIds((int) $selectedLocation);
                }
            } else {
                $clusterIds = [];
            }

            if (empty($clusterIds)) {
                $query->whereNull('id');
            } else {
                $activeLocationIds = array_values(array_unique(array_map('intval', $clusterIds)));
                // Match providers based here OR available here through an added service area.
                $query->availableInLocations($activeLocationIds);
            }
        }

        // Flag providers that match ONLY through an added service area, so the card
        // can show an "available in this area" badge (avoids N+1 with a single subquery).
        if (!empty($activeLocationIds)) {
            $query->withExists(['serviceAreas as is_available_area' => function ($q) use ($activeLocationIds) {
                $q->whereIn('location_id', $activeLocationIds)->where('is_active', true);
            }]);
        }

        // Display priority (applies to the default listing AND every filtered result,
        // since all filters are applied to $query before this ordering):
        //   1. Has a photo AND stars (rating)      → highest priority
        //   2. Has a photo AND years of experience
        //   3. Has a photo (only)                  → still above photo-less providers
        //   Ties are broken by most views, then by registration seniority (oldest first).
        $serviceProviders = $query
            ->orderByRaw("CASE
                WHEN profile_image IS NOT NULL AND profile_image != '' AND calculated_rating > 0 THEN 3
                WHEN profile_image IS NOT NULL AND profile_image != '' AND experience_years > 0 THEN 2
                WHEN profile_image IS NOT NULL AND profile_image != '' THEN 1
                ELSE 0
            END DESC")
            ->orderByDesc('views')
            ->orderBy('created_at')
            ->paginate(12)
            ->withQueryString();

        // PERFORMANCE: Use cached categories from Redis (24h TTL)
        // Merge the configured categories (e.g. renovation/construction) into a single option.
        $categories = MergedCategoryFilters::apply(app(CategoryCacheService::class)->getFilterGroups());

        // Get list of revealed contacts from session
        $revealedContacts = session('revealed_contacts', []);

        return view('service-providers.index', compact('serviceProviders', 'categories', 'locationClusters', 'revealedContacts', 'activeLocationIds'));
    }

    /**
     * Display the authenticated service provider's own profile with edit capabilities
     */
    public function profile()
    {
        try {
            // Get the authenticated user's service provider record
            $serviceProvider = auth()->user()->serviceProvider;

            if (!$serviceProvider) {
                ErrorHelper::flashNotification(__('service_provider.no_profile_found'), 'error');
                return redirect()->route('dashboard');
            }

            // Eager load relationships
            $serviceProvider->loadMissing(['user', 'category.parent.parent', 'location']);

            // PERFORMANCE: Use cached locations and categories from Redis (24h TTL)
            $locations = app(LocationCacheService::class)->getAllLocations();
            $categories = app(CategoryCacheService::class)->getTerminalCategories();

            // Check if user is owner (always true for this route)
            $isOwner = true;

            return view('service-providers.profile', compact(
                'serviceProvider',
                'locations',
                'categories',
                'isOwner'
            ));
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('dashboard');
        }
    }

    /**
     * Show the form for editing the specified service provider profile
     */
    public function edit(ServiceProvider $serviceProvider)
    {
        try {
            // Authorization check
            if (!auth()->check() || auth()->id() !== $serviceProvider->user_id) {
                ErrorHelper::flashNotification(__('service_provider.unauthorized_access'), 'error');
                return redirect()->route('service-providers.index');
            }

            // Redirect to profile route which handles editing
            return redirect()->route('service-providers.profile');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('dashboard');
        }
    }

    public function show(Request $request, ServiceProvider $serviceProvider, \App\Domain\SEO\Services\SeoMetaService $seoService)
    {
        try {
            // Apply SEO
            $seoService->apply('provider', $serviceProvider);

            // A profile is only reachable when the owner account is active AND
            // an admin has not deactivated the listing itself. Admins and the
            // owner may still open a deactivated profile to review it.
            $user = $serviceProvider->user;
            $isAdminViewer = auth()->check() && auth()->user()->isAdmin();
            $isOwnerViewer = auth()->check() && auth()->id() === $serviceProvider->user_id;

            if (!$isAdminViewer && !$isOwnerViewer) {
                if (($user && !$user->is_active) || !$serviceProvider->is_active) {
                    abort(404, __('service_provider.account_disabled'));
                }
            }

            // Increment views only for real visitors, not owners or admins monitoring the site.
            $isAdmin = auth()->check() && auth()->user()->isAdmin();
            $isOwner = auth()->check() && auth()->id() === $serviceProvider->user_id;

            if (!$isAdmin && !$isOwner) {
                DB::table('service_providers')
                    ->where('id', $serviceProvider->id)
                    ->increment('views');
                $serviceProvider->refresh(); // Reload to get updated views count

                // Internal analytics (spam-protected by session fingerprint within 24h)
                // PRIVACY: No IP address stored
                app(TrackProviderViewAction::class)->execute(
                    $serviceProvider->id
                );
            }

            // === STEP 1: Calculate review statistics FIRST (before eager loading) ===
            // Use fresh query builder to avoid conflicts with eager loading
            $activeReviewsData = Review::where('service_provider_id', $serviceProvider->id)
                ->where('is_active', true)
                ->selectRaw('
                    COUNT(*) as total_count,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                ')
                ->first();

            $totalCount = (int) ($activeReviewsData->total_count ?? 0);

            // Build star breakdown with percentages
            $starCounts = [
                5 => (int) ($activeReviewsData->five_star ?? 0),
                4 => (int) ($activeReviewsData->four_star ?? 0),
                3 => (int) ($activeReviewsData->three_star ?? 0),
                2 => (int) ($activeReviewsData->two_star ?? 0),
                1 => (int) ($activeReviewsData->one_star ?? 0),
            ];

            // Calculate percentages for each star level
            $starBreakdown = [];
            foreach ($starCounts as $rating => $count) {
                $percentage = $totalCount > 0 ? ($count / $totalCount) * 100 : 0;
                $starBreakdown[$rating] = [
                    'count' => $count,
                    'percentage' => round($percentage, 1),
                ];
            }

            $reviewStats = [
                'total_count' => $totalCount,
                'average_rating' => $activeReviewsData->average_rating
                    ? round($activeReviewsData->average_rating, 1)
                    : 0,
                '5_star' => $starCounts[5],
                '4_star' => $starCounts[4],
                '3_star' => $starCounts[3],
                '2_star' => $starCounts[2],
                '1_star' => $starCounts[1],
                'breakdown' => $starBreakdown,
            ];

            // === STEP 2: Eager load relationships for display ===
            $serviceProvider->loadMissing([
                'user',
                'category.parent.parent',
                'location',
                'activeReviews.client',
                'activeReviews.approvedBy',
                'endorsements',
                'serviceAreas',
                'media' => function($q) { $q->where('collection_name', 'gallery'); }
            ]);

            // Location IDs the owner has marked as available (for the edit form pre-selection).
            $serviceAreaLocationIds = $serviceProvider->serviceAreaLocationIds();

            // Only offer regions that are reachable through the public location filter.
            $serviceAreaLocations = app(LocationClusterService::class)->getFilterableLocations();

            // Prepare public gallery images payload
            $galleryImages = collect($serviceProvider->getMedia('gallery'))->map(function ($media) use ($serviceProvider) {
                return [
                    'id' => $media->id,
                    'thumb_url' => method_exists($serviceProvider, 'getMediaPublicUrl') 
                        ? ($serviceProvider->getMediaPublicUrl($media, $media->hasGeneratedConversion('gallery_thumb') ? 'gallery_thumb' : null) ?? $media->getUrl())
                        : $media->getUrl($media->hasGeneratedConversion('gallery_thumb') ? 'gallery_thumb' : ''),
                    'large_url' => method_exists($serviceProvider, 'getMediaPublicUrl')
                        ? ($serviceProvider->getMediaPublicUrl($media, $media->hasGeneratedConversion('gallery_large') ? 'gallery_large' : null) ?? $media->getUrl())
                        : $media->getUrl($media->hasGeneratedConversion('gallery_large') ? 'gallery_large' : '')
                ];
            });

            // === STEP 3: Get paginated reviews for display ===
            $reviews = $serviceProvider->activeReviews()
                ->with(['client', 'approvedBy'])
                ->orderByDesc('created_at')
                ->paginate(5, ['*'], 'reviews_page')
                ->withQueryString();

            // PERFORMANCE: Use cached locations from Redis (24h TTL)
            $locations = app(LocationCacheService::class)->getAllLocations();

            // Get similar providers in same category (excluding self)
            // Eager-load relationships and counts to avoid N+1 in similar-provider cards
            $similarProviders = ServiceProvider::with(['category', 'location', 'user', 'media'])
                ->withCount(['activeReviews', 'endorsements'])
                ->where('category_id', $serviceProvider->category_id)
                ->where('id', '!=', $serviceProvider->id)
                ->orderBy('rating', 'desc')
                ->limit(4)
                ->get();

            // PERFORMANCE: Use cached categories from Redis (24h TTL)
            $categories = app(CategoryCacheService::class)->getTerminalCategories();

            // Format phone number for WhatsApp
            $formattedNumber = preg_replace('/[^0-9]/', '', $serviceProvider->whatsapp_number ?? $serviceProvider->phone ?? '');

            // Check if this user has revealed this provider's contact
            $isContactRevealed = session()->has('revealed_contacts') &&
                in_array($serviceProvider->id, session('revealed_contacts', []));

            // Get all child categories (all professions) for category dropdown in edit form
            // Must match the categories loaded in profile() method to ensure consistency
            $categories = app(CategoryCacheService::class)->getTerminalCategories();

            // Check if current user has already reviewed this provider
            $hasReviewed = auth()->check() && auth()->user()->isClient()
                ? $serviceProvider->reviews()->where('client_id', auth()->id())->exists()
                : false;

            // Check if current user has rated this provider
            $userRating = null;
            if (auth()->check()) {
                $userRating = \App\Models\Rating::where('user_id', auth()->id())
                    ->where('service_provider_id', $serviceProvider->id)
                    ->first();
            }

            // === CAPI: Send ViewContent event (server-side, non-blocking) ===
            try {
                $capiEventId = 'vc_' . $serviceProvider->id . '_' . time();
                app(FacebookConversionService::class)->trackViewContent($capiEventId, [
                    'content_name' => $serviceProvider->company_name ?? $serviceProvider->user->name,
                    'content_ids' => [(string) $serviceProvider->id],
                    'content_category' => $serviceProvider->category->translated_name ?? 'Uncategorized',
                    'content_type' => 'service_provider',
                ]);
            } catch (\Throwable $e) {
                // Silently ignore CAPI errors
            }

            return view('service-providers.show', compact(
                'serviceProvider',
                'reviews',
                'reviewStats',
                'locations',
                'categories',
                'similarProviders',
                'formattedNumber',
                'isContactRevealed',
                'hasReviewed',
                'userRating',
                'galleryImages',
                'serviceAreaLocationIds',
                'serviceAreaLocations'
            ));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            // Deliberate aborts (404 for a hidden or deactivated profile) must
            // reach the client as-is. Swallowing them into a redirect told
            // crawlers the page had moved instead of that it does not exist.
            throw $e;
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);
            return redirect()->route('service-providers.index');
        }
    }

    /**
     * Track when a user reveals contact information
     * Uses session to ensure privacy - only the user who clicked sees the info
     */
    public function revealContact(Request $request, ServiceProvider $serviceProvider)
    {
        // Get existing revealed contacts from session
        $revealedContacts = session('revealed_contacts', []);

        // Add this provider if not already revealed
        if (!in_array($serviceProvider->id, $revealedContacts)) {
            $revealedContacts[] = $serviceProvider->id;
            session(['revealed_contacts' => $revealedContacts]);
        }

        // ARCHITECTURE COMPLIANCE: Only return JSON for AJAX requests
        if ($request->expectsJson()) {
            // CAPI: Send Lead event (server-side, non-blocking)
            try {
                $leadEventId = 'lead_' . $serviceProvider->id . '_' . time();
                $userData = [];
                if (auth()->check()) {
                    $userData['email'] = auth()->user()->email;
                    $userData['external_id'] = auth()->id();
                }
                app(FacebookConversionService::class)->trackLead($leadEventId, [
                    'content_name' => $serviceProvider->company_name ?? $serviceProvider->user->name ?? '',
                    'content_ids' => [(string) $serviceProvider->id],
                    'contact_type' => 'whatsapp',
                ], $userData);
            } catch (\Throwable $e) {
                // Silently ignore CAPI errors
            }

            return response()->json([
                'success' => true,
                'message' => 'Contact revealed'
            ]);
        }

        // Fallback to redirect for non-AJAX requests
        return redirect()->back()->with('success', 'Contact information revealed');
    }

    /**
     * Update the specified service provider's profile (alias for update method)
     */
    public function updateProfile(UpdateServiceProviderProfileRequest $request, ServiceProvider $serviceProvider)
    {
        return $this->update($request, $serviceProvider);
    }

    /**
     * Update the specified service provider's profile
     * CRITICAL: Handles all profile updates including certification upload
     */
    public function update(UpdateServiceProviderProfileRequest $request, ServiceProvider $serviceProvider)
    {
        // Authorization is handled in the FormRequest

        // Get validated data
        $validated = $request->validated();

        // Track uploaded files for cleanup on error
        $uploadedFiles = [];

        // Start database transaction
        DB::beginTransaction();

        try {
            // Profile image is now handled via AJAX auto-save (uploadProfileImage method)
            // so we no longer process it in the main update form.



            // Prepare update data with proper field mapping (NO 'description' field!)
            $updateData = [
                'company_name' => trim($validated['business_name']),
                'bio' => isset($validated['bio']) ? trim($validated['bio']) : $serviceProvider->bio,
                'experience_years' => $validated['experience_years'] ?? $serviceProvider->experience_years,
                'phone' => $validated['phone'] ?? $serviceProvider->phone, // Use validated phone as-is (preserves DB format)
                'whatsapp_number' => isset($validated['whatsapp_number']) ? preg_replace('/[^0-9+]/', '', $validated['whatsapp_number']) : null,
                'address' => isset($validated['address']) ? trim($validated['address']) : $serviceProvider->address,
                'location_id' => $validated['location_id'] ?? $serviceProvider->location_id,
                'languages' => $validated['languages'] ?? [],
            ];

            // === CATEGORY LOCK ENFORCEMENT: Backend Rule (Defense in Depth) ===
            // BUSINESS RULE: Only allow category change if CURRENT category = "Others"
            // This is the SECOND validation layer - even if FormRequest is bypassed, this catches it
            if ($serviceProvider->category) {
                $othersNames = ['other', 'others', 'أخرى'];
                $isOthersCategory = in_array(strtolower(trim($serviceProvider->category->name)), $othersNames) ||
                    in_array(strtolower(trim($serviceProvider->category->translated_name)), $othersNames);

                // If current category is NOT "Others", reject any category_id in the request
                if (!$isOthersCategory && isset($validated['category_id']) && $validated['category_id'] !== $serviceProvider->category_id) {
                    throw new \Exception("Category cannot be changed. You can only change category if it is currently set to 'Others'.");
                }
            }

            // Don't allow category_id change after initial registration (UNLESS current = "Others")
            // Only include category_id in update if it came through validation and is allowed
            // (already filtered by prepareForValidation() in FormRequest)
            // 'category_id' => $validated['category_id'] ?? $serviceProvider->category_id,

            // Handle services_offered (convert comma-separated string to array)
            if (isset($validated['services_offered'])) {
                if (is_string($validated['services_offered']) && !empty($validated['services_offered'])) {
                    $services = array_map('trim', explode(',', $validated['services_offered']));
                    $updateData['services_offered'] = array_filter($services, fn($s) => !empty($s));
                } elseif (is_array($validated['services_offered'])) {
                    $updateData['services_offered'] = array_filter($validated['services_offered'], fn($s) => !empty($s));
                } else {
                    $updateData['services_offered'] = [];
                }
            }

            // Add profile image if uploaded
            if (isset($validated['profile_image'])) {
                $updateData['profile_image'] = $validated['profile_image'];
            }



            // Add category if provided and allowed (FormRequest already filters/removes it if not allowed)
            if (isset($validated['category_id'])) {
                $updateData['category_id'] = $validated['category_id'];
            }

            // Update the service provider
            $updated = $serviceProvider->update($updateData);

            if (!$updated) {
                throw new \Exception(__('service_provider.profile_update_failed'));
            }

            // === Service Areas: sync the additional locations the provider is available in ===
            // The hidden `has_service_areas` marker lets us distinguish "no change" from
            // "cleared all", so unchecking every box correctly removes existing areas.
            if ($request->has('has_service_areas')) {
                $primaryLocationId = (int) ($updateData['location_id'] ?? $serviceProvider->location_id);
                $filterableIds = app(LocationClusterService::class)->getFilterableLocationIds();

                $areaIds = collect($validated['service_areas'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => in_array($id, $filterableIds, true)) // only filterable regions
                    ->reject(fn ($id) => $id === $primaryLocationId) // primary location is implicit
                    ->unique()
                    ->values();

                $serviceProvider->locations()->sync(
                    $areaIds->mapWithKeys(fn ($id) => [$id => ['is_active' => true]])->all()
                );
            }

            // Log successful update
            Log::info('Service Provider profile updated successfully', [
                'user_id' => auth()->id(),
                'sp_id' => $serviceProvider->id,
                'updated_fields' => array_keys($updateData)
            ]);

            // Commit the transaction
            DB::commit();

            // Provider gallery upload:
            // - Stored by Spatie media library
            // - Conversions are generated on save
            // - Images are additive; each one can also be replaced/deleted individually
            try {
                if ($request->hasFile('gallery_images')) {
                    $files = $request->file('gallery_images', []);

                    if (is_array($files)) {
                        foreach ($files as $file) {
                            $serviceProvider->addMedia($file)->toMediaCollection('gallery');
                        }
                    }

                    // Ensure completion percent is updated after gallery changes.
                    app(CalculateProfileCompletionAction::class)->execute($serviceProvider);
                }
            } catch (\Throwable $galleryError) {
                Log::error('Gallery upload failed', [
                    'user_id' => auth()->id(),
                    'sp_id' => $serviceProvider->id,
                    'error' => $galleryError->getMessage(),
                ]);
                ErrorHelper::flashNotification(
                    __('service_provider.gallery_upload_failed'),
                    'error'
                );
            }

            ErrorHelper::flashNotification(
                __('service_provider.profile_updated_successfully'),
                'success'
            );

            return redirect()->route('service-providers.show', $serviceProvider->id)
                ->with('success', __('service_provider.profile_updated_successfully'));

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Clean up uploaded files on error
            foreach ($uploadedFiles as $file) {
                if (Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            // Log the error with full context
            Log::error('Service Provider profile update failed', [
                'user_id' => auth()->id(),
                'sp_id' => $serviceProvider->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $error = ErrorHelper::handle($e, __('service_provider.profile_update_failed'));
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back()
                ->withInput()
                ->withErrors(['update_error' => $error['message']]);
        }
    }

    /**
     * Handle AJAX profile image upload
     */
    public function uploadProfileImage(Request $request)
    {
        try {
            $request->validate([
                'profile_image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            ]);

            $serviceProvider = auth()->user()->serviceProvider;

            if (!$serviceProvider) {
                return response()->json([
                    'success' => false,
                    'message' => __('service_provider.no_profile_found')
                ], 404);
            }

            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($serviceProvider->profile_image && Storage::disk('public')->exists($serviceProvider->profile_image)) {
                    Storage::disk('public')->delete($serviceProvider->profile_image);
                }

                // Store new image
                $image = $request->file('profile_image');
                $filename = 'profile_' . $serviceProvider->id . '_' . time() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('profile-images', $filename, 'public');

                // Update service provider record
                $serviceProvider->update(['profile_image' => $path]);

                // Recalculate profile completion after image upload
                $completionPercent = app(CalculateProfileCompletionAction::class)->execute($serviceProvider);

                return response()->json([
                    'success' => true,
                    'message' => __('service_provider.profile_image_updated'),
                    'image_url' => Storage::url($path),
                    'completion_percent' => $completionPercent,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('service_provider.failed_upload_image')
            ], 400);

        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            return response()->json([
                'success' => false,
                'message' => $error['message']
            ], 500);
        }
    }

    public function deleteGalleryImage(ServiceProvider $serviceProvider, int $mediaId)
    {
        try {
            if (!auth()->check() || auth()->id() !== $serviceProvider->user_id) {
                ErrorHelper::flashNotification(__('service_provider.unauthorized_access'), 'error');
                return redirect()->route('service-providers.show', $serviceProvider->id);
            }

            $media = $this->resolveGalleryMedia($serviceProvider, $mediaId);
            $media->delete();

            app(CalculateProfileCompletionAction::class)->execute($serviceProvider->fresh());

            ErrorHelper::flashNotification(__('service_provider.gallery_image_deleted'), 'success');

            return redirect()
                ->route('service-providers.show', $serviceProvider->id)
                ->with('success', __('service_provider.gallery_image_deleted'));
        } catch (\Exception $e) {
            Log::error('Gallery image delete failed', [
                'user_id' => auth()->id(),
                'sp_id' => $serviceProvider->id,
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);

            $error = ErrorHelper::handle($e, __('service_provider.gallery_upload_failed'));
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->route('service-providers.show', $serviceProvider->id);
        }
    }

    public function replaceGalleryImage(Request $request, ServiceProvider $serviceProvider, int $mediaId)
    {
        try {
            if (!auth()->check() || auth()->id() !== $serviceProvider->user_id) {
                ErrorHelper::flashNotification(__('service_provider.unauthorized_access'), 'error');
                return redirect()->route('service-providers.show', $serviceProvider->id);
            }

            $request->validate([
                'gallery_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            ]);

            $media = $this->resolveGalleryMedia($serviceProvider, $mediaId);

            $serviceProvider
                ->addMedia($request->file('gallery_image'))
                ->toMediaCollection('gallery');

            $media->delete();

            app(CalculateProfileCompletionAction::class)->execute($serviceProvider->fresh());

            ErrorHelper::flashNotification(__('service_provider.gallery_image_replaced'), 'success');

            return redirect()
                ->route('service-providers.show', $serviceProvider->id)
                ->with('success', __('service_provider.gallery_image_replaced'));
        } catch (\Exception $e) {
            Log::error('Gallery image replace failed', [
                'user_id' => auth()->id(),
                'sp_id' => $serviceProvider->id,
                'media_id' => $mediaId,
                'error' => $e->getMessage(),
            ]);

            $error = ErrorHelper::handle($e, __('service_provider.gallery_upload_failed'));
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->route('service-providers.show', $serviceProvider->id);
        }
    }

    private function resolveGalleryMedia(ServiceProvider $serviceProvider, int $mediaId): Media
    {
        return Media::query()
            ->whereKey($mediaId)
            ->where('model_type', ServiceProvider::class)
            ->where('model_id', $serviceProvider->id)
            ->where('collection_name', 'gallery')
            ->firstOrFail();
    }

    /**
     * Mark the profile completion popup as dismissed in session for the current provider
     */
    public function dismissCompletionPopup(Request $request)
    {
        session(['profile_completion_popup_dismissed' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Completion popup dismissed for current session'
        ]);
    }

    /**
     * Mark the profile engagement popup as dismissed for the authenticated provider
     */
    public function dismissEngagementPopup(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $serviceProvider = $user->serviceProvider;

            if ($serviceProvider && is_null($serviceProvider->profile_completion_popup_shown_at)) {
                $serviceProvider->update([
                    'profile_completion_popup_shown_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Engagement popup marked as dismissed'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Popup already dismissed or provider not found'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dismiss engagement popup', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error while dismissing popup'
            ], 500);
        }
    }
}
