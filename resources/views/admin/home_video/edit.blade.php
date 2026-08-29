@extends('layouts.app')

@section('title', __('admin.home_video'))

@section('content')
    @php
        $isLive = $video->is_active && $video->isPlayable();
        $isIncomplete = $video->is_active && !$video->isPlayable();
        $sourceType = old('source_type', $video->source_type);
        $locales = ['en' => 'English', 'ar' => 'العربية', 'fr' => 'Français'];
    @endphp

    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Content"
                :title="__('admin.home_video')"
                :subtitle="__('admin.home_video_subtitle')"
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('home')"
                        variant="secondary"
                        icon="fas fa-arrow-up-right-from-square"
                        class="admin-btn admin-btn-secondary"
                    >
                        {{ __('admin.view_public_profile') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            {{-- Status first: the single most common mistake is switching the
                 section on and assuming it is live when no video is set. --}}
            <div class="hv-status {{ $isLive ? 'is-live' : ($isIncomplete ? 'is-warn' : 'is-off') }}">
                <i class="fas {{ $isLive ? 'fa-circle-check' : ($isIncomplete ? 'fa-triangle-exclamation' : 'fa-circle-minus') }}"></i>
                <span>
                    @if($isLive)
                        {{ __('admin.home_video_status_live') }}
                    @elseif($isIncomplete)
                        {{ __('admin.home_video_status_incomplete') }}
                    @else
                        {{ __('admin.home_video_status_hidden') }}
                    @endif
                </span>
            </div>

            <form action="{{ route('admin.home_video.update') }}" method="POST" enctype="multipart/form-data" class="hv-form">
                @csrf
                @method('PUT')

                <div class="hv-grid">
                    <div class="hv-main">
                        {{-- ============ SOURCE ============ --}}
                        <div class="hv-card">
                            <h3>{{ __('admin.home_video_source') }}</h3>

                            <label class="hv-source-option {{ $sourceType === 'embed' ? 'is-selected' : '' }}" data-source="embed">
                                <input type="radio" name="source_type" value="embed" @checked($sourceType === 'embed')>
                                <span>
                                    <strong><i class="fab fa-youtube"></i> {{ __('admin.home_video_source_embed') }}</strong>
                                    <small>{{ __('admin.home_video_source_embed_hint') }}</small>
                                </span>
                            </label>

                            <label class="hv-source-option {{ $sourceType === 'upload' ? 'is-selected' : '' }}" data-source="upload">
                                <input type="radio" name="source_type" value="upload" @checked($sourceType === 'upload')>
                                <span>
                                    <strong><i class="fas fa-file-video"></i> {{ __('admin.home_video_source_upload') }}</strong>
                                    <small>{{ __('admin.home_video_source_upload_hint') }}</small>
                                </span>
                            </label>
                            @error('source_type') <span class="hv-error">{{ $message }}</span> @enderror

                            {{-- Embed fields --}}
                            <div class="hv-source-panel" data-panel="embed">
                                <div class="hv-field">
                                    <label for="video_url">{{ __('admin.home_video_url') }}</label>
                                    <input type="url" name="video_url" id="video_url" maxlength="500"
                                           placeholder="https://www.youtube.com/watch?v=..."
                                           value="{{ old('video_url', $video->video_url) }}">
                                    <small class="hv-hint">{{ __('admin.home_video_url_hint') }}</small>
                                    @error('video_url') <span class="hv-error">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Upload fields --}}
                            <div class="hv-source-panel" data-panel="upload">
                                <div class="hv-note">
                                    <i class="fas fa-circle-info"></i>
                                    {{ __('admin.home_video_upload_limit_note', ['mb' => $maxUploadMb]) }}
                                </div>

                                @if($video->video_path)
                                    <div class="hv-current-file">
                                        <span>
                                            <i class="fas fa-file-video"></i>
                                            {{ __('admin.home_video_file_current') }}:
                                            <a href="{{ $video->uploadUrl() }}" target="_blank" rel="noopener">{{ basename($video->video_path) }}</a>
                                        </span>
                                    </div>
                                @endif

                                <div class="hv-field">
                                    <label for="video_file">{{ __('admin.home_video_file') }}</label>
                                    <input type="file" name="video_file" id="video_file" accept="video/mp4,video/webm,video/quicktime">
                                    <small class="hv-hint">{{ __('admin.home_video_file_hint', ['mb' => $maxUploadMb]) }}</small>
                                    @error('video_file') <span class="hv-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ============ POSTER ============ --}}
                        <div class="hv-card">
                            <h3>{{ __('admin.home_video_poster') }}</h3>

                            <div class="hv-poster-row">
                                <div class="hv-poster-preview">
                                    @if($video->displayPosterUrl())
                                        <img src="{{ $video->displayPosterUrl() }}" alt="">
                                    @else
                                        <div class="hv-poster-placeholder"><i class="fas fa-image"></i></div>
                                    @endif
                                </div>

                                <div class="hv-field" style="flex:1; margin-bottom:0;">
                                    <label for="poster">{{ __('admin.home_video_poster') }}</label>
                                    <input type="file" name="poster" id="poster" accept="image/*">
                                    <small class="hv-hint">{{ __('admin.home_video_poster_hint') }}</small>
                                    <small class="hv-hint">{{ __('admin.home_video_poster_auto') }}</small>
                                    @error('poster') <span class="hv-error">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ============ COPY ============ --}}
                        <div class="hv-card">
                            <h3>{{ __('admin.home_video_copy') }}</h3>
                            <p class="hv-card-hint">{{ __('admin.home_video_copy_hint') }}</p>

                            @foreach($locales as $code => $label)
                                <div class="hv-locale-block">
                                    <span class="hv-locale-tag">{{ $label }}</span>

                                    <div class="hv-field">
                                        <label for="title_{{ $code }}">{{ __('admin.home_video_title_field', ['lang' => $label]) }}</label>
                                        <input type="text" name="title_{{ $code }}" id="title_{{ $code }}" maxlength="255"
                                               dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                                               value="{{ old('title_' . $code, $video->{'title_' . $code}) }}">
                                        @error('title_' . $code) <span class="hv-error">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="hv-field">
                                        <label for="description_{{ $code }}">{{ __('admin.home_video_description_field', ['lang' => $label]) }}</label>
                                        <textarea name="description_{{ $code }}" id="description_{{ $code }}" rows="3" maxlength="2000"
                                                  dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}">{{ old('description_' . $code, $video->{'description_' . $code}) }}</textarea>
                                        @error('description_' . $code) <span class="hv-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ============ SIDEBAR ============ --}}
                    <div class="hv-side">
                        <div class="hv-card">
                            <h3>{{ __('admin.listing_status') }}</h3>

                            <label class="hv-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $video->is_active))>
                                <span>
                                    <strong>{{ __('admin.home_video_enable') }}</strong>
                                    <small>{{ __('admin.home_video_enable_hint') }}</small>
                                </span>
                            </label>

                            <button type="submit" class="admin-btn admin-btn-primary text-white hv-save">
                                <i class="fas fa-save"></i> {{ __('admin.save') }}
                            </button>
                        </div>

                        <div class="hv-card">
                            <h3>{{ __('admin.home_video_preview') }}</h3>

                            @if($video->isPlayable())
                                <div class="hv-preview-frame">
                                    @if($video->isUpload())
                                        <video src="{{ $video->uploadUrl() }}" controls preload="none"
                                               poster="{{ $video->displayPosterUrl() }}"></video>
                                    @else
                                        {{-- src is built from a parsed video id, never the raw admin input --}}
                                        <iframe src="{{ $video->embedUrl() }}" title="{{ __('admin.home_video_preview') }}"
                                                loading="lazy" allowfullscreen
                                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                    @endif
                                </div>
                            @else
                                <p class="hv-preview-empty">{{ __('admin.home_video_preview_none') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </form>

            {{-- File removal lives outside the main form: a nested form is
                 invalid HTML, and these are immediate actions, not edits. --}}
            @if($video->video_path || $video->poster_path)
                <div class="hv-danger">
                    @if($video->video_path)
                        <form action="{{ route('admin.home_video.destroy_file') }}" method="POST"
                              onsubmit="return confirm('{{ __('admin.home_video_confirm_remove_file') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-danger">
                                <i class="fas fa-trash"></i> {{ __('admin.home_video_remove') }}: {{ __('admin.home_video_file') }}
                            </button>
                        </form>
                    @endif

                    @if($video->poster_path)
                        <form action="{{ route('admin.home_video.destroy_poster') }}" method="POST"
                              onsubmit="return confirm('{{ __('admin.home_video_confirm_remove_poster') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-danger">
                                <i class="fas fa-trash"></i> {{ __('admin.home_video_remove') }}: {{ __('admin.home_video_poster') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <style>
        .hv-status {
            display: flex; align-items: center; gap: .6rem; padding: .9rem 1.25rem; margin-bottom: 1.25rem;
            border-radius: 14px; font-size: .9rem; font-weight: 600;
        }
        .hv-status.is-live { background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; }
        .hv-status.is-warn { background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.35); color: #b45309; }
        .hv-status.is-off  { background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; }

        .hv-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; align-items: start; }
        @media (max-width: 992px) { .hv-grid { grid-template-columns: 1fr; } }
        .hv-main, .hv-side { display: grid; gap: 1.25rem; }

        .hv-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .hv-card h3 { font-size: 1rem; font-weight: 800; color: #1e293b; margin: 0 0 1rem; }
        .hv-card-hint { font-size: .82rem; color: #64748b; margin: -.5rem 0 1.25rem; }

        .hv-field { margin-bottom: 1rem; }
        .hv-field label { display: block; margin-bottom: .35rem; font-size: .82rem; font-weight: 700; color: #475569; }
        .hv-field input[type="text"], .hv-field input[type="url"], .hv-field textarea {
            width: 100%; padding: .6rem .85rem; border: 1px solid #cbd5e1; border-radius: 12px;
            font-size: .9375rem; color: #0f172a; background: #fff; font-family: inherit;
        }
        .hv-field input[type="file"] { width: 100%; font-size: .875rem; }
        .hv-field textarea { resize: vertical; }
        .hv-hint { display: block; margin-top: .35rem; font-size: .78rem; color: #64748b; }
        .hv-error { display: block; margin-top: .3rem; font-size: .8rem; color: #dc2626; }

        .hv-source-option {
            display: flex; align-items: flex-start; gap: .7rem; padding: .9rem 1rem; margin-bottom: .6rem;
            border: 2px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: border-color .2s, background .2s;
        }
        .hv-source-option.is-selected { border-color: #2563eb; background: #f8faff; }
        .hv-source-option input { margin-top: .2rem; width: 17px; height: 17px; flex-shrink: 0; accent-color: #2563eb; }
        .hv-source-option strong { display: block; font-size: .9rem; color: #0f172a; }
        .hv-source-option strong i { margin-inline-end: .35rem; color: #2563eb; }
        .hv-source-option small { display: block; font-size: .78rem; color: #64748b; margin-top: .2rem; }

        .hv-source-panel { display: none; padding-top: 1rem; margin-top: .5rem; border-top: 1px dashed #e2e8f0; }
        .hv-source-panel.is-visible { display: block; }

        .hv-note {
            display: flex; gap: .5rem; align-items: flex-start; padding: .75rem 1rem; margin-bottom: 1rem;
            background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; font-size: .82rem; color: #1e40af;
        }
        .hv-note i { margin-top: .15rem; }

        .hv-current-file { font-size: .85rem; color: #334155; margin-bottom: .85rem; }
        .hv-current-file a { color: #2563eb; font-weight: 600; }

        .hv-poster-row { display: flex; gap: 1.25rem; align-items: flex-start; }
        .hv-poster-preview {
            width: 160px; aspect-ratio: 16 / 9; border-radius: 12px; overflow: hidden; flex-shrink: 0;
            background: #f1f5f9; border: 1px solid #e2e8f0;
        }
        .hv-poster-preview img { width: 100%; height: 100%; object-fit: cover; }
        .hv-poster-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.5rem; }
        @media (max-width: 640px) { .hv-poster-row { flex-direction: column; } .hv-poster-preview { width: 100%; } }

        .hv-locale-block { padding: 1rem; margin-bottom: .85rem; border: 1px solid #e2e8f0; border-radius: 12px; background: #fcfdff; }
        .hv-locale-tag {
            display: inline-block; margin-bottom: .75rem; padding: .2rem .65rem; border-radius: 99px;
            background: #eef2ff; color: #4338ca; font-size: .72rem; font-weight: 800; letter-spacing: .04em;
        }
        .hv-locale-block .hv-field:last-child { margin-bottom: 0; }

        .hv-switch { display: flex; align-items: flex-start; gap: .7rem; padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px dashed #e2e8f0; cursor: pointer; }
        .hv-switch input[type="checkbox"] { width: 18px; height: 18px; margin-top: .15rem; flex-shrink: 0; accent-color: #2563eb; }
        .hv-switch strong { display: block; font-size: .9rem; color: #0f172a; }
        .hv-switch small { display: block; font-size: .78rem; color: #64748b; margin-top: .2rem; }
        .hv-save { width: 100%; justify-content: center; }

        .hv-preview-frame { position: relative; aspect-ratio: 16 / 9; border-radius: 12px; overflow: hidden; background: #0f172a; }
        .hv-preview-frame iframe, .hv-preview-frame video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
        .hv-preview-empty { font-size: .85rem; color: #94a3b8; margin: 0; }

        .hv-danger {
            display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.5rem; padding: 1.25rem 1.5rem;
            background: #fff; border: 1px solid #fecaca; border-inline-start: 5px solid #ef4444; border-radius: 16px;
        }
        .admin-btn-danger { background: #dc2626; color: #fff; border: none; }
        .admin-btn-danger:hover { background: #b91c1c; color: #fff; }
    </style>
@endsection

@push('scripts')
    <script>
        // Only the fields for the chosen source are shown. Both sets stay in
        // the DOM so switching back and forth does not lose what was typed.
        document.addEventListener('DOMContentLoaded', function () {
            const radios = document.querySelectorAll('input[name="source_type"]');
            const panels = document.querySelectorAll('[data-panel]');
            const options = document.querySelectorAll('.hv-source-option');
            if (!radios.length) return;

            function sync() {
                const selected = document.querySelector('input[name="source_type"]:checked');
                const value = selected ? selected.value : 'embed';

                panels.forEach(p => p.classList.toggle('is-visible', p.dataset.panel === value));
                options.forEach(o => o.classList.toggle('is-selected', o.dataset.source === value));
            }

            radios.forEach(r => r.addEventListener('change', sync));
            sync();
        });
    </script>
@endpush
