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
use App\Services\FacebookConversionService;
use App\Services\LocationClusterService;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ServiceProviderController extends Controller
{
    /**
     * Display a listing of service providers (public index page)
     */
    public function index(Request $request)
    {
        if ($request->input('category') === 'construction-services') {
            $redirectParams = $request->query();
            $redirectParams['category'] = 'renovation-construction';

            return redirect()->route('service-providers.index', $redirectParams)->setStatusCode(301);
        }

        // Eager-load Spatie media to avoid N+1 queries in provider cards.
        $query = ServiceProvider::with(['user', 'category.parent.parent', 'location', 'media'])
            ->withCount(['activeReviews as reviews_count', 'endorsements as endorsements_count'])
            // Calculate live average rating from active reviews (SINGLE QUERY PER PROVIDER)
            ->selectRaw(
                'service_providers.*,
                COALESCE(
                    (SELECT AVG(rating) FROM service_provider_reviews
                     WHERE service_provider_id = service_providers.id AND is_active = true),
                    0
                ) as live_rating'
            );

        // Only show providers whose users are active
        $query->whereHas('user', function ($userQuery) {
            $userQuery->where('is_active', true);
        });

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

        // Category filter
        if ($request->filled('category')) {
            $selectedCategory = Category::resolveFilterValue($request->input('category'));

            if ($selectedCategory) {
                $query->whereIn('category_id', $selectedCategory->providerCategoryIds());
            } else {
                $query->whereNull('id');
            }
        }

        // Location filter — with cluster mapping
        // @change 2026-04-12 TASK-3 | Switched public location filter to two named clusters with legacy ID fallback | Restrict dropdown choices without breaking existing cluster resolution | risk:LOW
        $locationClusters = [
            'cluster_montreal' => 'Laval – Montréal',
            'cluster_ottawa' => 'Ottawa – Gatineau',
        ];

        if ($request->filled('location')) {
            $selectedLocation = (string) $request->input('location');
            $clusterService = app(LocationClusterService::class);

            if (array_key_exists($selectedLocation, $locationClusters)) {
                $clusterIds = $clusterService->getClusterIdsByKey($selectedLocation);
            } elseif (ctype_digit($selectedLocation)) {
                $clusterIds = $clusterService->getClusterIds((int) $selectedLocation);
            } else {
                $clusterIds = [];
            }

            if (empty($clusterIds)) {
                $query->whereNull('id');
            } else {
                $query->whereIn('location_id', $clusterIds);
            }
        }

        // Order by LIVE rating (from subquery) instead of stored rating
        $serviceProviders = $query->orderByRaw('live_rating DESC')
            ->orderBy('views', 'desc')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::with('children')
            ->filterGroups()
            ->get()
            ->sortBy('translated_name')
            ->values();

        // Get list of revealed contacts from session
        $revealedContacts = session('revealed_contacts', []);

        return view('service-providers.index', compact('serviceProviders', 'categories', 'locationClusters', 'revealedContacts'));
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

            // Get all locations for dropdown
            $locations = Location::orderBy('city')->get();

            // Get all child categories (all 55 professions)
            $categories = Category::with('parent.parent')
                ->terminal()
                ->orderBy('name')
                ->get();

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

    /**
     * Display the specified service provider's profile (public view)
     * Anyone can view but only owner can edit
     */
    // @change 2026-04-12 TASK-1 | Added public gallery eager loading and view variables | Allow non-providers to view the gallery | risk:LOW
    public function show(Request $request, ServiceProvider $serviceProvider)
    {
        try {
            // Check if the service provider's user is active
            $user = $serviceProvider->user;
            if ($user && !$user->is_active) {
                // User is deactivated - show 404
                abort(404, __('service_provider.account_disabled'));
            }

            // Increment views only if not the owner
            if (!auth()->check() || auth()->id() !== $serviceProvider->user_id) {
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
                'media' => function($q) { $q->where('collection_name', 'gallery'); }
            ]);

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

            // Get all locations for dropdown (not needed for public view, only for owner edit)
            $locations = Location::orderBy('city')->get();

            // Get similar providers in same category (excluding self)
            // Eager-load Spatie media to render gallery thumbnails in similar-provider cards.
            $similarProviders = ServiceProvider::with(['category', 'location', 'user', 'media'])
                ->where('category_id', $serviceProvider->category_id)
                ->where('id', '!=', $serviceProvider->id)
                ->orderBy('rating', 'desc')
                ->limit(4)
                ->get();

            // Format phone number for WhatsApp
            $formattedNumber = preg_replace('/[^0-9]/', '', $serviceProvider->whatsapp_number ?? $serviceProvider->phone ?? '');

            // Check if this user has revealed this provider's contact
            $isContactRevealed = session()->has('revealed_contacts') &&
                in_array($serviceProvider->id, session('revealed_contacts', []));

            // Get all child categories (all professions) for category dropdown in edit form
            // Must match the categories loaded in profile() method to ensure consistency
            $categories = Category::with('parent.parent')
                ->terminal()
                ->orderBy('name')
                ->get();

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
                'galleryImages'
            ));
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
            // CRITICAL FIX: Handle profile image upload
            if ($request->hasFile('profile_image')) {
                try {
                    $image = $request->file('profile_image');

                    // Validate file was uploaded successfully
                    if (!$image->isValid()) {
                        throw new \Exception(__('service_provider.upload_error'));
                    }

                    // Validate it's a real image
                    $imageSize = @getimagesize($image->getRealPath());
                    if ($imageSize === false) {
                        throw new \Exception(__('service_provider.invalid_image_file'));
                    }

                    // Validate image dimensions
                    if ($imageSize[0] > 5000 || $imageSize[1] > 5000) {
                        throw new \Exception(__('service_provider.image_too_large_dimensions'));
                    }

                    // Validate minimum dimensions
                    if ($imageSize[0] < 200 || $imageSize[1] < 200) {
                        throw new \Exception(__('service_provider.image_too_small'));
                    }

                    // Generate secure filename
                    $filename = 'profile_' . $serviceProvider->id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('profile-images', $filename, 'public');

                    if (!$path) {
                        throw new \Exception(__('service_provider.failed_upload_image'));
                    }

                    $uploadedFiles[] = $path;

                    // Delete old image only after new one uploaded successfully
                    if ($serviceProvider->profile_image && Storage::disk('public')->exists($serviceProvider->profile_image)) {
                        Storage::disk('public')->delete($serviceProvider->profile_image);
                    }

                    $validated['profile_image'] = $path;

                } catch (\Exception $imgError) {
                    Log::error('Profile image upload failed', [
                        'user_id' => auth()->id(),
                        'sp_id' => $serviceProvider->id,
                        'error' => $imgError->getMessage()
                    ]);
                    throw new \Exception(__('service_provider.failed_upload_image') . ': ' . $imgError->getMessage());
                }
            }

            // CRITICAL FIX: Handle certification upload (image or PDF) with enhanced validation
            if ($request->hasFile('certification')) {
                try {
                    $certFile = $request->file('certification');

                    // Validate file was uploaded successfully
                    if (!$certFile->isValid()) {
                        throw new \Exception(__('service_provider.upload_error'));
                    }

                    $extension = strtolower($certFile->getClientOriginalExtension());

                    // Validate file based on type
                    if ($extension === 'pdf') {
                        // Enhanced PDF validation
                        $mime = $certFile->getMimeType();
                        if (!in_array($mime, ['application/pdf', 'application/x-pdf'])) {
                            throw new \Exception(__('service_provider.invalid_pdf_file'));
                        }

                        // Check if PDF is corrupted by reading first few bytes
                        $handle = @fopen($certFile->getRealPath(), 'r');
                        if ($handle === false) {
                            throw new \Exception(__('service_provider.cannot_read_file'));
                        }

                        $header = fread($handle, 5);
                        fclose($handle);

                        if ($header !== '%PDF-') {
                            throw new \Exception(__('service_provider.corrupted_pdf_file'));
                        }

                    } else {
                        // Validate certification image
                        $imageSize = @getimagesize($certFile->getRealPath());
                        if ($imageSize === false) {
                            throw new \Exception(__('service_provider.invalid_certification_image'));
                        }

                        // Validate certification image dimensions
                        if ($imageSize[0] < 300 || $imageSize[1] < 300) {
                            throw new \Exception(__('service_provider.certification_too_small'));
                        }

                        if ($imageSize[0] > 10000 || $imageSize[1] > 10000) {
                            throw new \Exception(__('service_provider.certification_too_large'));
                        }
                    }

                    // Generate secure filename
                    $certFilename = 'certification_' . $serviceProvider->id . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                    $certPath = $certFile->storeAs('certifications', $certFilename, 'public');

                    if (!$certPath) {
                        throw new \Exception(__('service_provider.failed_upload_certification'));
                    }

                    $uploadedFiles[] = $certPath;

                    // Delete old certification only after new one uploaded successfully
                    if ($serviceProvider->certification && Storage::disk('public')->exists($serviceProvider->certification)) {
                        Storage::disk('public')->delete($serviceProvider->certification);
                    }

                    $validated['certification'] = $certPath;
                    $validated['is_certified'] = true;

                    // Log certification upload
                    Log::info('Certification uploaded successfully', [
                        'user_id' => auth()->id(),
                        'sp_id' => $serviceProvider->id,
                        'file_type' => $extension,
                        'file_size' => $certFile->getSize(),
                        'file_name' => $certFilename,
                        'stored_path' => $certPath
                    ]);

                } catch (\Exception $certError) {
                    Log::error('Certification upload failed', [
                        'user_id' => auth()->id(),
                        'sp_id' => $serviceProvider->id,
                        'error' => $certError->getMessage(),
                        'file_size' => $certFile->getSize() ?? 0
                    ]);
                    throw new \Exception(__('service_provider.failed_upload_certification') . ': ' . $certError->getMessage());
                }
            }

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

            // Add certification if uploaded
            if (isset($validated['certification'])) {
                $updateData['certification'] = $validated['certification'];
                $updateData['is_certified'] = true;
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
                            $serviceProvider->addMedia($file)->toMediaCollection('provider_gallery');
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
                ->toMediaCollection('provider_gallery');

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
            ->where('collection_name', 'provider_gallery')
            ->firstOrFail();
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
