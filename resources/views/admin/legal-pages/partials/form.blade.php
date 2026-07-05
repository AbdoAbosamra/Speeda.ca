@php
    $currentStatus = old('status', $page->status ?: 'draft');
    $isDefaultSlug = \App\Models\LegalPage::defaultForSlug(old('slug', $page->slug));
@endphp

<form action="{{ $action }}" method="POST" class="admin-legal-form">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-legal-layout">
        <div class="admin-legal-main">
            @if($default)
                <x-ui.alert variant="warning" icon="fas fa-triangle-exclamation">
                    You are customizing an existing public legal page. Publishing this CMS page will replace the static fallback for this URL. Deleting the CMS page later will restore the static fallback.
                </x-ui.alert>
            @endif

            <section class="admin-form-card">
                <div class="admin-form-section-head">
                    <h2>Page Identity</h2>
                    <p>The slug controls the public URL. Use the existing slug to override Privacy Policy or Terms of Service.</p>
                </div>

                <div class="admin-form-grid">
                    <label class="admin-field admin-field-wide">
                        <span>Slug <strong>*</strong></span>
                        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required placeholder="privacy-policy">
                        @error('slug')<small>{{ $message }}</small>@enderror
                        @if($isDefaultSlug)
                            <em class="admin-field-note">This slug maps to an existing website link.</em>
                        @else
                            <em class="admin-field-note">Custom pages are published at /legal/your-slug.</em>
                        @endif
                    </label>

                    <label class="admin-field admin-field-wide">
                        <span>Page Type <strong>*</strong></span>
                        <select name="page_type" required>
                            @foreach($pageTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('page_type', $page->page_type ?: 'custom') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('page_type')<small>{{ $message }}</small>@enderror
                    </label>
                </div>
            </section>

            <section class="admin-form-card">
                <div class="admin-form-section-head">
                    <h2>English</h2>
                    <p>Primary legal content. Basic HTML is allowed: headings, paragraphs, lists, links, tables, and emphasis.</p>
                </div>

                <div class="admin-form-grid">
                    <label class="admin-field admin-field-wide">
                        <span>Title EN <strong>*</strong></span>
                        <input type="text" name="title_en" value="{{ old('title_en', $page->title_en) }}" required>
                        @error('title_en')<small>{{ $message }}</small>@enderror
                    </label>

                    <label class="admin-field admin-field-wide">
                        <span>Content EN <strong>*</strong></span>
                        <textarea name="content_en" rows="16" required placeholder="<h2>Section title</h2><p>Legal text...</p>">{{ old('content_en', $page->content_en) }}</textarea>
                        @error('content_en')<small>{{ $message }}</small>@enderror
                    </label>

                    <label class="admin-field admin-field-wide">
                        <span>Summary EN</span>
                        <textarea name="summary_en" rows="3" maxlength="500">{{ old('summary_en', $page->summary_en) }}</textarea>
                        @error('summary_en')<small>{{ $message }}</small>@enderror
                    </label>
                </div>
            </section>

            <section class="admin-form-card" dir="rtl">
                <div class="admin-form-section-head">
                    <h2>العربية</h2>
                    <p>المحتوى العربي الظاهر عند اختيار اللغة العربية.</p>
                </div>

                <div class="admin-form-grid">
                    <label class="admin-field admin-field-wide">
                        <span>العنوان AR <strong>*</strong></span>
                        <input type="text" name="title_ar" value="{{ old('title_ar', $page->title_ar) }}" required dir="rtl">
                        @error('title_ar')<small>{{ $message }}</small>@enderror
                    </label>

                    <label class="admin-field admin-field-wide">
                        <span>المحتوى AR <strong>*</strong></span>
                        <textarea name="content_ar" rows="16" required dir="rtl" placeholder="<h2>عنوان القسم</h2><p>النص القانوني...</p>">{{ old('content_ar', $page->content_ar) }}</textarea>
                        @error('content_ar')<small>{{ $message }}</small>@enderror
                    </label>

                    <label class="admin-field admin-field-wide">
                        <span>الملخص AR</span>
                        <textarea name="summary_ar" rows="3" maxlength="500" dir="rtl">{{ old('summary_ar', $page->summary_ar) }}</textarea>
                        @error('summary_ar')<small>{{ $message }}</small>@enderror
                    </label>
                </div>
            </section>

            <section class="admin-form-card">
                <div class="admin-form-section-head">
                    <h2>French</h2>
                    <p>French legal content for the public site.</p>
                </div>

                <div class="admin-form-grid">
                    <label class="admin-field admin-field-wide">
                        <span>Title FR <strong>*</strong></span>
                        <input type="text" name="title_fr" value="{{ old('title_fr', $page->title_fr) }}" required>
                        @error('title_fr')<small>{{ $message }}</small>@enderror
                    </label>

                    <label class="admin-field admin-field-wide">
                        <span>Content FR <strong>*</strong></span>
                        <textarea name="content_fr" rows="16" required placeholder="<h2>Titre de section</h2><p>Texte juridique...</p>">{{ old('content_fr', $page->content_fr) }}</textarea>
                        @error('content_fr')<small>{{ $message }}</small>@enderror
                    </label>

                    <label class="admin-field admin-field-wide">
                        <span>Summary FR</span>
                        <textarea name="summary_fr" rows="3" maxlength="500">{{ old('summary_fr', $page->summary_fr) }}</textarea>
                        @error('summary_fr')<small>{{ $message }}</small>@enderror
                    </label>
                </div>
            </section>

            <section class="admin-form-card">
                <div class="admin-form-section-head">
                    <h2>SEO</h2>
                    <p>Optional search metadata. Empty fields fall back to the localized title and summary.</p>
                </div>

                <div class="admin-form-grid">
                    <label class="admin-field">
                        <span>SEO Title EN</span>
                        <input type="text" name="seo_title_en" value="{{ old('seo_title_en', $page->seo_title_en) }}" maxlength="255">
                        @error('seo_title_en')<small>{{ $message }}</small>@enderror
                    </label>
                    <label class="admin-field">
                        <span>SEO Description EN</span>
                        <textarea name="seo_description_en" rows="3" maxlength="500">{{ old('seo_description_en', $page->seo_description_en) }}</textarea>
                        @error('seo_description_en')<small>{{ $message }}</small>@enderror
                    </label>
                    <label class="admin-field" dir="rtl">
                        <span>عنوان SEO AR</span>
                        <input type="text" name="seo_title_ar" value="{{ old('seo_title_ar', $page->seo_title_ar) }}" maxlength="255" dir="rtl">
                        @error('seo_title_ar')<small>{{ $message }}</small>@enderror
                    </label>
                    <label class="admin-field" dir="rtl">
                        <span>وصف SEO AR</span>
                        <textarea name="seo_description_ar" rows="3" maxlength="500" dir="rtl">{{ old('seo_description_ar', $page->seo_description_ar) }}</textarea>
                        @error('seo_description_ar')<small>{{ $message }}</small>@enderror
                    </label>
                    <label class="admin-field">
                        <span>SEO Title FR</span>
                        <input type="text" name="seo_title_fr" value="{{ old('seo_title_fr', $page->seo_title_fr) }}" maxlength="255">
                        @error('seo_title_fr')<small>{{ $message }}</small>@enderror
                    </label>
                    <label class="admin-field">
                        <span>SEO Description FR</span>
                        <textarea name="seo_description_fr" rows="3" maxlength="500">{{ old('seo_description_fr', $page->seo_description_fr) }}</textarea>
                        @error('seo_description_fr')<small>{{ $message }}</small>@enderror
                    </label>
                </div>
            </section>
        </div>

        <aside class="admin-legal-side">
            <section class="admin-form-card">
                <div class="admin-form-section-head">
                    <h2>Publishing</h2>
                    <p>Draft pages are hidden from public CMS routes.</p>
                </div>

                <label class="admin-field">
                    <span>Status <strong>*</strong></span>
                    <select name="status" required>
                        <option value="draft" @selected($currentStatus === 'draft')>Draft</option>
                        <option value="published" @selected($currentStatus === 'published')>Published</option>
                    </select>
                    @error('status')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field">
                    <span>Published Date</span>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($page->published_at)->format('Y-m-d\TH:i')) }}">
                    @error('published_at')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field">
                    <span>Last Legal Review</span>
                    <input type="date" name="last_reviewed_at" value="{{ old('last_reviewed_at', optional($page->last_reviewed_at)->format('Y-m-d')) }}">
                    @error('last_reviewed_at')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-check-row">
                    <input type="hidden" name="allow_indexing" value="0">
                    <input type="checkbox" name="allow_indexing" value="1" @checked(old('allow_indexing', $page->allow_indexing ?? true))>
                    <span>Allow search engine indexing</span>
                </label>
            </section>

            <section class="admin-form-card">
                <div class="admin-form-section-head">
                    <h2>Public URL</h2>
                    <p>The URL depends on the slug and whether it is a core page.</p>
                </div>

                @if($page->exists)
                    <code class="legal-url-preview">{{ $page->public_url }}</code>
                @else
                    <code class="legal-url-preview">/{{ old('slug', $page->slug ?: 'legal/your-slug') }}</code>
                @endif
            </section>

            <div class="admin-sticky-actions">
                <button type="submit" class="admin-btn admin-btn-primary text-white">
                    <i class="fas fa-floppy-disk"></i>
                    <span>{{ $submitLabel }}</span>
                </button>
                <a href="{{ route('admin.legal-pages.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
            </div>
        </aside>
    </div>
</form>

@push('styles')
    <style>
        .admin-legal-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 360px);
            gap: 1.5rem;
            align-items: start;
        }

        .admin-legal-main,
        .admin-legal-side {
            display: grid;
            gap: 1.25rem;
        }

        .admin-form-card {
            background: var(--sp-color-surface, #fff);
            border: 1px solid var(--sp-color-border-strong, #dbe3ef);
            border-radius: var(--sp-radius-xl, 1rem);
            padding: 1.25rem;
            box-shadow: var(--sp-shadow-xs, 0 1px 2px rgba(15, 23, 42, 0.06));
        }

        .admin-form-section-head {
            margin-bottom: 1rem;
        }

        .admin-form-section-head h2 {
            margin: 0;
            color: var(--sp-color-text, #0f172a);
            font-size: 1rem;
            font-weight: 800;
        }

        .admin-form-section-head p {
            margin: 0.25rem 0 0;
            color: var(--sp-color-text-muted, #64748b);
            font-size: 0.9rem;
        }

        .admin-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .admin-field-wide {
            grid-column: 1 / -1;
        }

        .admin-field {
            display: grid;
            gap: 0.45rem;
        }

        .admin-field span {
            color: var(--sp-color-text, #0f172a);
            font-weight: 800;
            font-size: 0.88rem;
        }

        .admin-field strong {
            color: var(--sp-color-danger, #dc2626);
        }

        .admin-field input,
        .admin-field select,
        .admin-field textarea {
            width: 100%;
            border: 1px solid var(--sp-color-border-strong, #cbd5e1);
            border-radius: var(--sp-radius-lg, 0.75rem);
            padding: 0.75rem 0.85rem;
            color: var(--sp-color-text, #0f172a);
            background: var(--sp-color-surface, #fff);
            font: inherit;
        }

        .admin-field textarea {
            min-height: 8rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            line-height: 1.55;
        }

        .admin-field small,
        .admin-field-error {
            color: var(--sp-color-danger, #dc2626);
            font-weight: 700;
        }

        .admin-field-note {
            color: var(--sp-color-text-muted, #64748b);
            font-size: 0.82rem;
            font-style: normal;
        }

        .admin-check-row {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: var(--sp-color-text, #0f172a);
            font-weight: 700;
        }

        .admin-check-row input {
            width: 1rem;
            height: 1rem;
        }

        .legal-url-preview {
            display: block;
            padding: 0.85rem;
            border-radius: var(--sp-radius-lg, 0.75rem);
            background: var(--sp-color-surface-muted, #f8fafc);
            color: var(--sp-color-text, #0f172a);
            white-space: normal;
            word-break: break-word;
        }

        .admin-sticky-actions {
            position: sticky;
            top: 1rem;
            display: grid;
            gap: 0.75rem;
        }

        @media (max-width: 992px) {
            .admin-legal-layout,
            .admin-form-grid {
                grid-template-columns: 1fr;
            }

            .admin-sticky-actions {
                position: static;
            }
        }
    </style>
@endpush
