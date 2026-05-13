<?php

namespace App\Http\Requests\Auth;

use App\Models\ServiceProvider;
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

        if ($this->has('login') && ! filter_var($this->input('login'), FILTER_VALIDATE_EMAIL)) {
            $this->merge([
                'login' => $this->normalizePhoneNumber($this->input('login')),
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

        // Determine if login field is email or mobile
        // Special case: if user types 'admin', treat it as the admin email
        if ($loginField === 'admin') {
            $loginField = 'admin@speeda.com';
            $this->merge(['login' => $loginField]);
            $loginType = 'email';
        } else {
            $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';
        }

        // Build credentials array
        $credentials = [
            'password' => $password,
        ];

        // Add the appropriate login field
        if ($loginType === 'email') {
            $credentials['email'] = $loginField;
        } else {
            // Mobile number login - only for service providers
            // Bypass for admin email even if misidentified
            if ($selectedRole !== 'service_provider' && $loginField !== 'admin@speeda.com') {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'login' => __('auth.mobile_only_for_providers'),
                ])->redirectTo(route('register'));
            }

            $serviceProvider = ServiceProvider::where('phone', $loginField)->first();
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
        // Allow 'admin' to bypass this check
        $user = Auth::user();
        if ($user->role !== 'admin' && $user->role !== $selectedRole) {
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

    private function normalizePhoneNumber(?string $phone): ?string
    {
        if (blank($phone)) {
            return $phone;
        }

        $cleaned = preg_replace('/[^0-9+]/', '', trim($phone));

        if (str_starts_with($cleaned, '+1')) {
            return $cleaned;
        }

        $digits = preg_replace('/\D/', '', $cleaned);
        if ($digits === '') {
            return trim($phone);
        }

        if (strlen($digits) === 11 && $digits[0] === '1') {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        return $cleaned;
    }
}
