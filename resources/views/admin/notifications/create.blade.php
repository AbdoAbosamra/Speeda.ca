@extends('layouts.app')

@section('content')
<div class="admin-content-wrapper">
    <div class="container py-4">
        {{-- Header --}}
        <div class="mb-5">
            <a href="{{ route('admin.notifications.index') }}" class="btn btn-link text-secondary p-0 mb-3 text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> {{ __('admin.back_to_list') ?? 'Back to List' }}
            </a>
            <h1 class="display-6 fw-bold mb-1" style="color: var(--text-primary);">{{ __('admin.create_notification') ?? 'Create Notification' }}</h1>
            <p class="text-secondary fs-5">{{ __('admin.add_new_broadcast_message') ?? 'Add a new broadcast message for all service providers' }}</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('admin.notifications.store') }}" method="POST" class="card border-0 shadow-sm rounded-4 p-4">
                    @csrf

                    {{-- Arabic Content --}}
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 text-indigo border-bottom pb-2">
                            <i class="fas fa-language me-2"></i> {{ __('admin.arabic_content') ?? 'Arabic Content' }}
                        </h5>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.title_ar') ?? 'Title (Arabic)' }}</label>
                            <input type="text" name="title_ar" class="form-control @error('title_ar') is-invalid @enderror" value="{{ old('title_ar') }}" required dir="rtl">
                            @error('title_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.message_ar') ?? 'Message (Arabic)' }}</label>
                            <textarea name="message_ar" class="form-control @error('message_ar') is-invalid @enderror" rows="4" required dir="rtl">{{ old('message_ar') }}</textarea>
                            @error('message_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- English Content --}}
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 text-indigo border-bottom pb-2">
                            <i class="fas fa-language me-2"></i> {{ __('admin.english_content') ?? 'English Content' }}
                        </h5>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.title_en') ?? 'Title (English)' }}</label>
                            <input type="text" name="title_en" class="form-control @error('title_en') is-invalid @enderror" value="{{ old('title_en') }}" required>
                            @error('title_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.message_en') ?? 'Message (English)' }}</label>
                            <textarea name="message_en" class="form-control @error('message_en') is-invalid @enderror" rows="4" required>{{ old('message_en') }}</textarea>
                            @error('message_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- French Content --}}
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3 text-indigo border-bottom pb-2">
                            <i class="fas fa-language me-2"></i> {{ __('admin.french_content') ?? 'French Content' }}
                        </h5>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.title_fr') ?? 'Title (French)' }}</label>
                            <input type="text" name="title_fr" class="form-control @error('title_fr') is-invalid @enderror" value="{{ old('title_fr') }}" required>
                            @error('title_fr') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('admin.message_fr') ?? 'Message (French)' }}</label>
                            <textarea name="message_fr" class="form-control @error('message_fr') is-invalid @enderror" rows="4" required>{{ old('message_fr') }}</textarea>
                            @error('message_fr') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 mt-4 border-top pt-4">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 shadow-sm fw-bold">
                            <i class="fas fa-paper-plane me-2"></i> {{ __('admin.save_notification') ?? 'Save Notification' }}
                        </button>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-light px-4 py-2 rounded-3 fw-bold">
                            {{ __('admin.cancel') ?? 'Cancel' }}
                        </a>
                    </div>
                </form>
            </div>

            {{-- Sidebar Info --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i> Information</h5>
                    <ul class="list-unstyled mb-0 d-grid gap-3">
                        <li class="d-flex gap-2">
                            <i class="fas fa-check-circle text-success mt-1"></i>
                            <span>{{ __('admin.notification_expiry_info') ?? 'Notifications will automatically expire after 30 days.' }}</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="fas fa-check-circle text-success mt-1"></i>
                            <span>{{ __('admin.target_audience_info') ?? 'This message will be visible only to active Service Providers.' }}</span>
                        </li>
                        <li class="d-flex gap-2">
                            <i class="fas fa-check-circle text-success mt-1"></i>
                            <span>{{ __('admin.multi_language_info') ?? 'Providers will see the message in their selected session language.' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-indigo { color: #4f46e5; }
    .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.1);
    }
</style>
@endsection
