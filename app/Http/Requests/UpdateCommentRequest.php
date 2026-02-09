<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCommentRequest extends FormRequest
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
            'content.required' => __('validation.required', ['attribute' => 'Comment']),
            'content.min' => __('validation.min.string', ['attribute' => 'Comment', 'min' => 5]),
            'content.max' => __('validation.max.string', ['attribute' => 'Comment', 'max' => 500]),
        ];
    }
}
