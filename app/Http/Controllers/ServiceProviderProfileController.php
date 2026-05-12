<?php

namespace App\Http\Controllers;

use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Location;
use App\Services\CategoryCacheService;
use App\Services\LocationCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceProviderProfileController extends Controller
{
    /**
     * Show the form for creating a new service provider profile
     */
    public function create()
    {
        // التحقق من أن المستخدم ليس لديه ملف مقدم خدمة مسبقًا
        if (auth()->user()->serviceProvider) {
            // If the user already has a provider profile, redirect to its public page
            $sp = auth()->user()->serviceProvider;
            return redirect()->route('service-providers.show', $sp->id)
                ->with('info', 'You already have a service provider profile.');
        }

        // PERFORMANCE: Use cached categories and locations from Redis (24h TTL)
        $categories = app(CategoryCacheService::class)->getTerminalCategories();
        $locations = app(LocationCacheService::class)->getActiveLocations();

        // The dedicated create view is not present in the repository. Redirect to
        // the provider listing to avoid returning a missing view (per constraints
        // do not create new files).
        return redirect()->route('service-providers.index')
            ->with('info', 'Creating provider profiles is currently unavailable.');
    }

    /**
     * Store a newly created service provider profile
     */
    public function store(Request $request)
    {
        // التحقق من أن المستخدم ليس لديه ملف مسبق
        if (auth()->user()->serviceProvider) {
            return redirect()->route('service-providers.manage')
                ->with('error', 'You already have a service provider profile.');
        }

        $validated = $request->validate([
            'business_name'     => 'required|string|max:255|unique:service_providers,business_name',
            'category_id'       => 'required|exists:categories,id',
            'location_id'       => 'required|exists:locations,id',
            'description'       => 'required|string|min:100|max:2000',
            'contact_phone'     => 'required|string|max:20',
            'contact_email'     => 'required|email',
            'experience_years'  => 'required|integer|min:0|max:50',
            'services_offered'  => 'required|array|min:1',
            'services_offered.*'=> 'string|max:100',
            'languages'         => 'nullable|array',
            'specializations'   => 'nullable|array',
            'business_type'     => 'required|in:individual,company',
            'company_name'      => 'required_if:business_type,company|nullable|string|max:255',
            'emergency_available' => 'boolean',
            'available_weekends' => 'boolean',
            'available_evenings' => 'boolean',
            'profile_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $validated['user_id'] = auth()->id();
                $validated['business_slug'] = $this->generateUniqueSlug($validated['business_name']);
                $validated['is_verified'] = true; // Auto-verified

                // تعيين القيم الافتراضية
                $validated['views'] = 0;
                $validated['average_rating'] = 0;
                $validated['total_reviews'] = 0;
                $validated['completed_jobs'] = 0;

                // Handle profile image upload
                if ($request->hasFile('profile_image')) {
                    $validated['profile_image'] = $request->file('profile_image')
                        ->store('service-providers/profiles', 'public');
                }

                $serviceProvider = ServiceProvider::create($validated);

                // تحديث دور المستخدم إذا لزم الأمر
                if (auth()->user()->role !== 'service_provider') {
                    auth()->user()->update(['role' => 'service_provider']);
                }
            });

            return redirect()->route('service-providers.manage')
                ->with('success', 'Service provider profile created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create service provider profile. Please try again.');
        }
    }

    /**
     * Generate unique slug for business
     */
    private function generateUniqueSlug($businessName)
    {
        $slug = Str::slug($businessName);
        $count = ServiceProvider::where('business_slug', 'like', $slug . '%')->count();

        return $count ? $slug . '-' . ($count + 1) : $slug;
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit(ServiceProvider $serviceProvider)
    {
        $this->authorize('update', $serviceProvider);
        // The dedicated edit view is not present. Redirect to the public
        // service provider page as a conservative fallback.
        return redirect()->route('service-providers.show', $serviceProvider->id);
    }

    /**
     * Manage service provider profile
     */
    public function manage()
    {
        $serviceProvider = auth()->user()->serviceProvider;

        if (!$serviceProvider) {
            // Redirect to the providers index; profile creation page is not
            // available (we avoid creating new files).
            return redirect()->route('service-providers.index')
                ->with('info', 'Please create your service provider profile first.');
        }

        // The management view is not present. Redirect to the public profile.
        return redirect()->route('service-providers.show', $serviceProvider->id);
    }

    /**
     * Update the specified service provider
     */
    public function update(Request $request, ServiceProvider $serviceProvider)
    {
        $this->authorize('update', $serviceProvider);

        $validated = $request->validate([
            'business_name'     => 'required|string|max:255|unique:service_providers,business_name,' . $serviceProvider->id,
            'category_id'       => 'required|exists:categories,id',
            'location_id'       => 'required|exists:locations,id',
            'description'       => 'required|string|min:100|max:2000',
            'contact_phone'     => 'required|string|max:20',
            'contact_email'     => 'required|email',
            'experience_years'  => 'required|integer|min:0|max:50',
            'services_offered'  => 'required|array|min:1',
            'services_offered.*'=> 'string|max:100',
            'languages'         => 'nullable|array',
            'specializations'   => 'nullable|array',
            'business_type'     => 'required|in:individual,company',
            'company_name'      => 'required_if:business_type,company|nullable|string|max:255',
            'emergency_available' => 'boolean',
            'available_weekends' => 'boolean',
            'available_evenings' => 'boolean',
            'profile_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($serviceProvider->profile_image) {
                    Storage::disk('public')->delete($serviceProvider->profile_image);
                }
                $validated['profile_image'] = $request->file('profile_image')
                    ->store('service-providers/profiles', 'public');
            }

            $serviceProvider->update($validated);

            // معالجة صور المعرض (gallery_images) باستخدام مكتبة spatie
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $serviceProvider
                        ->addMedia($image)
                        ->toMediaCollection('provider_gallery');
                }
            }

            return redirect()->route('service-providers.manage')
                ->with('success', 'Service provider profile updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update service provider profile. Please try again.');
        }
    }
}
