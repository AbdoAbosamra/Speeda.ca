@extends('layouts.auth-password', [
    'pageTitle' => __('auth.forgot_password'),
    'pageIntro' => __('auth.forgot_password_instructions'),
    'visualTitle' => __('auth.forgot_password'),
])

@section('content')
    <form class="password-form" method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="password-field">
            <label for="email">{{ __('auth.email_address') }}</label>
            <div class="password-input-wrap">
                <i class="fas fa-envelope" aria-hidden="true"></i>
                <input
                    id="email"
                    class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="{{ __('general.email_placeholder') }}"
                >
            </div>

            @foreach ($errors->get('email') as $message)
                <p class="password-error">{{ $message }}</p>
            @endforeach
        </div>

        <button class="password-submit" type="submit">
            <i class="fas fa-paper-plane" aria-hidden="true"></i>
            <span>{{ __('auth.email_password_reset_link') }}</span>
        </button>
    </form>

    <p class="password-auth-footer">
        {{ __('auth.remembered_password') }}
        <a href="{{ route('login') }}?tab=login">{{ __('auth.login') }}</a>
    </p>
@endsection
