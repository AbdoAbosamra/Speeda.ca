<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return Auth::check() && ($user && $user->isAdmin());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'city' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'unique:locations,city',
            ],
            'country' => [
                'nullable',
                'string',
                'max:255',
                'min:2',
            ],
            'area' => [
                'nullable',
                'string',
                'max:255',
                'min:2',
            ],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:5120', // 5MB
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'meta_description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'city.required' => __('admin.city_required'),
            'city.unique' => __('admin.city_already_exists'),
            'city.min' => __('admin.city_min'),
            'city.max' => __('admin.city_max'),
            'image.image' => __('admin.image_must_be_image'),
            'image.mimes' => __('admin.image_invalid_format'),
            'image.max' => __('admin.image_too_large'),
            'latitude.numeric' => __('admin.latitude_invalid'),
            'latitude.between' => __('admin.latitude_out_of_range'),
            'longitude.numeric' => __('admin.longitude_invalid'),
            'longitude.between' => __('admin.longitude_out_of_range'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string booleans to actual booleans
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
