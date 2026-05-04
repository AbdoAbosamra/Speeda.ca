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
                    <textarea name="content_en" rows="12" required placeholder="Write the English article content">{{ old('content_en', $post->content_en ?: $post->content) }}</textarea>
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
                    <textarea name="content_ar" rows="12" required placeholder="اكتب محتوى المقال بالعربية" dir="rtl">{{ old('content_ar', $post->content_ar) }}</textarea>
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
                    <textarea name="content_fr" rows="12" required placeholder="Rédigez le contenu en français">{{ old('content_fr', $post->content_fr) }}</textarea>
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
                            {{ $category->name_en ?: $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<small>{{ $message }}</small>@enderror
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
            <button type="submit" class="admin-btn admin-btn-primary">
                <i class="fas fa-floppy-disk"></i>
                <span>Save Blog</span>
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
@endpush
