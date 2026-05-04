@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Notifications</p>
                    <h1>Create Notification</h1>
                    <p>Send a multilingual broadcast to active service providers.</p>
                </div>
                <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Notifications</span>
                </a>
            </section>

            <form action="{{ route('admin.notifications.store') }}" method="POST" class="admin-blog-form">
                @csrf

                <div class="admin-form-main">
                    <section class="admin-form-card">
                        <div class="admin-form-section-head">
                            <h2>Message Content</h2>
                            <p>All three languages are required because providers may use different public languages.</p>
                        </div>

                        <div class="admin-form-grid">
                            <label class="admin-field admin-field-wide">
                                <span>Title AR <strong>*</strong></span>
                                <input type="text" name="title_ar" value="{{ old('title_ar') }}" required dir="rtl">
                                @error('title_ar')<small>{{ $message }}</small>@enderror
                            </label>
                            <label class="admin-field admin-field-wide">
                                <span>Message AR <strong>*</strong></span>
                                <textarea name="message_ar" rows="5" required dir="rtl">{{ old('message_ar') }}</textarea>
                                @error('message_ar')<small>{{ $message }}</small>@enderror
                            </label>
                            <label class="admin-field admin-field-wide">
                                <span>Title EN <strong>*</strong></span>
                                <input type="text" name="title_en" value="{{ old('title_en') }}" required>
                                @error('title_en')<small>{{ $message }}</small>@enderror
                            </label>
                            <label class="admin-field admin-field-wide">
                                <span>Message EN <strong>*</strong></span>
                                <textarea name="message_en" rows="5" required>{{ old('message_en') }}</textarea>
                                @error('message_en')<small>{{ $message }}</small>@enderror
                            </label>
                            <label class="admin-field admin-field-wide">
                                <span>Title FR <strong>*</strong></span>
                                <input type="text" name="title_fr" value="{{ old('title_fr') }}" required>
                                @error('title_fr')<small>{{ $message }}</small>@enderror
                            </label>
                            <label class="admin-field admin-field-wide">
                                <span>Message FR <strong>*</strong></span>
                                <textarea name="message_fr" rows="5" required>{{ old('message_fr') }}</textarea>
                                @error('message_fr')<small>{{ $message }}</small>@enderror
                            </label>
                        </div>
                    </section>
                </div>

                <aside class="admin-form-side">
                    <section class="admin-form-card">
                        <div class="admin-form-section-head">
                            <h2>Delivery</h2>
                            <p>This notification will be visible to active service providers for 30 days.</p>
                        </div>
                        <div class="admin-language-preview">
                            <strong>Target Audience</strong>
                            <p>Active service providers only.</p>
                        </div>
                        <div class="admin-language-preview">
                            <strong>Expiration</strong>
                            <p>Automatically expires 30 days after sending.</p>
                        </div>
                    </section>

                    <div class="admin-sticky-actions">
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            <span>Send Notification</span>
                        </button>
                        <a href="{{ route('admin.notifications.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
                    </div>
                </aside>
            </form>
        </div>
    </div>
@endsection
