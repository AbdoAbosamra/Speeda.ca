<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:categories,slug',
                'regex:/^[a-z0-9\-_]+$/'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                Rule::notIn($this->input('id')), // Prevent self-reference
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
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', // Validate hex color
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
            'name.required' => __('admin.category_name_required'),
            'name.max' => __('admin.category_name_max'),
            'slug.unique' => __('admin.category_slug_exists'),
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
        // Generate slug from name if not provided
        if (!$this->input('slug') && $this->input('name')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->input('name')),
            ]);
        }

        // Convert string booleans to actual booleans
        $this->merge([
            'is_section' => $this->boolean('is_section'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
