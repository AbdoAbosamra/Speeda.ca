<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Only clients can create reviews
        return Auth::check() && ($user && $user->isClient());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'service_provider_id' => [
                'required',
                'integer',
                'exists:service_providers,id',
            ],
            'booking_id' => [
                'nullable',
                'integer',
                'exists:bookings,id',
            ],
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'review_text' => [
                'required',
                'string',
                'max:1000',
                'min:10',
            ],
            'rating_breakdown' => [
                'nullable',
                'array',
            ],
        ];
    }



    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'service_provider_id.required' => __('validation.required', ['attribute' => 'Service Provider']),
            'service_provider_id.exists' => __('validation.exists', ['attribute' => 'Service Provider']),
            'rating.required' => __('validation.required', ['attribute' => 'Rating']),
            'rating.min' => __('validation.min.numeric', ['attribute' => 'Rating', 'min' => 1]),
            'rating.max' => __('validation.max.numeric', ['attribute' => 'Rating', 'max' => 5]),
            'review_text.required' => __('validation.required', ['attribute' => 'Review']),
            'review_text.min' => __('validation.min.string', ['attribute' => 'Review', 'min' => 10]),
            'review_text.max' => __('validation.max.string', ['attribute' => 'Review', 'max' => 1000]),
        ];
    }
}
