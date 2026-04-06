<?php

namespace App\Http\Requests\Auth;

use App\Rules\CanadianPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Dedicated registration request with comprehensive validation
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'min:2', 'regex:/^[\p{L}\p{M}\s\-\']+$/u'],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:client,service_provider'],
        ];

        // Service provider specific rules
        if ($this->input('role') === 'service_provider') {
            $rules['mobile'] = ['required', 'string', new CanadianPhoneNumber(), 'unique:service_providers,phone'];
            $rules['whatsapp_number'] = ['nullable', 'string', new CanadianPhoneNumber()];
            $rules['profession'] = ['required', function($attribute, $value, $fail) {
                if ($value === 'other') {
                    return; // Allow "other" value
                }
                if (!is_numeric($value) || !\App\Models\Category::terminal()->where('id', $value)->exists()) {
                    $fail(__('validation.profession_invalid'));
                }
            }];
            $rules['city'] = ['required', 'string', 'max:100'];
            $rules['terms'] = ['required', 'accepted'];
        } else {
            // Client can optionally provide mobile
            $rules['mobile'] = ['nullable', 'string', new CanadianPhoneNumber()];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.name_required'),
            'name.min' => __('validation.name_min'),
            'name.regex' => __('validation.name_format'),
            'email.required' => __('validation.email_required'),
            'email.email' => __('validation.email_format'),
            'email.unique' => __('validation.email_unique'),
            'email.regex' => __('validation.email_invalid'),
            'password.required' => __('auth.password_required'),
            'password.confirmed' => __('validation.password_confirmed'),
            'role.required' => __('validation.role_required'),
            'role.in' => __('validation.role_invalid'),
            'mobile.required' => __('validation.mobile_required_provider'),
            'mobile.unique' => __('validation.mobile_unique'),
            'profession.required' => __('validation.profession_required'),
            'profession.exists' => __('validation.profession_invalid'),
            'city.required' => __('validation.city_required'),
            'city.in' => __('validation.city_invalid'),
            'terms.required' => __('validation.terms_required'),
            'terms.accepted' => __('validation.terms_accepted'),
        ];
    }

    /**
     * Prepare data for validation
     */
    protected function prepareForValidation(): void
    {
        // Normalize phone number format
        if ($this->has('mobile')) {
            $this->merge([
                'mobile' => $this->normalizePhoneNumber($this->input('mobile'))
            ]);
        }

        if ($this->has('whatsapp_number')) {
            $this->merge([
                'whatsapp_number' => $this->normalizePhoneNumber($this->input('whatsapp_number'))
            ]);
        }

        // Normalize city name (capitalize first letter)
        if ($this->has('city')) {
            $this->merge([
                'city' => ucfirst(strtolower(trim($this->input('city'))))
            ]);
        }
    }

    /**
     * Normalize phone number to standard format
     */
    private function normalizePhoneNumber(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // If it starts with +1, keep it
        if (str_starts_with($cleaned, '+1')) {
            return $cleaned;
        }

        // Remove leading 1 if present (but not +1)
        $digits = preg_replace('/\D/', '', $cleaned);
        if (strlen($digits) === 11 && $digits[0] === '1') {
            $digits = substr($digits, 1);
        }

        // Return +1 prefix with digits if we have exactly 10 digits
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        return $phone;
    }
}
