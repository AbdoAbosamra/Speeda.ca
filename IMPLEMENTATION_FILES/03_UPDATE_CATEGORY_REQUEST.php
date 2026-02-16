<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Enhanced Update Category Request
 *
 * CHANGES:
 * 1. English and French names are now required
 * 2. Better validation messages
 */
class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('category')->id;

        return [
            // Multi-language name fields
            'name_ar' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            // ✅ CHANGED: English is now required
            'name_en' => [
                'required', // Changed from 'nullable'
                'string',
                'max:255',
                'min:2',
            ],
            // ✅ CHANGED: French is now required
            'name_fr' => [
                'required', // Changed from 'nullable'
                'string',
                'max:255',
                'min:2',
            ],
            // Multi-language description fields (still optional)
            'description_ar' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'description_en' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'description_fr' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                Rule::notIn($categoryId), // Prevent self-reference
            ],
            'is_section' => [
                'nullable',
                'boolean',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:10000',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],
            'color' => [
                'nullable',
                'string',
                'max:7',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
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
            'name_ar.required' => __('admin.category_name_ar_required'),
            'name_ar.max' => __('admin.category_name_max'),
            'name_ar.min' => __('admin.category_name_min'),

            'name_en.required' => __('admin.category_name_en_required'),
            'name_en.max' => __('admin.category_name_max'),
            'name_en.min' => __('admin.category_name_min'),

            'name_fr.required' => __('admin.category_name_fr_required'),
            'name_fr.max' => __('admin.category_name_max'),
            'name_fr.min' => __('admin.category_name_min'),

            'slug.regex' => __('admin.category_slug_format'),
            'parent_id.exists' => __('admin.invalid_parent_category'),
            'parent_id.not_in' => __('admin.cannot_set_self_as_parent'),
            'color.regex' => __('admin.invalid_color_format'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_section' => $this->boolean('is_section'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
