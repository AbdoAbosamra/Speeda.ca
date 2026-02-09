<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'commentable_type' => [
                'required',
                'string',
                'in:App\Models\Review',
            ],
            'commentable_id' => [
                'required',
                'integer',
            ],
            'content' => [
                'required',
                'string',
                'max:500',
                'min:5',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'commentable_type.required' => __('validation.required', ['attribute' => 'Commentable Type']),
            'commentable_type.in' => __('validation.in', ['attribute' => 'Commentable Type']),
            'commentable_id.required' => __('validation.required', ['attribute' => 'Commentable ID']),
            'commentable_id.integer' => __('validation.integer', ['attribute' => 'Commentable ID']),
            'content.required' => __('validation.required', ['attribute' => 'Comment']),
            'content.min' => __('validation.min.string', ['attribute' => 'Comment', 'min' => 5]),
            'content.max' => __('validation.max.string', ['attribute' => 'Comment', 'max' => 500]),
        ];
    }
}
