<?php

namespace App\Http\Requests\Auth;

class EnrollProviderRequest extends RegisterRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['client', 'service_provider'], true)
            && ! $this->user()->serviceProvider;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['role' => 'service_provider']);
        parent::prepareForValidation();
    }

    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['email'], $rules['password']);

        return $rules;
    }
}
