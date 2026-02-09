<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorHelper;
use App\Http\Requests\UpdateServiceProviderProfileRequest;
use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ServiceProviderController extends Controller
{
    /**
     * Display a listing of service providers (public index page)
     */
    public function index(Request $request)
    {
        $query = ServiceProvider::with(['user', 'category', 'location']);
        // Remove is_verified filter to show all service providers

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
            $category = $request->input('category');
            if ($category === 'others') {
                $othersNames = ['other', 'others', 'أخرى'];
                $otherIds = Category::all()->filter(function ($c) use ($othersNames) {
                    return in_array(strtolower(trim($c->translated_name)), $othersNames);
                })->pluck('id')->toArray();

                if (!empty($otherIds)) {
                    $query->whereIn('category_id', $otherIds);
                } else {
                    // If there are no 'others' categories, return no results
                    $query->whereNull('id');
                }
            } else {
                $query->where('category_id', $category);
            }
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location_id', $request->input('location'));
        }

        $serviceProviders = $query->orderBy('rating', 'desc')
            ->orderBy('views', 'desc')
            ->paginate(12);

        $categories = Category::orderBy('name')->get();
        $locations = Location::orderBy('city')->get();

        // Get list of revealed contacts from session
        $revealedContacts = session('revealed_contacts', []);

        return view('service-providers.index', compact('serviceProviders', 'categories', 'locations', 'revealedContacts'));
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
            $serviceProvider->loadMissing(['user', 'category', 'location']);

            // Get all locations for dropdown
            $locations = Location::orderBy('city')->get();

            // Get all child categories (all 55 professions)
            $categories = Category::whereNotNull('parent_id')
                ->where('is_active', 1)
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
    public function show(ServiceProvider $serviceProvider)
    {
        try {
            // Increment views only if not the owner
            if (!auth()->check() || auth()->id() !== $serviceProvider->user_id) {
                DB::table('service_providers')
                    ->where('id', $serviceProvider->id)
                    ->increment('views');
                $serviceProvider->refresh(); // Reload to get updated views count
            }

            // Eager load relationships
            $serviceProvider->loadMissing(['user', 'category', 'location']);

            // Get all locations for dropdown (not needed for public view, only for owner edit)
            $locations = Location::orderBy('city')->get();

            // Get similar providers in same category (excluding self)
            $similarProviders = ServiceProvider::with(['category', 'location', 'user'])
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

            // Get all categories for dropdown (needed for the search bar in header)
            $categories = Category::orderBy('name')->get();

            return view('service-providers.show', compact(
                'serviceProvider',
                'locations',
                'categories',
                'similarProviders',
                'formattedNumber',
                'isContactRevealed'
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
            ];

            // Don't allow category_id change after initial registration
            // Remove this line to prevent category updates
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

                return response()->json([
                    'success' => true,
                    'message' => __('service_provider.profile_image_updated'),
                    'image_url' => Storage::url($path)
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
}
