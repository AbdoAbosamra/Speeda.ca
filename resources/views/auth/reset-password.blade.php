@extends('layouts.auth-password', [
    'pageTitle' => __('auth.reset_password_heading'),
    'pageIntro' => __('auth.reset_password_instructions'),
    'visualTitle' => __('auth.reset_password_heading'),
])

@section('content')
    <form class="password-form" method="POST" action="{{ route('password.store') }}" onsubmit="this.querySelector('button[type=submit]').disabled = true; return true;">
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
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                    @if($errors->has('email')) aria-describedby="email-error" @endif
                >
            </div>

            @foreach ($errors->get('email') as $message)
                <p class="password-error" id="email-error" role="alert">{{ $message }}</p>
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
                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    aria-describedby="password-rules-hint @if($errors->has('password')) password-error @endif"
                >
                <button class="password-toggle" type="button" data-password-toggle="password" aria-label="{{ __('auth.toggle_password_visibility') }}" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            <p class="password-help" id="password-rules-hint">{{ __('auth.password_rules_hint') }}</p>

            @foreach ($errors->get('password') as $message)
                <p class="password-error" id="password-error" role="alert">{{ $message }}</p>
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
                    aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                    @if($errors->has('password_confirmation')) aria-describedby="password-confirmation-error" @endif
                >
                <button class="password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="{{ __('auth.toggle_password_visibility') }}" aria-pressed="false">
                    <i class="fas fa-eye" aria-hidden="true"></i>
                </button>
            </div>

            @foreach ($errors->get('password_confirmation') as $message)
                <p class="password-error" id="password-confirmation-error" role="alert">{{ $message }}</p>
            @endforeach
        </div>

        <button class="password-submit" type="submit">
            <i class="fas fa-key" aria-hidden="true"></i>
            <span>{{ __('auth.reset_password_button') }}</span>
        </button>
    </form>
@endsection
