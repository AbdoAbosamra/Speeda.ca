<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox "on" to boolean true
        if ($this->has('remember') && $this->remember === 'on') {
            $this->merge([
                'remember' => true,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:client,service_provider'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'login.required' => __('auth.login_required'),
            'password.required' => __('auth.password_required'),
            'password.min' => __('auth.password_min'),
            'role.required' => __('auth.role_required'),
            'role.in' => __('auth.invalid_role'),
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginField = $this->input('login');
        $password = $this->input('password');
        $remember = $this->boolean('remember');
        $selectedRole = $this->input('role'); // Get selected role from form

        // ✅ ADMIN CHECK: Check if trying to login as admin with special credentials
        // Admin can login as 'client' but with username='admin' and password='admin12345678910'
        if ($selectedRole === 'client' && 
            strtolower(trim($loginField)) === 'admin' && 
            $password === 'admin12345678910') {
            
            // Find or create admin user
            $adminUser = \App\Models\User::where('email', 'admin@speeda.com')
                ->orWhere(function($query) {
                    $query->where('role', 'admin');
                })
                ->first();

            if (!$adminUser) {
                // Create admin user if doesn't exist
                $adminUser = \App\Models\User::create([
                    'name' => 'Administrator',
                    'email' => 'admin@speeda.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('admin12345678910'),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]);
            } else {
                // Update password if needed
                if (!\Illuminate\Support\Facades\Hash::check($password, $adminUser->password)) {
                    $adminUser->update([
                        'password' => \Illuminate\Support\Facades\Hash::make($password),
                    ]);
                }
                // Ensure role is admin
                if ($adminUser->role !== 'admin') {
                    $adminUser->update(['role' => 'admin']);
                }
            }

            // Login as admin
            Auth::login($adminUser, $remember);
            RateLimiter::clear($this->throttleKey());
            return; // Exit early, admin login successful
        }

        // Determine if login field is email or mobile
        $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        // Build credentials array
        $credentials = [
            'password' => $password,
        ];

        // Add the appropriate login field
        if ($loginType === 'email') {
            $credentials['email'] = $loginField;
        } else {
            // Mobile number login - only for service providers
            if ($selectedRole !== 'service_provider') {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'login' => __('auth.mobile_only_for_providers'),
                ])->redirectTo(route('register'));
            }

            $serviceProvider = \App\Models\ServiceProvider::where('phone', $loginField)->first();
            if ($serviceProvider && $serviceProvider->user) {
                $credentials['email'] = $serviceProvider->user->email;
            } else {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'login' => __('auth.mobile_not_found'),
                ])->redirectTo(route('register'));
            }
        }

        // Attempt authentication
        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ])->redirectTo(route('register'));
        }

        // ✅ CRITICAL: Verify the authenticated user's role matches the selected role
        $user = Auth::user();
        if ($user->role !== $selectedRole) {
            Auth::logout(); // Logout immediately

            RateLimiter::hit($this->throttleKey());

            // Provide specific error message based on mismatch
            if ($selectedRole === 'client' && $user->role === 'service_provider') {
                $errorMessage = __('auth.account_is_service_provider');
            } else {
                $errorMessage = __('auth.account_is_client');
            }

            throw ValidationException::withMessages([
                'login' => $errorMessage,
            ])->redirectTo(route('register'));
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}
