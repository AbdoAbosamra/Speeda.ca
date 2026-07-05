@php
    $currentImage = $post->exists ? $post->image_url : asset('images/banner.png');
    $currentStatus = old('status', $post->status ?: ($post->is_published ? 'published' : 'draft'));
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="admin-blog-form">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="admin-form-main">
        <section class="admin-form-card">
            <div class="admin-form-section-head">
                <h2>Multilingual Content</h2>
                <p>Blog posts are managed in English, Arabic, and French for the public site.</p>
            </div>

            <div class="admin-form-grid">
                <label class="admin-field admin-field-wide">
                    <span>Slug</span>
                    <input id="blog-slug" type="text" name="slug" value="{{ old('slug', $post->slug) }}" placeholder="auto-generated-from-english-title">
                    @error('slug')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Title EN <strong>*</strong></span>
                    <input id="blog-title-en" type="text" name="title_en" value="{{ old('title_en', $post->title_en ?: $post->title) }}" required placeholder="Enter English title">
                    @error('title_en')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Content EN <strong>*</strong></span>
                    <textarea name="content_en" rows="12" class="rich-editor" data-editor-dir="ltr" placeholder="Write the English article content">{{ old('content_en', $post->content_en ?: $post->content) }}</textarea>
                    @error('content_en')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Excerpt EN</span>
                    <textarea name="excerpt_en" rows="3" maxlength="500" placeholder="Optional English summary for blog cards">{{ old('excerpt_en', $post->excerpt_en ?: $post->excerpt) }}</textarea>
                    @error('excerpt_en')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Title AR <strong>*</strong></span>
                    <input type="text" name="title_ar" value="{{ old('title_ar', $post->title_ar) }}" required placeholder="اكتب عنوان المقال بالعربية" dir="rtl">
                    @error('title_ar')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Content AR <strong>*</strong></span>
                    <textarea name="content_ar" rows="12" class="rich-editor" data-editor-dir="rtl" placeholder="اكتب محتوى المقال بالعربية" dir="rtl">{{ old('content_ar', $post->content_ar) }}</textarea>
                    @error('content_ar')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Excerpt AR</span>
                    <textarea name="excerpt_ar" rows="3" maxlength="500" placeholder="ملخص اختياري بالعربية" dir="rtl">{{ old('excerpt_ar', $post->excerpt_ar) }}</textarea>
                    @error('excerpt_ar')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Title FR <strong>*</strong></span>
                    <input type="text" name="title_fr" value="{{ old('title_fr', $post->title_fr) }}" required placeholder="Entrez le titre en français">
                    @error('title_fr')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Content FR <strong>*</strong></span>
                    <textarea name="content_fr" rows="12" class="rich-editor" data-editor-dir="ltr" placeholder="Rédigez le contenu en français">{{ old('content_fr', $post->content_fr) }}</textarea>
                    @error('content_fr')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>Excerpt FR</span>
                    <textarea name="excerpt_fr" rows="3" maxlength="500" placeholder="Résumé optionnel en français">{{ old('excerpt_fr', $post->excerpt_fr) }}</textarea>
                    @error('excerpt_fr')<small>{{ $message }}</small>@enderror
                </label>
            </div>
        </section>

        <section class="admin-form-card">
            <div class="admin-form-section-head">
                <h2>SEO Metadata</h2>
                <p>These fields control search snippets and social previews. Empty fields use the title and excerpt.</p>
            </div>

            <div class="admin-form-grid">
                <label class="admin-field admin-field-wide">
                    <span>SEO Title EN</span>
                    <input type="text" name="seo_title_en" value="{{ old('seo_title_en', $post->seo_title_en) }}" maxlength="255" placeholder="Recommended 50-60 characters">
                    @error('seo_title_en')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>SEO Description EN</span>
                    <textarea name="seo_description_en" rows="3" maxlength="500" placeholder="Recommended 150-160 characters">{{ old('seo_description_en', $post->seo_description_en) }}</textarea>
                    @error('seo_description_en')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>SEO Title AR</span>
                    <input type="text" name="seo_title_ar" value="{{ old('seo_title_ar', $post->seo_title_ar) }}" maxlength="255" dir="rtl">
                    @error('seo_title_ar')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>SEO Description AR</span>
                    <textarea name="seo_description_ar" rows="3" maxlength="500" dir="rtl">{{ old('seo_description_ar', $post->seo_description_ar) }}</textarea>
                    @error('seo_description_ar')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>SEO Title FR</span>
                    <input type="text" name="seo_title_fr" value="{{ old('seo_title_fr', $post->seo_title_fr) }}" maxlength="255">
                    @error('seo_title_fr')<small>{{ $message }}</small>@enderror
                </label>

                <label class="admin-field admin-field-wide">
                    <span>SEO Description FR</span>
                    <textarea name="seo_description_fr" rows="3" maxlength="500">{{ old('seo_description_fr', $post->seo_description_fr) }}</textarea>
                    @error('seo_description_fr')<small>{{ $message }}</small>@enderror
                </label>
            </div>
        </section>
    </div>

    <aside class="admin-form-side">
        <section class="admin-form-card">
            <div class="admin-form-section-head">
                <h2>Publishing</h2>
                <p>Drafts stay hidden from public blog pages.</p>
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
                <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}">
                @error('published_at')<small>{{ $message }}</small>@enderror
            </label>

            <label class="admin-field">
                <span>Category</span>
                <select name="category_id">
                    <option value="">No category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $post->category_id) === (string) $category->id)>
                            {{ $category->localized_name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<small>{{ $message }}</small>@enderror
            </label>

            <label class="admin-field">
                <span>Location</span>
                <select name="location_id">
                    <option value="">No location (Global)</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" @selected((string) old('location_id', $post->location_id) === (string) $location->id)>
                            {{ $location->city }}
                        </option>
                    @endforeach
                </select>
                @error('location_id')<small>{{ $message }}</small>@enderror
            </label>

            <label class="admin-check-row">
                <input type="hidden" name="allow_indexing" value="0">
                <input type="checkbox" name="allow_indexing" value="1" @checked(old('allow_indexing', $post->allow_indexing ?? true))>
                <span>Allow search engine indexing</span>
            </label>
        </section>

        <section class="admin-form-card">
            <div class="admin-form-section-head">
                <h2>Featured Image</h2>
                <p>JPG, PNG, WebP, or GIF up to 5 MB.</p>
            </div>

            <div class="admin-image-preview">
                <img id="blog-image-preview" src="{{ $currentImage }}" alt="Featured image preview" loading="lazy">
            </div>

            <label class="admin-file-field">
                <i class="fas fa-upload"></i>
                <span>Upload image</span>
                <input id="featured-image-input" type="file" name="featured_image" accept="image/*">
            </label>
            @error('featured_image')<small class="admin-field-error">{{ $message }}</small>@enderror
        </section>

        <div class="admin-sticky-actions">
            <button type="submit" class="admin-btn admin-btn-primary text-white">
                <i class="fas fa-floppy-disk"></i>
                <span>Save Blog</span>
            </button>
            <button type="button" id="blog-preview-btn" class="admin-btn admin-btn-secondary">
                <i class="fas fa-eye"></i>
                <span>Preview</span>
            </button>
            @if($post->exists && ($post->status === 'published' || $post->is_published))
                <a href="{{ route('blogs.show', $post) }}" class="admin-btn admin-btn-secondary" target="_blank" rel="noopener">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    <span>View Public Page</span>
                </a>
            @endif
            <a href="{{ route('admin.blog.posts.index') }}" class="admin-btn admin-btn-secondary">Cancel</a>
        </div>
    </aside>
</form>

{{-- ===================== Live Article Preview Modal ===================== --}}
<div id="blog-preview-modal" class="blog-preview-modal" aria-hidden="true">
    <div class="blog-preview-backdrop" data-preview-close></div>
    <div class="blog-preview-dialog" role="dialog" aria-modal="true" aria-label="Article preview">
        <header class="blog-preview-head">
            <div class="blog-preview-tabs">
                <button type="button" class="blog-preview-tab is-active" data-preview-lang="en">EN</button>
                <button type="button" class="blog-preview-tab" data-preview-lang="ar">AR</button>
                <button type="button" class="blog-preview-tab" data-preview-lang="fr">FR</button>
            </div>
            <span class="blog-preview-label"><i class="fas fa-eye"></i> Live Preview</span>
            <button type="button" class="blog-preview-x" data-preview-close aria-label="Close preview">&times;</button>
        </header>
        <div class="blog-preview-body">
            <article class="blog-preview-stage" data-preview-stage dir="ltr"></article>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .blog-preview-modal { position: fixed; inset: 0; z-index: 1080; display: none; }
        .blog-preview-modal.is-open { display: block; }
        .blog-preview-backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.62); }
        .blog-preview-dialog { position: relative; max-width: 920px; margin: 3vh auto; height: 94vh; background: #fff; border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,.35); display: flex; flex-direction: column; overflow: hidden; }
        .blog-preview-head { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; }
        .blog-preview-tabs { display: flex; gap: 6px; }
        .blog-preview-tab { border: 1px solid #d1d5db; background: #fff; color: #374151; padding: 5px 14px; border-radius: 8px; font-weight: 700; font-size: .8rem; cursor: pointer; transition: all .15s; }
        .blog-preview-tab:hover { border-color: #2563eb; color: #2563eb; }
        .blog-preview-tab.is-active { background: #2563eb; border-color: #2563eb; color: #fff; }
        .blog-preview-label { margin-inline-start: auto; color: #64748b; font-size: .85rem; font-weight: 600; }
        .blog-preview-x { border: 0; background: transparent; font-size: 1.7rem; line-height: 1; color: #64748b; cursor: pointer; padding: 0 6px; }
        .blog-preview-x:hover { color: #111; }
        .blog-preview-body { overflow-y: auto; padding: 32px clamp(16px, 5vw, 56px); background: #fff; flex: 1; }
        .blog-preview-stage { max-width: 760px; margin: 0 auto; }
        .blog-preview-hero { width: 100%; max-height: 380px; object-fit: cover; border-radius: 14px; margin-bottom: 24px; }
        .blog-preview-title { font-size: 2rem; font-weight: 800; color: #0f172a; line-height: 1.25; margin: 0 0 12px; }
        .blog-preview-meta { color: #94a3b8; font-size: .85rem; font-weight: 600; margin-bottom: 18px; }
        .blog-preview-excerpt { font-size: 1.1rem; color: #475569; line-height: 1.6; margin-bottom: 24px; }

        /* Mirror the public article prose styling so the preview matches 1:1 */
        #blog-preview-modal .blog-content { font-size: 1.05rem; line-height: 1.8; color: #495057; }
        #blog-preview-modal .blog-content > *:first-child { margin-top: 0; }
        #blog-preview-modal .blog-content h2 { font-size: 1.7rem; font-weight: 700; color: #1f2937; margin: 2rem 0 1rem; line-height: 1.3; }
        #blog-preview-modal .blog-content h3 { font-size: 1.35rem; font-weight: 700; color: #1f2937; margin: 1.75rem 0 .85rem; line-height: 1.35; }
        #blog-preview-modal .blog-content h4 { font-size: 1.15rem; font-weight: 700; color: #374151; margin: 1.5rem 0 .75rem; }
        #blog-preview-modal .blog-content p { margin: 0 0 1.15rem; }
        #blog-preview-modal .blog-content a { color: #2563eb; text-decoration: underline; text-underline-offset: 2px; }
        #blog-preview-modal .blog-content ul, #blog-preview-modal .blog-content ol { margin: 0 0 1.15rem; padding-inline-start: 1.6rem; }
        #blog-preview-modal .blog-content li { margin-bottom: .5rem; }
        #blog-preview-modal .blog-content blockquote { margin: 1.5rem 0; padding: .85rem 1.25rem; border-inline-start: 4px solid #2563eb; background: #f8fafc; color: #475569; font-style: italic; border-radius: 6px; }
        #blog-preview-modal .blog-content img { max-width: 100%; height: auto; border-radius: 10px; margin: 1.25rem 0; }
        #blog-preview-modal .blog-content table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
        #blog-preview-modal .blog-content th, #blog-preview-modal .blog-content td { border: 1px solid #e5e7eb; padding: .6rem .85rem; text-align: start; }
        #blog-preview-modal .blog-content th { background: #f9fafb; font-weight: 700; }
        #blog-preview-modal .blog-content hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const title = document.getElementById('blog-title-en');
            const slug = document.getElementById('blog-slug');
            const imageInput = document.getElementById('featured-image-input');
            const preview = document.getElementById('blog-image-preview');
            let slugTouched = Boolean(slug?.value);

            function slugify(value) {
                return value
                    .toString()
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            slug?.addEventListener('input', () => slugTouched = true);
            title?.addEventListener('input', () => {
                if (!slugTouched && slug) slug.value = slugify(title.value);
            });

            imageInput?.addEventListener('change', () => {
                const file = imageInput.files?.[0];
                if (!file || !preview) return;
                preview.src = URL.createObjectURL(file);
            });
        })();
    </script>

    {{-- Rich text editor (TinyMCE) for the multilingual article body --}}
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
    <script>
        (() => {
            if (typeof tinymce === 'undefined') return;

            document.querySelectorAll('textarea.rich-editor').forEach((el) => {
                const dir = el.getAttribute('data-editor-dir') === 'rtl' ? 'rtl' : 'ltr';

                tinymce.init({
                    target: el,
                    directionality: dir,
                    height: 460,
                    menubar: false,
                    branding: false,
                    promotion: false,
                    plugins: 'lists link image table code autolink hr',
                    toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist | blockquote | link image table | alignleft aligncenter alignright | removeformat | code',
                    block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Quote=blockquote',
                    // Images are inserted by URL only (no server upload).
                    image_uploadtab: false,
                    automatic_uploads: false,
                    file_picker_types: '',
                    // Links open in a new, safe tab by default.
                    link_default_target: '_blank',
                    link_assume_external_targets: true,
                    default_link_target: '_blank',
                    rel_list: [{ title: 'External (safe)', value: 'noopener noreferrer' }],
                    convert_urls: false,
                    // Defense in depth: never keep <script> tags in stored content.
                    invalid_elements: 'script',
                    content_style: 'body{font-family:inherit;font-size:16px;line-height:1.8} img{max-width:100%;height:auto}',
                    setup: (editor) => {
                        // Keep the underlying <textarea> in sync so server-side validation sees the value.
                        editor.on('change keyup', () => editor.save());
                    },
                });
            });

            // Flush all editors into their textareas right before the form submits.
            const form = document.querySelector('form.admin-blog-form');
            form?.addEventListener('submit', () => {
                if (typeof tinymce !== 'undefined') tinymce.triggerSave();
            });
        })();
    </script>

    {{-- Live preview: renders the article exactly like the public page, reflecting unsaved edits --}}
    <script>
        (() => {
            const openBtn = document.getElementById('blog-preview-btn');
            const modal = document.getElementById('blog-preview-modal');
            if (!openBtn || !modal) return;

            const stage = modal.querySelector('[data-preview-stage]');
            const tabs = modal.querySelectorAll('[data-preview-lang]');
            const closeEls = modal.querySelectorAll('[data-preview-close]');
            let currentLang = 'en';

            const field = (name) => document.querySelector(`[name="${name}"]`);

            function escapeHtml(s) {
                return (s || '').replace(/[&<>"']/g, (c) => (
                    { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
                ));
            }

            function stripTags(html) {
                const tmp = document.createElement('div');
                tmp.innerHTML = html || '';
                return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
            }

            function readContent(lang) {
                // Flush editors so the textarea values reflect the latest edits.
                if (typeof tinymce !== 'undefined') tinymce.triggerSave();
                return field('content_' + lang)?.value || '';
            }

            function render(lang) {
                currentLang = lang;
                const dir = lang === 'ar' ? 'rtl' : 'ltr';
                const title = (field('title_' + lang)?.value || '').trim() || '(Untitled)';
                const contentHtml = readContent(lang);
                const plain = stripTags(contentHtml);

                let excerpt = (field('excerpt_' + lang)?.value || '').trim();
                if (!excerpt && plain) {
                    excerpt = plain.length > 180 ? plain.slice(0, 180).trim() + '…' : plain;
                }

                const words = plain ? plain.split(' ').filter(Boolean).length : 0;
                const readingTime = Math.max(1, Math.ceil(words / 220));
                const img = document.getElementById('blog-image-preview')?.src || '';
                const readLabel = lang === 'ar' ? 'دقيقة قراءة' : (lang === 'fr' ? 'min de lecture' : 'min read');
                const wordLabel = lang === 'ar' ? 'كلمة' : (lang === 'fr' ? 'mots' : 'words');

                stage.setAttribute('dir', dir);
                stage.innerHTML =
                    (img ? `<img class="blog-preview-hero" src="${img}" alt="">` : '') +
                    `<h1 class="blog-preview-title">${escapeHtml(title)}</h1>` +
                    `<div class="blog-preview-meta">⏱ ${readingTime} ${readLabel} · ${words} ${wordLabel}</div>` +
                    (excerpt ? `<p class="blog-preview-excerpt">${escapeHtml(excerpt)}</p>` : '') +
                    `<div class="blog-content">${contentHtml || '<p style="color:#9ca3af">No content yet…</p>'}</div>`;

                tabs.forEach((t) => t.classList.toggle('is-active', t.getAttribute('data-preview-lang') === lang));
            }

            function open() {
                render(currentLang);
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function close() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            openBtn.addEventListener('click', open);
            closeEls.forEach((el) => el.addEventListener('click', close));
            tabs.forEach((t) => t.addEventListener('click', () => render(t.getAttribute('data-preview-lang'))));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modal.classList.contains('is-open')) close();
            });
        })();
    </script>
@endpush
