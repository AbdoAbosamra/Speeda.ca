{{--
    Gallery Grid — Alpine.js + AJAX
    @props: $serviceProvider (loaded with media)

    Features:
    - Grid of gallery images (max 4)
    - Per-image hover overlay: Replace / Delete buttons
    - "Add Image" card always visible when < 4
    - FileReader preview before upload
    - Loading spinner, inline errors, success flash
    - Modal z-index ≥ 1060
    - CSS logical properties only (RTL-safe)
--}}

@php
    $galleryMedia = $serviceProvider->getMedia('provider_gallery');
    $isOwner = auth()->check() && auth()->id() === $serviceProvider->user_id;
    $maxImages = 4;
@endphp

@if($isOwner || $galleryMedia->count() > 0)
<div class="gallery-section mt-4"
     x-data="galleryManager({
         providerId: {{ $serviceProvider->id }},
         images: {{ Js::from($galleryMedia->map(fn ($m) => [
             'id' => $m->id,
             'thumb_url' => $serviceProvider->getMediaPublicUrl($m, $m->hasGeneratedConversion('gallery_thumb') ? 'gallery_thumb' : null) ?? $m->getUrl(),
             'full_url' => $serviceProvider->getMediaPublicUrl($m, $m->hasGeneratedConversion('gallery_large') ? 'gallery_large' : null) ?? $m->getUrl(),
         ])->values()) }},
         max: {{ $maxImages }},
         isOwner: {{ $isOwner ? 'true' : 'false' }},
         storeUrl: '{{ route('service-providers.gallery.store', $serviceProvider->id) }}',
         csrfToken: '{{ csrf_token() }}'
     })">

    <h5 class="fw-bold mb-3">
        <i class="fas fa-images me-2 text-primary"></i>
        {{ __('service_provider.gallery') }}
        <span class="badge bg-primary ms-2" x-text="images.length + '/' + max"></span>
    </h5>

    {{-- Flash messages --}}
    <div x-show="flashMessage" x-transition.duration.300ms
         class="alert d-flex align-items-center mb-3"
         :class="flashType === 'success' ? 'alert-success' : 'alert-danger'"
         style="border-radius: 12px;">
        <i class="fas me-2" :class="flashType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
        <span x-text="flashMessage"></span>
        <button type="button" class="btn-close ms-auto" @click="flashMessage = ''"></button>
    </div>

    {{-- Error message --}}
    <div x-show="errorMessage" x-transition.duration.300ms
         class="alert alert-danger d-flex align-items-center mb-3"
         style="border-radius: 12px;">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <span x-text="errorMessage"></span>
        <button type="button" class="btn-close ms-auto" @click="errorMessage = ''"></button>
    </div>

    {{-- Gallery Grid --}}
    <div class="gallery-grid" style="
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    ">
        {{-- Existing images --}}
        <template x-for="(img, index) in images" :key="img.id">
            <div class="gallery-card" style="
                position: relative;
                border-radius: 16px;
                overflow: hidden;
                aspect-ratio: 1;
                background: #f1f5f9;
                border: 2px solid #e2e8f0;
                transition: all 0.3s ease;
            "
            @mouseenter="hoveredId = img.id"
            @mouseleave="hoveredId = null">

                <img :src="img.thumb_url"
                     :alt="'Gallery image ' + (index + 1)"
                     style="width: 100%; height: 100%; object-fit: cover;"
                     loading="lazy">

                {{-- Owner hover overlay --}}
                @if($isOwner)
                <div x-show="hoveredId === img.id" x-transition.opacity.duration.200ms
                     style="
                        position: absolute;
                        inset: 0;
                        background: rgba(0,0,0,0.55);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.75rem;
                     ">
                    {{-- Replace button --}}
                    <button type="button"
                            @click="startReplace(img)"
                            class="btn btn-sm btn-light"
                            style="border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;"
                            :disabled="uploading"
                            title="{{ __('service_provider.replace_image') }}">
                        <i class="fas fa-pencil-alt"></i>
                    </button>

                    {{-- Delete button --}}
                    <button type="button"
                            @click="confirmDelete(img)"
                            class="btn btn-sm btn-danger"
                            style="border-radius: 50%; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;"
                            :disabled="uploading"
                            title="{{ __('service_provider.delete_image') }}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                @endif
            </div>
        </template>

        {{-- Upload preview card (shown during upload) --}}
        <div x-show="previewUrl" x-transition
             style="
                position: relative;
                border-radius: 16px;
                overflow: hidden;
                aspect-ratio: 1;
                background: #f1f5f9;
                border: 2px dashed #667eea;
             ">
            <img :src="previewUrl" alt="Preview" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.6;">
            <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
                <div class="gallery-spinner"></div>
            </div>
        </div>

        {{-- Add Image card --}}
        @if($isOwner)
        <div x-show="images.length < max && !previewUrl"
             x-transition
             @click="$refs.galleryFileInput.click()"
             style="
                border-radius: 16px;
                aspect-ratio: 1;
                background: linear-gradient(135deg, #f8fafc, #eef2ff);
                border: 2px dashed #c7d2fe;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                gap: 0.5rem;
             "
             class="gallery-add-card">
            <i class="fas fa-plus-circle" style="font-size: 2.5rem; color: #818cf8;"></i>
            <span style="font-weight: 600; color: #6366f1; font-size: 0.9rem;">
                {{ __('service_provider.add_image') }}
            </span>
            <span style="font-size: 0.75rem; color: #94a3b8;">
                JPG, PNG, WebP · {{ __('service_provider.max_5mb') }}
            </span>
        </div>
        @endif
    </div>

    {{-- Hidden file inputs --}}
    @if($isOwner)
    <input type="file" x-ref="galleryFileInput"
           accept="image/jpeg,image/png,image/webp"
           @change="handleFileSelect($event, 'add')"
           style="display: none;">

    <input type="file" x-ref="galleryReplaceInput"
           accept="image/jpeg,image/png,image/webp"
           @change="handleFileSelect($event, 'replace')"
           style="display: none;">
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($isOwner)
    <div x-show="showDeleteModal" x-transition.opacity
         style="
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 1065;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
         "
         @click.self="showDeleteModal = false"
         @keydown.escape.window="showDeleteModal = false">

        <div style="
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            z-index: 1070;
            text-align: center;
        ">
            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <h5 class="fw-bold mb-2">{{ __('service_provider.confirm_delete_title') }}</h5>
            <p class="text-muted mb-4">{{ __('service_provider.confirm_delete_message') }}</p>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button"
                        class="btn btn-outline-secondary px-4"
                        @click="showDeleteModal = false"
                        style="border-radius: 12px;">
                    {{ __('general.cancel') }}
                </button>
                <button type="button"
                        class="btn btn-danger px-4"
                        @click="executeDelete()"
                        :disabled="uploading"
                        style="border-radius: 12px;">
                    <span x-show="!uploading">{{ __('general.delete') }}</span>
                    <span x-show="uploading"><div class="gallery-spinner-sm"></div></span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Gallery Component Styles --}}
<style>
    .gallery-add-card:hover {
        border-color: #818cf8 !important;
        background: linear-gradient(135deg, #eef2ff, #e0e7ff) !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.15);
    }

    .gallery-card:hover {
        border-color: #818cf8 !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .gallery-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: galleryspinner 0.8s linear infinite;
    }

    .gallery-spinner-sm {
        width: 18px;
        height: 18px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: galleryspinner 0.8s linear infinite;
        display: inline-block;
    }

    @keyframes galleryspinner {
        to { transform: rotate(360deg); }
    }

    /* RTL adjustments using logical properties */
    [dir="rtl"] .gallery-section .me-2 {
        margin-inline-end: 0.5rem !important;
        margin-inline-start: 0 !important;
    }

    [dir="rtl"] .gallery-section .ms-2 {
        margin-inline-start: 0.5rem !important;
        margin-inline-end: 0 !important;
    }

    [dir="rtl"] .gallery-section .ms-auto {
        margin-inline-start: auto !important;
    }
</style>

{{-- Gallery Alpine.js Component Logic --}}
<script>
    function galleryManager(config) {
        return {
            providerId: config.providerId,
            images: config.images,
            max: config.max,
            isOwner: config.isOwner,
            storeUrl: config.storeUrl,
            csrfToken: config.csrfToken,

            // UI state
            hoveredId: null,
            uploading: false,
            previewUrl: null,
            flashMessage: '',
            flashType: 'success',
            errorMessage: '',
            showDeleteModal: false,

            // working state
            pendingAction: null,   // 'add' | 'replace'
            pendingMedia: null,    // media object for replace/delete

            flash(message, type = 'success') {
                this.flashMessage = message;
                this.flashType = type;
                setTimeout(() => { this.flashMessage = ''; }, 4000);
            },

            startReplace(img) {
                this.pendingMedia = img;
                this.$refs.galleryReplaceInput.value = '';
                this.$refs.galleryReplaceInput.click();
            },

            confirmDelete(img) {
                this.pendingMedia = img;
                this.showDeleteModal = true;
            },

            handleFileSelect(event, action) {
                const file = event.target.files[0];
                if (!file) return;

                // Client-side validation
                const allowed = ['image/jpeg', 'image/png', 'image/webp'];
                if (!allowed.includes(file.type)) {
                    this.errorMessage = 'Please select a JPG, PNG, or WebP image.';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    this.errorMessage = 'File is too large. Maximum size is 5MB.';
                    return;
                }

                this.errorMessage = '';

                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewUrl = e.target.result;
                };
                reader.readAsDataURL(file);

                if (action === 'add') {
                    this.uploadImage(file);
                } else if (action === 'replace') {
                    this.replaceImage(file);
                }
            },

            async uploadImage(file) {
                this.uploading = true;
                const formData = new FormData();
                formData.append('gallery_image', file);

                try {
                    const res = await fetch(this.storeUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const data = await res.json();

                    if (data.success) {
                        this.images.push(data.media);
                        this.flash(data.message);
                    } else {
                        this.errorMessage = data.message || 'Upload failed.';
                    }
                } catch (err) {
                    this.errorMessage = 'Network error. Please try again.';
                } finally {
                    this.uploading = false;
                    this.previewUrl = null;
                    this.$refs.galleryFileInput.value = '';
                }
            },

            async replaceImage(file) {
                if (!this.pendingMedia) return;
                this.uploading = true;

                const url = `/service-providers/profile/${this.providerId}/gallery/${this.pendingMedia.id}/replace`;
                const formData = new FormData();
                formData.append('gallery_image', file);

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const data = await res.json();

                    if (data.success) {
                        // Swap the old media entry with the new one
                        const idx = this.images.findIndex(i => i.id === this.pendingMedia.id);
                        if (idx !== -1) {
                            this.images.splice(idx, 1, data.media);
                        }
                        this.flash(data.message);
                    } else {
                        this.errorMessage = data.message || 'Replace failed.';
                    }
                } catch (err) {
                    this.errorMessage = 'Network error. Please try again.';
                } finally {
                    this.uploading = false;
                    this.previewUrl = null;
                    this.pendingMedia = null;
                    this.$refs.galleryReplaceInput.value = '';
                }
            },

            async executeDelete() {
                if (!this.pendingMedia) return;
                this.uploading = true;

                const url = `/service-providers/profile/${this.providerId}/gallery/${this.pendingMedia.id}/ajax`;

                try {
                    const res = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                    });

                    const data = await res.json();

                    if (data.success) {
                        this.images = this.images.filter(i => i.id !== this.pendingMedia.id);
                        this.flash(data.message);
                    } else {
                        this.errorMessage = data.message || 'Delete failed.';
                    }
                } catch (err) {
                    this.errorMessage = 'Network error. Please try again.';
                } finally {
                    this.uploading = false;
                    this.showDeleteModal = false;
                    this.pendingMedia = null;
                }
            }
        };
    }
</script>
@endif
