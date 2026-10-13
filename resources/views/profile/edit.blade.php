@extends('layouts.app')

@section('title', __('profile.account_title'))

@section('content')
@php
    $googleOnly = $user->isGoogleOnly();
@endphp
<div class="container py-2 pb-5" style="max-width: 760px;">
    <header class="mb-4">
        <h1 class="h3 fw-bold mb-1">{{ __('profile.account_title') }}</h1>
        <p class="text-muted mb-0">{{ __('profile.account_subtitle') }}</p>
    </header>

    @if($user->isClient())
        <a href="{{ route('offers.favorites') }}" class="card border-0 shadow-sm rounded-4 mb-4 text-decoration-none">
            <div class="card-body p-4 d-flex align-items-center gap-3">
                <span class="d-grid place-items-center rounded-3 text-white" style="width:44px;height:44px;background:#2563eb;place-items:center;display:grid"><i class="fas fa-bookmark"></i></span>
                <span class="flex-grow-1">
                    <b class="d-block text-dark">{{ __('offers.my_favorites') }}</b>
                    <small class="text-muted">{{ __('offers.my_favorites_subtitle') }}</small>
                </span>
                <i class="fas fa-chevron-right text-muted flip-rtl" aria-hidden="true"></i>
            </div>
        </a>
    @endif

    {{-- ============ PROFILE ============ --}}
    <section class="card border-0 shadow-sm rounded-4 mb-4" aria-labelledby="profile-info-title">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-1" id="profile-info-title">{{ __('profile.profile_information') }}</h2>
            <p class="text-muted small mb-4">{{ __('profile.profile_information_description') }}</p>

            @if(session('status') === 'profile-updated')
                <div class="alert alert-success py-2" role="status">{{ __('profile.saved') }}</div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="d-grid gap-3">
                @csrf
                @method('PATCH')
                <div>
                    <label for="name" class="form-label fw-semibold">{{ __('profile.name') }}</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="email" class="form-label fw-semibold">{{ __('profile.email') }}</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" dir="ltr">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <p class="small text-warning mt-2 mb-0">{{ __('profile.email_unverified') }}</p>
                    @endif
                </div>
                <div>
                    <button type="submit" class="btn btn-primary px-4">{{ __('profile.save') }}</button>
                </div>
            </form>
        </div>
    </section>

    {{-- ============ PASSWORD ============ --}}
    <section class="card border-0 shadow-sm rounded-4 mb-4" aria-labelledby="password-title">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-1" id="password-title">{{ $googleOnly ? __('profile.set_password') : __('profile.update_password') }}</h2>
            <p class="text-muted small mb-4">{{ $googleOnly ? __('profile.set_password_help') : __('profile.password_help') }}</p>

            @if(session('status') === 'password-updated')
                <div class="alert alert-success py-2" role="status">{{ __('profile.saved') }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="d-grid gap-3">
                @csrf
                @method('PUT')
                @unless($googleOnly)
                    <div>
                        <label for="current_password" class="form-label fw-semibold">{{ __('profile.current_password') }}</label>
                        <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                               class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
                        @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endunless
                <div>
                    <label for="password" class="form-label fw-semibold">{{ __('profile.new_password') }}</label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
                           class="form-control @error('password', 'updatePassword') is-invalid @enderror" required>
                    @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="form-label fw-semibold">{{ __('profile.confirm_password') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary px-4">{{ __('profile.save') }}</button>
                </div>
            </form>
        </div>
    </section>

    {{-- ============ DELETE ============ --}}
    <section class="card border-danger-subtle shadow-sm rounded-4" aria-labelledby="delete-title" x-data="{ open: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold text-danger mb-1" id="delete-title">{{ __('profile.delete_account') }}</h2>
            <p class="text-muted small mb-3">{{ __('profile.delete_account_description') }}</p>

            <button type="button" class="btn btn-outline-danger" x-show="!open" @click="open = true">{{ __('profile.delete_account') }}</button>

            <form method="POST" action="{{ route('profile.destroy') }}" class="d-grid gap-3" x-show="open" x-cloak>
                @csrf
                @method('DELETE')
                <p class="fw-semibold mb-0">{{ __('profile.delete_account_modal_title') }}</p>
                @if($googleOnly)
                    <div>
                        <label for="confirm_email" class="form-label small">{{ __('profile.delete_confirm_email', ['email' => $user->email]) }}</label>
                        <input type="email" id="confirm_email" name="confirm_email" class="form-control @error('confirm_email', 'userDeletion') is-invalid @enderror" dir="ltr" required>
                        @error('confirm_email', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @else
                    <div>
                        <label for="delete_password" class="form-label small">{{ __('profile.delete_account_confirmation') }}</label>
                        <input type="password" id="delete_password" name="password" autocomplete="current-password"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror" required>
                        @error('password', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endif
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light" @click="open = false">{{ __('profile.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('profile.delete_account') }}</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
