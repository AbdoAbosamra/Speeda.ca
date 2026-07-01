@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Notifications"
                title="Create Notification"
                subtitle="Send a multilingual message to all active service providers or only selected providers."
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.notifications.index')"
                        variant="secondary"
                        icon="fas fa-arrow-left"
                        class="admin-btn admin-btn-secondary"
                    >
                        Back to List
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            <div class="admin-form-layout">
                <div class="admin-form-main">
                    <form action="{{ route('admin.notifications.store') }}" method="POST" id="notificationForm">
                        @csrf

                        {{-- Targeting Section --}}
                        <div class="admin-form-section">
                            <div class="section-header">
                                <span class="target-icon"><i class="fas fa-bullseye"></i></span>
                                <h3>Recipients</h3>
                            </div>

                            @if($targetingEnabled)
                                <div class="target-mode-grid" role="radiogroup" aria-label="Notification recipients">
                                    <label class="target-mode-card">
                                        <input type="radio" name="target_mode" value="all" {{ old('target_mode', 'all') === 'all' ? 'checked' : '' }}>
                                        <span class="target-mode-content">
                                            <strong>All Service Providers</strong>
                                            <small>Every active service provider can see this notification.</small>
                                        </span>
                                    </label>

                                    <label class="target-mode-card">
                                        <input type="radio" name="target_mode" value="selected" {{ old('target_mode') === 'selected' ? 'checked' : '' }}>
                                        <span class="target-mode-content">
                                            <strong>Selected Service Providers</strong>
                                            <small>Only the providers selected below can see this notification.</small>
                                        </span>
                                    </label>
                                </div>

                                @error('target_mode')
                                    <span class="admin-error">{{ $message }}</span>
                                @enderror

                                <div class="provider-target-panel" id="providerTargetPanel">
                                    <label for="service_provider_ids">Choose providers <span class="required">*</span></label>
                                    <select
                                        name="service_provider_ids[]"
                                        id="service_provider_ids"
                                        multiple
                                        size="10"
                                        class="provider-target-select"
                                    >
                                        @foreach($serviceProviders as $provider)
                                            @php($providerLabel = $provider->company_name ?: ($provider->user->name ?? 'Provider #' . $provider->id))
                                            <option
                                                value="{{ $provider->id }}"
                                                @selected(in_array($provider->id, old('service_provider_ids', [])))
                                            >
                                                {{ $providerLabel }} - {{ $provider->user->email ?? 'no email' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="target-help">
                                        Hold Ctrl/Cmd to select more than one provider. {{ $serviceProviders->count() }} active provider(s) available.
                                    </div>
                                    @error('service_provider_ids')
                                        <span class="admin-error">{{ $message }}</span>
                                    @enderror
                                    @error('service_provider_ids.*')
                                        <span class="admin-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            @else
                                <input type="hidden" name="target_mode" value="all">
                                <x-ui.alert variant="warning" icon="fas fa-triangle-exclamation">
                                    Targeted provider delivery will become available after running the new notification targeting migration. This notification will be sent to all service providers.
                                </x-ui.alert>
                            @endif
                        </div>

                        {{-- Arabic Section --}}
                        <div class="admin-form-section" dir="rtl">
                            <div class="section-header">
                                <span class="flag-icon">🇸🇦</span>
                                <h3>Arabic Content</h3>
                            </div>
                            <div class="form-group">
                                <label for="title_ar">Title (Arabic) <span class="required">*</span></label>
                                <input type="text" name="title_ar" id="title_ar" value="{{ old('title_ar') }}" placeholder="أدخل العنوان بالعربية" required>
                                @error('title_ar')
                                    <span class="admin-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="message_ar">Message (Arabic) <span class="required">*</span></label>
                                <textarea name="message_ar" id="message_ar" rows="4" placeholder="أدخل الرسالة بالعربية" required>{{ old('message_ar') }}</textarea>
                                @error('message_ar')
                                    <span class="admin-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- English Section --}}
                        <div class="admin-form-section">
                            <div class="section-header">
                                <span class="flag-icon">🇺🇸</span>
                                <h3>English Content</h3>
                            </div>
                            <div class="form-group">
                                <label for="title_en">Title (English) <span class="required">*</span></label>
                                <input type="text" name="title_en" id="title_en" value="{{ old('title_en') }}" placeholder="Enter title in English" required>
                                @error('title_en')
                                    <span class="admin-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="message_en">Message (English) <span class="required">*</span></label>
                                <textarea name="message_en" id="message_en" rows="4" placeholder="Enter message in English" required>{{ old('message_en') }}</textarea>
                                @error('message_en')
                                    <span class="admin-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- French Section --}}
                        <div class="admin-form-section">
                            <div class="section-header">
                                <span class="flag-icon">🇫🇷</span>
                                <h3>French Content</h3>
                            </div>
                            <div class="form-group">
                                <label for="title_fr">Title (French) <span class="required">*</span></label>
                                <input type="text" name="title_fr" id="title_fr" value="{{ old('title_fr') }}" placeholder="Entrez le titre en français" required>
                                @error('title_fr')
                                    <span class="admin-error">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="message_fr">Message (French) <span class="required">*</span></label>
                                <textarea name="message_fr" id="message_fr" rows="4" placeholder="Entrez le message en français" required>{{ old('message_fr') }}</textarea>
                                @error('message_fr')
                                    <span class="admin-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="admin-form-actions">
                            <button type="button" class="admin-btn admin-btn-secondary" id="previewBtn">
                                <i class="fas fa-eye"></i>
                                <span>Preview</span>
                            </button>
                            <button type="submit" class="admin-btn admin-btn-primary text-white">
                                <i class="fas fa-paper-plane"></i>
                                <span>Send Notification</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="admin-form-sidebar">
                    <div class="admin-info-card">
                        <div class="info-card-header">
                            <i class="fas fa-info-circle"></i>
                            <h4>Information</h4>
                        </div>
                        <ul class="info-list">
                            <li>
                                <i class="fas fa-users"></i>
                                <span><strong>Target:</strong> All or selected service providers</span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span><strong>Expires:</strong> 30 days from creation</span>
                            </li>
                            <li>
                                <i class="fas fa-globe"></i>
                                <span><strong>Languages:</strong> Arabic, English, French</span>
                            </li>
                            <li>
                                <i class="fas fa-bell"></i>
                                <span><strong>Badge:</strong> Unread count shown in navbar</span>
                            </li>
                        </ul>
                    </div>

                    <div class="admin-tips-card">
                        <div class="tips-card-header">
                            <i class="fas fa-lightbulb"></i>
                            <h4>Tips</h4>
                        </div>
                        <ul class="tips-list">
                            <li>Keep titles concise (max 255 characters)</li>
                            <li>Messages should be clear and actionable</li>
                            <li>Use the preview to check all languages</li>
                            <li>Expired notifications are automatically hidden</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content admin-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Notification Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="preview-tabs">
                        <button type="button" class="preview-tab active" data-lang="ar">
                            <span class="flag-icon">🇸🇦</span> Arabic
                        </button>
                        <button type="button" class="preview-tab" data-lang="en">
                            <span class="flag-icon">🇺🇸</span> English
                        </button>
                        <button type="button" class="preview-tab" data-lang="fr">
                            <span class="flag-icon">🇫🇷</span> French
                        </button>
                    </div>
                    
                    <div class="preview-content">
                        <div class="preview-notification-card" id="previewCard">
                            <div class="preview-notification-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="preview-notification-body">
                                <h3 class="preview-notification-title" id="previewTitle"></h3>
                                <p class="preview-notification-message" id="previewMessage"></p>
                                <span class="preview-notification-time">
                                    <i class="fas fa-clock"></i> Just now
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        <span>Close</span>
                    </button>
                    <button type="button" class="admin-btn admin-btn-primary text-white" id="confirmSend">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Notification</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    .admin-form-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 2rem;
    }

    .admin-form-main {
        min-width: 0;
    }

    .admin-form-section {
        background: white;
        border: 1px solid var(--border-default, #e2e8f0);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-default, #e2e8f0);
    }

    .section-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
    }

    .target-icon {
        color: var(--sp-color-primary, #2563eb);
    }

    .target-mode-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .target-mode-card {
        display: flex;
        gap: 0.75rem;
        padding: 1rem;
        border: 1px solid var(--sp-color-border-strong, #cbd5e1);
        border-radius: var(--sp-radius-lg, 0.75rem);
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .target-mode-card:has(input:checked) {
        border-color: var(--sp-color-primary, #2563eb);
        box-shadow: var(--sp-shadow-focus, 0 0 0 0.2rem rgba(37, 99, 235, 0.18));
    }

    .target-mode-card input {
        width: auto;
        margin-top: 0.25rem;
    }

    .target-mode-content {
        display: grid;
        gap: 0.25rem;
    }

    .target-mode-content small {
        color: var(--sp-color-text-muted, #64748b);
        line-height: 1.4;
    }

    .provider-target-panel {
        display: none;
        margin-top: 1rem;
    }

    .provider-target-panel.is-visible {
        display: block;
    }

    .provider-target-panel label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .provider-target-select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--sp-color-border-strong, #cbd5e1);
        border-radius: var(--sp-radius-lg, 0.75rem);
        color: var(--sp-color-text, #0f172a);
        background: var(--sp-color-surface, #ffffff);
    }

    .target-help {
        margin-top: 0.5rem;
        color: var(--sp-color-text-muted, #64748b);
        font-size: 0.875rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        font-size: 0.9375rem;
        color: var(--text-primary, #0f172a);
    }

    .required {
        color: #ef4444;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-default, #e2e8f0);
        border-radius: 12px;
        font-size: 0.9375rem;
        color: var(--text-primary, #0f172a);
        transition: all 0.2s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary-500, #3b82f6);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .admin-error {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: #ef4444;
    }

    .admin-form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .admin-form-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .admin-info-card,
    .admin-tips-card {
        background: white;
        border: 1px solid var(--border-default, #e2e8f0);
        border-radius: 16px;
        padding: 1.25rem;
    }

    .info-card-header,
    .tips-card-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-default, #e2e8f0);
    }

    .info-card-header i {
        color: var(--primary-500, #3b82f6);
    }

    .tips-card-header i {
        color: #f59e0b;
    }

    .info-card-header h4,
    .tips-card-header h4 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }

    .info-list,
    .tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list li,
    .tips-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.5rem 0;
        font-size: 0.875rem;
        color: var(--text-secondary, #475569);
    }

    .info-list li i {
        color: var(--primary-500, #3b82f6);
        margin-top: 2px;
    }

    .tips-list li {
        position: relative;
        padding-left: 1rem;
    }

    .tips-list li::before {
        content: '•';
        position: absolute;
        left: 0;
        color: #f59e0b;
    }

    /* Preview Modal Styles */
    .preview-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .preview-tab {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        background: white;
        border: 2px solid var(--border-default, #e2e8f0);
        border-radius: 12px;
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--text-secondary, #475569);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .preview-tab:hover {
        border-color: var(--primary-300, #93c5fd);
    }

    .preview-tab.active {
        background: linear-gradient(135deg, var(--primary-500, #3b82f6), var(--primary-600, #2563eb));
        border-color: transparent;
        color: white;
    }

    .preview-content {
        padding: 1.5rem;
        background: var(--surface-subtle, #f8fafc);
        border-radius: 16px;
    }

    .preview-notification-card {
        display: flex;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: white;
        border: 1px solid var(--border-default, #e2e8f0);
        border-radius: 16px;
        max-width: 600px;
        margin: 0 auto;
    }

    .preview-notification-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.15));
        color: var(--primary-500, #3b82f6);
        flex-shrink: 0;
    }

    .preview-notification-body {
        flex: 1;
    }

    .preview-notification-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 0.5rem;
        color: var(--text-primary, #0f172a);
    }

    .preview-notification-message {
        font-size: 0.9375rem;
        line-height: 1.6;
        color: var(--text-secondary, #475569);
        margin: 0 0 0.75rem;
    }

    .preview-notification-time {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8125rem;
        color: var(--text-muted, #94a3b8);
    }

    @media (max-width: 992px) {
        .admin-form-layout {
            grid-template-columns: 1fr;
        }

        .admin-form-sidebar {
            order: -1;
        }
    }

    @media (max-width: 768px) {
        .admin-form-actions {
            flex-direction: column;
        }

        .preview-tabs {
            flex-wrap: wrap;
        }

        .target-mode-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('notificationForm');
        const previewBtn = document.getElementById('previewBtn');
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        const confirmSendBtn = document.getElementById('confirmSend');
        const previewTabs = document.querySelectorAll('.preview-tab');
        const targetModeInputs = document.querySelectorAll('input[name="target_mode"]');
        const providerTargetPanel = document.getElementById('providerTargetPanel');
        const providerTargetSelect = document.getElementById('service_provider_ids');
        
        let currentLang = 'ar';

        function updateTargetPanel() {
            if (!providerTargetPanel || !providerTargetSelect) return;

            const selectedMode = document.querySelector('input[name="target_mode"]:checked')?.value || 'all';
            const requiresSelection = selectedMode === 'selected';

            providerTargetPanel.classList.toggle('is-visible', requiresSelection);
            providerTargetSelect.toggleAttribute('required', requiresSelection);
        }

        targetModeInputs.forEach(input => {
            input.addEventListener('change', updateTargetPanel);
        });

        updateTargetPanel();

        // Update preview content
        function updatePreview(lang) {
            const titleInput = document.getElementById(`title_${lang}`);
            const messageInput = document.getElementById(`message_${lang}`);
            const previewTitle = document.getElementById('previewTitle');
            const previewMessage = document.getElementById('previewMessage');
            const previewCard = document.getElementById('previewCard');
            
            previewTitle.textContent = titleInput.value || '(No title)';
            previewMessage.textContent = messageInput.value || '(No message)';
            
            // Set RTL for Arabic
            previewCard.dir = lang === 'ar' ? 'rtl' : 'ltr';
        }

        // Preview button click
        previewBtn.addEventListener('click', function() {
            updatePreview(currentLang);
            previewModal.show();
        });

        // Tab switching
        previewTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                previewTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentLang = this.dataset.lang;
                updatePreview(currentLang);
            });
        });

        // Confirm send button
        confirmSendBtn.addEventListener('click', function() {
            previewModal.hide();
            form.submit();
        });
    });
    </script>
@endsection
