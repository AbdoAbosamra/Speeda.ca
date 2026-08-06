<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Location;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Handles user registration and authentication logic
 * Separates business logic from controllers following SOLID principles
 */
class AuthService
{
    /**
     * Register a new user with role-specific setup
     */
    public function registerUser(array $validatedData): User
    {
        return DB::transaction(function () use ($validatedData) {
            // Create base user
            $user = $this->createUser($validatedData);

            // Setup role-specific records
            if ($user->role === 'service_provider') {
                $this->setupServiceProvider($user, $validatedData);
            }

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email
            ]);

            return $user;
        });
    }

    /**
     * Create user record
     */
    private function createUser(array $data): User
    {
        $profession = null;

        // Only process profession for service providers
        if (isset($data['profession'])) {
            if (!empty($data['profession']) && $data['profession'] !== 'other') {
                $category = Category::find($data['profession']);
                if ($category && $category->isLeaf()) {
                    $profession = $category->name;
                }
            } elseif ($data['profession'] === 'other') {
                $profession = 'Others';
            }
        }

        // @change 2026-04-12 TASK-2 | Derived a fallback client name when the form omits it | Allow email/password-only client registration without changing provider registration | risk:LOW
        return User::create([
            'name' => $this->resolveRegistrationName($data),
            'email' => $data['email'],
            'role' => $data['role'],
            'profession' => $profession,
            'password' => Hash::make($data['password']),
            'email_verified_at' => $data['role'] === 'service_provider' ? now() : null,
        ]);
    }

    private function resolveRegistrationName(array $data): string
    {
        if (!empty($data['name'])) {
            return $data['name'];
        }

        $emailPrefix = Str::before((string) ($data['email'] ?? ''), '@');
        $fallbackName = trim(str_replace(['.', '_', '-'], ' ', $emailPrefix));

        return $fallbackName !== '' ? Str::title($fallbackName) : 'Client';
    }

    /**
     * Setup service provider records
     */
    private function setupServiceProvider(User $user, array $data): void
    {
        // Handle "other" profession - find "Others" category under "Others" section
        $categoryId = null;

        // Only process profession if it exists and user is service provider
        if (isset($data['profession'])) {
            if (!empty($data['profession']) && $data['profession'] !== 'other') {
                $categoryId = (int) $data['profession'];
            } elseif ($data['profession'] === 'other') {
                // Find the "Others" category under the "Others" section
                $othersSection = Category::where('name', 'Others')
                    ->where('is_section', true)
                    ->first();

                if ($othersSection) {
                    $othersCategory = Category::where('name', 'Others')
                        ->where('parent_id', $othersSection->id)
                        ->first();

                    if ($othersCategory) {
                        $categoryId = $othersCategory->id;
                    }
                }
            }
        }

        $locationId = $this->getOrCreateLocation($data['city'] ?? null);

        // Create ServiceProvider record
        $serviceProvider = ServiceProvider::create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'location_id' => $locationId,
            'phone' => $data['mobile'] ?? null,
            'whatsapp_number' => $data['whatsapp_number'] ?? null,
            'company_name' => $user->name,
            'bio' => 'Service provider profile. Please complete your details.',
            'is_verified' => false,
            'views' => 0,
            'rating' => 0,
        ]);

        // Send Welcome Email immediately via journey service
        try {
            app(\App\Services\ProviderEmailJourneyService::class)->sendWelcomeEmail($serviceProvider);
        } catch (\Throwable $e) {
            Log::error('Failed to trigger welcome email during registration: ' . $e->getMessage());
        }

        // Deprecated: ServiceProviderProfile creation removed in favor of single ServiceProvider model

        Log::info('Service provider setup completed', [
            'service_provider_id' => $serviceProvider->id,
            'category_id' => $categoryId,
            'location_id' => $locationId
        ]);
    }

    /**
     * Get or create location by city name
     */
    private function getOrCreateLocation(?string $city): ?int
    {
        if (empty($city)) {
            return null;
        }

        $location = Location::whereRaw('LOWER(city) = ?', [mb_strtolower($city)])->first();

        if ($location) {
            return $location->id;
        }

        try {
            $newLocation = Location::create([
                'city' => $city,
                'is_active' => true
            ]);
            return $newLocation->id;
        } catch (\Exception $e) {
            Log::warning('Failed to create location', [
                'city' => $city,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get redirect path based on user role
     */
    public function getRedirectPath(User $user): string
    {
        if ($user->role === 'service_provider') {
            $serviceProvider = ServiceProvider::where('user_id', $user->id)->first();
            if ($serviceProvider) {
                return route('service-providers.show', $serviceProvider->id);
            }
            return route('dashboard');
        }

        return route('home');
    }
}
