<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceProviderProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() &&
               auth()->user()->serviceProvider &&
               auth()->user()->serviceProvider->id === $this->route('serviceProvider')->id;
    }

    /**
     * Prepare the data for validation.
     * SECURITY: This runs before validation to prepare data and enforce business rules.
     */
    protected function prepareForValidation(): void
    {
        // === CATEGORY LOCK RULE: Prevent category changes if not "Others" ===
        // BUSINESS RULE: Service providers can ONLY change category if current category = "Others"
        $currentServiceProvider = $this->route('serviceProvider');
        if ($currentServiceProvider && $currentServiceProvider->category) {
            $currentCategory = $currentServiceProvider->category;
            $othersNames = ['other', 'others', 'أخرى'];
            $isOthersCategory = in_array(strtolower(trim($currentCategory->name)), $othersNames) ||
                                in_array(strtolower(trim($currentCategory->translated_name)), $othersNames);

            // If current category is NOT "Others", force remove category_id from input
            // This prevents any attempt to change it (even via manual request manipulation)
            if (!$isOthersCategory && $this->has('category_id')) {
                // Remove category_id from the request entirely
                $this->request->remove('category_id');
            }
        }

        // Get current service provider's phone to avoid unique check issues
        $currentPhone = $this->route('serviceProvider')->phone ?? null;

        // Sanitize phone numbers - but keep original format if it's the same number
        if ($this->has('phone')) {
            $inputPhone = trim($this->phone ?? '');
            $cleanedInput = preg_replace('/[^0-9]/', '', $inputPhone);
            $cleanedCurrent = preg_replace('/[^0-9]/', '', $currentPhone ?? '');

            // If it's the same number (digits only), keep DB value EXACTLY as is
            if ($cleanedInput === $cleanedCurrent && !empty($cleanedCurrent)) {
                $this->merge(['phone' => $currentPhone]);
            } else {
                // New number - clean but keep + if present
                $this->merge(['phone' => preg_replace('/[^0-9+]/', '', $inputPhone)]);
            }
        }

        // Merge WhatsApp country code with number
        if ($this->has('whatsapp_number') && $this->has('whatsapp_country_code')) {
            $countryCode = $this->whatsapp_country_code;
            $number = preg_replace('/[^0-9]/', '', trim($this->whatsapp_number ?? ''));

            $this->merge([
                'whatsapp_number' => $countryCode . $number
            ]);
        }

        // Trim all text inputs
        $trimFields = ['business_name', 'bio', 'address', 'services_offered'];
        foreach ($trimFields as $field) {
            if ($this->has($field) && is_string($this->$field)) {
                $this->merge([$field => trim($this->$field)]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $serviceProviderId = $this->route('serviceProvider')->id;

        $rules = [
            'business_name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}\-_&.،]+$/u'
            ],

            'profession' => ['nullable', 'string', 'max:255'], // Read-only field, ignore if sent
            'category_id' => ['nullable', 'exists:categories,id'], // Read-only, will be ignored in update

            'bio' => [
                'nullable',
                'string',
                'min:10',
                'max:2000',
                'regex:/^[^<>]*$/u' // Prevent HTML tags
            ],

            'experience_years' => [
                'nullable',
                'integer',
                'min:0',
                'max:50'
            ],

            'phone' => [
                'required',
                'string',
                'min:10',
                'max:20',
                'regex:/^[+]?[0-9\-\s]{10,20}$/',
                Rule::unique('service_providers', 'phone')->ignore($serviceProviderId)
            ],

            'whatsapp_country_code' => [
                'required',
                'string',
                'regex:/^\+[0-9]{1,4}$/'
            ],

            'whatsapp_number' => [
                'required',
                'string',
                'min:10',
                'max:16',
                // Allow optional leading +, digits, spaces, and dashes; we'll normalize before save
                'regex:/^\+?[0-9\s\-]{10,16}$/',
                Rule::unique('service_providers', 'whatsapp_number')->ignore($serviceProviderId),
            ],

            'address' => [
                'nullable',
                'string',
                'min:5',
                'max:500',
                'regex:/^[\pL\pN\s\-_.,#&\'\/@،()]+$/u',
            ],

            'location_id' => [
                'nullable',
                'exists:locations,id'
            ],

            'services_offered' => [
                'nullable',
                'string',
                'max:1000',
                'regex:/^[a-zA-Z0-9\s\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}\-_،,]+$/u'
            ],

            // profile_image validation removed (handled by AJAX)

            'certification' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:2048', // 2MB max (matching PHP upload_max_filesize)
            ],

            // Provider gallery (Spatie Media Library)
            'gallery_images' => [
                'nullable',
                'array',
            ],
            'gallery_images.*' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240', // 10MB
            ],
            'languages' => [
                'nullable',
                'array'
            ],
            'languages.*' => [
                'string',
                'in:ar,en,fr'
            ],
        ];

        return $rules;
    }

    /**
     * Get custom error messages (with translation support).
     */
    public function messages(): array
    {
        return [
            // Business Name
            'business_name.required' => __('sp_validation.sp_business_name_required'),
            'business_name.min' => __('sp_validation.sp_business_name_min'),
            'business_name.max' => __('sp_validation.sp_business_name_max'),
            'business_name.regex' => __('sp_validation.sp_business_name_invalid_chars'),

            // Bio / Description
            'bio.min' => __('sp_validation.sp_bio_min'),
            'bio.max' => __('sp_validation.sp_bio_max'),
            'bio.regex' => __('sp_validation.sp_bio_no_html'),

            // Experience Years
            'experience_years.integer' => __('sp_validation.sp_experience_integer'),
            'experience_years.min' => __('sp_validation.sp_experience_min'),
            'experience_years.max' => __('sp_validation.sp_experience_max'),

            // Phone
            'phone.required' => __('sp_validation.sp_phone_required'),
            'phone.min' => __('sp_validation.sp_phone_min'),
            'phone.max' => __('sp_validation.sp_phone_max'),
            'phone.regex' => __('sp_validation.sp_phone_format'),
            'phone.unique' => __('sp_validation.sp_phone_unique'),

            // WhatsApp (Required)
            'whatsapp_country_code.required' => __('sp_validation.sp_whatsapp_country_code_required'),
            'whatsapp_country_code.regex' => __('sp_validation.sp_whatsapp_country_code_in'),
            'whatsapp_number.required' => __('sp_validation.sp_whatsapp_required'),
            'whatsapp_number.min' => __('sp_validation.sp_whatsapp_min'),
            'whatsapp_number.max' => __('sp_validation.sp_whatsapp_max'),
            'whatsapp_number.regex' => __('sp_validation.sp_whatsapp_format'),

            // Address
            'address.min' => __('sp_validation.sp_address_min'),
            'address.max' => __('sp_validation.sp_address_max'),
            'address.regex' => __('sp_validation.sp_address_english_only'),

            // Location
            'location_id.exists' => __('sp_validation.sp_location_invalid'),

            // Services
            'services_offered.max' => __('sp_validation.sp_services_max'),
            'services_offered.regex' => __('sp_validation.sp_services_invalid_chars'),

            // Profile Image
            'profile_image.image' => __('sp_validation.sp_image_type'),
            'profile_image.mimes' => __('sp_validation.sp_image_mimes'),
            'profile_image.max' => __('sp_validation.sp_image_size'),
            'profile_image.dimensions' => __('sp_validation.sp_image_dimensions'),

            // Certification
            'certification.file' => __('sp_validation.sp_cert_file'),
            'certification.mimes' => __('sp_validation.sp_cert_mimes'),
            'certification.max' => __('sp_validation.sp_cert_size'),

            // Category (read-only)
            'category_id.exists' => __('sp_validation.sp_category_invalid'),
        ];
    }

    /**
     * Get custom attributes for error messages.
     */
    public function attributes(): array
    {
        return [
            'business_name' => __('service_provider.company_activity_name'),
            'bio' => __('general.description'),
            'experience_years' => __('service_provider.experience_years_label'),
            'phone' => __('general.phone'),
            'whatsapp_country_code' => 'Country Code',
            'whatsapp_number' => __('service_provider.whatsapp_number'),
            'address' => __('general.address'),
            'location_id' => __('general.location'),
            'services_offered' => __('service_provider.services_provided'),
            'profile_image' => __('general.profile_image'),
            'certification' => __('service_provider.certification'),
            'languages' => __('service_provider.languages_spoken'),
        ];
    }
}
