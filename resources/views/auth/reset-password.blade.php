@extends('layouts.auth-password', [
    'pageTitle' => __('auth.reset_password_heading'),
    'pageIntro' => __('auth.reset_password_instructions'),
    'visualTitle' => __('auth.reset_password_heading'),
])

@section('content')
    <form class="password-form" method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="password-field">
            <label for="email">{{ __('auth.email_address') }}</label>
            <div class="password-input-wrap">
                <i class="fas fa-envelope" aria-hidden="true"></i>
                <input
                    id="email"
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="{{ __('general.email_placeholder') }}"
                >
            </div>

            @foreach ($errors->get('email') as $message)
                <p class="password-error">{{ $message }}</p>
            @endforeach
        </div>

        <div class="password-field">
            <label for="password">{{ __('auth.password') }}</label>
            <div class="password-input-wrap">
                <i class="fas fa-lock" aria-hidden="true"></i>
                <input
                    id="password"
                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                >
                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="{{ __('auth.toggle_password_visibility') }}" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <p class="password-help">{{ __('auth.password_rules_hint') }}</p>

            @foreach ($errors->get('password') as $message)
                <p class="password-error">{{ $message }}</p>
            @endforeach
        </div>

        <div class="password-field">
            <label for="password_confirmation">{{ __('auth.confirm_password') }}</label>
            <div class="password-input-wrap">
                <i class="fas fa-lock" aria-hidden="true"></i>
                <input
                    id="password_confirmation"
                    class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                >
                <button class="password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="{{ __('auth.toggle_password_visibility') }}" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>

            @foreach ($errors->get('password_confirmation') as $message)
                <p class="password-error">{{ $message }}</p>
            @endforeach
        </div>

        <button class="password-submit" type="submit">
            <i class="fas fa-key" aria-hidden="true"></i>
            <span>{{ __('auth.reset_password_button') }}</span>
        </button>
    </form>
@endsection
