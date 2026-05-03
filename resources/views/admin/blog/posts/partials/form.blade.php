<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="card border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif
    <div class="card-body p-5 p-lg-6">
        <!-- Main Info Section -->
        <div class="mb-6 pb-4 border-bottom">
            <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem;">📝 {{ __('Basic Information') }}</h5>
            <div class="row g-4">
                <div class="col-lg-8">
                    <label class="form-label fw-bold mb-2">{{ __('Title (EN)') }} <span style="color: #dc3545;">*</span></label>
                    <input type="text" name="title_en" class="form-control form-control-lg" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="Enter post title in English" value="{{ old('title_en', $post?->title_en) }}" required>
                    @error('title_en')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold mb-2">{{ __('Status') }} <span style="color: #dc3545;">*</span></label>
                    <select name="status" class="form-select form-select-lg" style="border-radius: 8px; border: 1px solid #e0e0e0;">
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $post?->status?->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">{{ __('Title (AR)') }}</label>
                    <input type="text" name="title_ar" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="العنوان بالعربية" value="{{ old('title_ar', $post?->title_ar) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">{{ __('Title (FR)') }}</label>
                    <input type="text" name="title_fr" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="Titre en français" value="{{ old('title_fr', $post?->title_fr) }}">
                </div>
            </div>
        </div>

        <!-- Category & Publishing Section -->
        <div class="mb-6 pb-4 border-bottom">
            <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem;">📂 {{ __('Organization & Publishing') }}</h5>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">{{ __('Category') }}</label>
                    <select name="category_id" class="form-select" style="border-radius: 8px; border: 1px solid #e0e0e0;">
                        <option value="">{{ __('Select a category...') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $post?->category_id) === (string) $category->id)>{{ $category->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">{{ __('Published At') }}</label>
                    <input type="datetime-local" name="published_at" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" value="{{ old('published_at', optional($post?->published_at)->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="mb-6 pb-4 border-bottom">
            <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem;">📄 {{ __('Content') }}</h5>
            <div class="row g-4">
                <div class="col-12">
                    <label class="form-label fw-bold mb-2">{{ __('Content (EN)') }} <span style="color: #dc3545;">*</span></label>
                    <textarea name="content_en" rows="10" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0; font-family: 'Monaco', monospace;" placeholder="Write your content here..." required>{{ old('content_en', $post?->content_en) }}</textarea>
                    @error('content_en')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">{{ __('Content (AR)') }}</label>
                    <textarea name="content_ar" rows="8" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="المحتوى بالعربية">{{ old('content_ar', $post?->content_ar) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">{{ __('Content (FR)') }}</label>
                    <textarea name="content_fr" rows="8" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="Contenu en français">{{ old('content_fr', $post?->content_fr) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Excerpt Section -->
        <div class="mb-6 pb-4 border-bottom">
            <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem;">✂️ {{ __('Excerpts') }}</h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('Excerpt (EN)') }}</label>
                    <textarea name="excerpt_en" rows="4" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="Brief summary in English">{{ old('excerpt_en', $post?->excerpt_en) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('Excerpt (AR)') }}</label>
                    <textarea name="excerpt_ar" rows="4" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="ملخص بالعربية">{{ old('excerpt_ar', $post?->excerpt_ar) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('Excerpt (FR)') }}</label>
                    <textarea name="excerpt_fr" rows="4" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="Résumé en français">{{ old('excerpt_fr', $post?->excerpt_fr) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Media Section -->
        <div class="mb-6 pb-4 border-bottom">
            <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem;">🖼️ {{ __('Featured Image') }}</h5>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-2 border-dashed" style="border-radius: 8px; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.3s ease;" onmouseover="this.style.borderColor='#667eea'; this.style.backgroundColor='#f8f9ff'" onmouseout="this.style.borderColor='#dee2e6'; this.style.backgroundColor='transparent'">
                        <input type="file" name="featured_image" class="form-control" style="border: none;" id="featured_image" onchange="this.parentElement.querySelector('.file-name').textContent = this.files[0]?.name || 'Click to upload'">
                        <div style="margin-top: 0.5rem;">
                            <span style="font-size: 2rem;">📤</span>
                            <p class="text-muted mt-2 mb-0">{{ __('Click to upload or drag & drop') }}</p>
                            <small class="text-muted">PNG, JPG, GIF (max 5MB)</small>
                        </div>
                        <p class="text-primary fw-bold mt-2 file-name" style="display: none; margin: 0;"></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-2">
                        <input type="hidden" name="allow_indexing" value="0">
                        <input type="checkbox" class="form-check-input" id="allow_indexing" name="allow_indexing" value="1" @checked(old('allow_indexing', $post?->allow_indexing ?? true)) style="width: 1.25rem; height: 1.25rem;">
                        <label for="allow_indexing" class="form-check-label fw-bold">{{ __('Allow Search Engine Indexing') }}</label>
                        <small class="d-block text-muted mt-2">{{ __('This will allow search engines to index this post') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Section -->
        <div class="mb-6 pb-4 border-bottom">
            <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem;">🔍 {{ __('SEO Settings') }}</h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('SEO Title (EN)') }}</label>
                    <input type="text" name="seo_title_en" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="SEO title in English" value="{{ old('seo_title_en', $post?->seo_title_en) }}">
                    <small class="text-muted">Recommended: 50-60 characters</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('SEO Title (AR)') }}</label>
                    <input type="text" name="seo_title_ar" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="العنوان للـ SEO" value="{{ old('seo_title_ar', $post?->seo_title_ar) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('SEO Title (FR)') }}</label>
                    <input type="text" name="seo_title_fr" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="Titre SEO français" value="{{ old('seo_title_fr', $post?->seo_title_fr) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('SEO Description (EN)') }}</label>
                    <textarea name="seo_description_en" rows="3" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="SEO description">{{ old('seo_description_en', $post?->seo_description_en) }}</textarea>
                    <small class="text-muted">Recommended: 150-160 characters</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('SEO Description (AR)') }}</label>
                    <textarea name="seo_description_ar" rows="3" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="الوصف للـ SEO">{{ old('seo_description_ar', $post?->seo_description_ar) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold mb-2">{{ __('SEO Description (FR)') }}</label>
                    <textarea name="seo_description_fr" rows="3" class="form-control" style="border-radius: 8px; border: 1px solid #e0e0e0;" placeholder="Description SEO">{{ old('seo_description_fr', $post?->seo_description_fr) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.boxShadow='0 8px 16px rgba(102, 126, 234, 0.4)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow=''; this.style.transform='translateY(0)'">
                💾 {{ __('Save Post') }}
            </button>
            <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-lg" style="background: #f0f0f0; color: #333; border: none; padding: 0.75rem 2rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#e0e0e0'" onmouseout="this.style.backgroundColor='#f0f0f0'">
                ❌ {{ __('Cancel') }}
            </a>
        </div>
    </div>
</form>
