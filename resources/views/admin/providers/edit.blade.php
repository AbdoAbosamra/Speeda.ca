@extends('layouts.app')

@php
    $displayName = $provider->company_name ?: ($provider->user->name ?? 'Provider #'.$provider->id);
    $selectedLanguages = collect(old('languages', $provider->languages ?? []))->map(fn ($v) => (string) $v)->all();
    $selectedAreas = collect(old('service_areas', $serviceAreaIds))->map(fn ($v) => (int) $v)->all();
    $servicesValue = old(
        'services_offered',
        is_array($provider->services_offered) ? implode(', ', $provider->services_offered) : ($provider->services_offered ?? '')
    );
    $completion = (int) $provider->profile_completion_percent;

    // Never advertise a limit PHP will not honour. A form that accepts a 10 MB
    // image on a server capped at 2 MB sends the upload straight into a failure
    // that says nothing useful, so the smaller of the two is what the field
    // shows and what the browser enforces.
    $galleryMaxMb = \App\Support\UploadLimits::effectiveMb(10);
    $fileMaxMb = \App\Support\UploadLimits::effectiveMb(5);
    $requestMaxMb = \App\Support\UploadLimits::phpRequestMb();
@endphp

@section('title', __('admin.edit_provider').': '.$displayName)

@section('content')
    <div class="apx" dir="ltr">
        <div class="apx-shell">
            <x-admin.header
                eyebrow="Providers"
                :title="__('admin.edit_provider')"
                :subtitle="$displayName"
            >
                <x-slot:actions>
                    <x-ui.button :href="route('service-providers.show', $provider->id)" variant="secondary" icon="fas fa-arrow-up-right-from-square" class="admin-btn admin-btn-secondary">
                        {{ __('admin.view_public_profile') }}
                    </x-ui.button>
                    <x-ui.button :href="route('admin.provider_activity_monitor.show', $provider->id)" variant="secondary" icon="fas fa-chart-line" class="admin-btn admin-btn-secondary">
                        {{ __('admin.analytics_label') }}
                    </x-ui.button>
                    <x-ui.button :href="route('admin.provider_activity_monitor.index')" variant="secondary" icon="fas fa-arrow-left" class="admin-btn admin-btn-secondary">
                        {{ __('admin.back_to_list') }}
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            {{-- Identity strip --}}
            <section class="apx-hero">
                <div class="apx-hero-photo">
                    @if($provider->profile_image_url)
                        <img src="{{ $provider->profile_image_url }}" alt="">
                    @else
                        <span><i class="fas fa-user"></i></span>
                    @endif
                </div>
                <div class="apx-hero-copy">
                    <p class="apx-kicker">{{ __('admin.owner_account') }}</p>
                    <h2>{{ $displayName }}</h2>
                    <p class="apx-hero-meta">
                        <span>{{ $provider->user->name ?? '—' }}</span>
                        @if($provider->user?->email)
                            <span>·</span>
                            <span>{{ $provider->user->email }}</span>
                        @endif
                    </p>
                    <div class="apx-pills">
                        @if($provider->is_active)
                            <span class="apx-pill apx-pill-ok"><i class="fas fa-eye"></i> {{ __('admin.listing_visible') }}</span>
                        @else
                            <span class="apx-pill apx-pill-bad"><i class="fas fa-eye-slash"></i> {{ __('admin.listing_hidden_short') }}</span>
                        @endif
                        @if($provider->user?->is_active)
                            <span class="apx-pill apx-pill-ok"><i class="fas fa-circle-check"></i> {{ __('admin.account_active') }}</span>
                        @else
                            <span class="apx-pill apx-pill-bad"><i class="fas fa-circle-xmark"></i> {{ __('admin.account_inactive') }}</span>
                        @endif
                        @if($provider->is_certified)
                            <span class="apx-pill apx-pill-gold"><i class="fas fa-certificate"></i> {{ __('admin.certified_badge') }}</span>
                        @endif
                        @if($provider->user)
                            <a href="{{ route('admin.users.edit', $provider->user) }}" class="apx-pill apx-pill-link">
                                {{ __('admin.manage_account') }} <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="apx-hero-stats" aria-label="{{ __('admin.profile_completion') }}">
                    <div class="apx-ring" style="--p: {{ max(0, min(100, $completion)) }}">
                        <strong>{{ $completion }}%</strong>
                        <span>{{ __('admin.profile_completion') }}</span>
                    </div>
                    <dl>
                        <div><dt>{{ __('admin.rating') }}</dt><dd>{{ number_format((float) ($provider->calculated_rating ?? 0), 2) }}</dd></div>
                        <div><dt>{{ __('admin.views') }}</dt><dd>{{ number_format((int) $provider->views) }}</dd></div>
                        <div><dt>{{ __('admin.created_at') }}</dt><dd>{{ optional($provider->created_at)->format('M d, Y') ?: '—' }}</dd></div>
                    </dl>
                </div>
            </section>

            @if(!$provider->user?->is_active)
                <div class="apx-banner apx-banner-warn">
                    <i class="fas fa-triangle-exclamation"></i>
                    <p>{{ __('admin.provider_hidden_by_account') }}</p>
                </div>
            @endif

            <form action="{{ route('admin.providers.update', $provider) }}" method="POST" enctype="multipart/form-data" class="apx-layout" id="apx-form">
                @csrf
                @method('PATCH')

                <aside class="apx-nav" aria-label="Sections">
                    <a href="#apx-basics">{{ __('admin.provider_section_basics') }}</a>
                    <a href="#apx-offerings">{{ __('admin.provider_section_offerings') }}</a>
                    <a href="#apx-coverage">{{ __('admin.provider_section_coverage') }}</a>
                    <a href="#apx-gallery">{{ __('admin.provider_section_gallery') }}</a>
                    <a href="#apx-consent">{{ __('admin.provider_section_consent_proof') }}</a>
                    <a href="#apx-photo">{{ __('admin.provider_section_photo') }}</a>
                    <a href="#apx-status">{{ __('admin.listing_status') }}</a>
                </aside>

                <div class="apx-main">
                    <section class="apx-panel" id="apx-basics">
                        <header>
                            <h3>{{ __('admin.provider_section_basics') }}</h3>
                            <p>{{ __('admin.provider_section_basics_hint') }}</p>
                        </header>

                        <div class="apx-field">
                            <label for="company_name">{{ __('admin.company_name') }} *</label>
                            <input type="text" name="company_name" id="company_name" maxlength="255" required
                                   value="{{ old('company_name', $provider->company_name) }}">
                            @error('company_name') <span class="apx-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="apx-grid-2">
                            <div class="apx-field">
                                <label for="category_id">{{ __('admin.category') }} <em>{{ __('admin.admin_only_field') }}</em></label>
                                <select name="category_id" id="category_id">
                                    <option value="">—</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}"
                                            @selected((string) old('category_id', $provider->category_id) === (string) $category->id)>
                                            {{ $category->localized_name ?? $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="apx-hint">{{ __('admin.category_admin_hint') }}</small>
                                @error('category_id') <span class="apx-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="apx-field">
                                <label for="location_id">{{ __('admin.location') }}</label>
                                <select name="location_id" id="location_id">
                                    <option value="">—</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}"
                                            @selected((string) old('location_id', $provider->location_id) === (string) $location->id)>
                                            {{ $location->city }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id') <span class="apx-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="apx-grid-3">
                            <div class="apx-field">
                                <label for="phone">{{ __('admin.phone') }}</label>
                                <input type="text" name="phone" id="phone" maxlength="30" value="{{ old('phone', $provider->phone) }}">
                                @error('phone') <span class="apx-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="apx-field">
                                <label for="whatsapp_number">{{ __('admin.whatsapp_number') }}</label>
                                <input type="text" name="whatsapp_number" id="whatsapp_number" maxlength="30" value="{{ old('whatsapp_number', $provider->whatsapp_number) }}">
                                @error('whatsapp_number') <span class="apx-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="apx-field">
                                <label for="experience_years">{{ __('admin.experience_years') }}</label>
                                <input type="number" name="experience_years" id="experience_years" min="0" max="80" value="{{ old('experience_years', $provider->experience_years) }}">
                                @error('experience_years') <span class="apx-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="apx-field">
                            <label for="address">{{ __('admin.address') }}</label>
                            <input type="text" name="address" id="address" maxlength="500" value="{{ old('address', $provider->address) }}">
                            @error('address') <span class="apx-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="apx-field">
                            <label for="bio">{{ __('admin.bio') }}</label>
                            <textarea name="bio" id="bio" rows="6" maxlength="5000">{{ old('bio', $provider->bio) }}</textarea>
                            @error('bio') <span class="apx-error">{{ $message }}</span> @enderror
                        </div>
                    </section>

                    <section class="apx-panel" id="apx-offerings">
                        <header>
                            <h3>{{ __('admin.provider_section_offerings') }}</h3>
                            <p>{{ __('admin.provider_section_offerings_hint') }}</p>
                        </header>

                        <div class="apx-field">
                            <label for="services_offered">{{ __('admin.services_offered') }}</label>
                            <input type="text" name="services_offered" id="services_offered" maxlength="1000"
                                   value="{{ $servicesValue }}"
                                   placeholder="{{ __('admin.services_offered_placeholder') }}">
                            <small class="apx-hint">{{ __('admin.services_offered_hint') }}</small>
                            @error('services_offered') <span class="apx-error">{{ $message }}</span> @enderror
                        </div>

                        <fieldset class="apx-fieldset">
                            <legend>{{ __('admin.languages_spoken') }}</legend>
                            <div class="apx-chip-grid">
                                @foreach($languageOptions as $code => $label)
                                    @php $checked = in_array($code, $selectedLanguages, true); @endphp
                                    <label class="apx-chip {{ $checked ? 'is-on' : '' }}">
                                        <input type="checkbox" name="languages[]" value="{{ $code }}" @checked($checked)
                                               onchange="this.closest('.apx-chip').classList.toggle('is-on', this.checked)">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('languages') <span class="apx-error">{{ $message }}</span> @enderror
                            @error('languages.*') <span class="apx-error">{{ $message }}</span> @enderror
                        </fieldset>
                    </section>

                    <section class="apx-panel" id="apx-coverage">
                        <header>
                            <h3>{{ __('admin.provider_section_coverage') }}</h3>
                            <p>{{ __('admin.provider_section_coverage_hint') }}</p>
                        </header>

                        <input type="hidden" name="has_service_areas" value="1">
                        <div class="apx-chip-grid apx-chip-grid-dense">
                            @forelse($filterableLocations as $loc)
                                @php
                                    $isPrimary = (int) old('location_id', $provider->location_id) === (int) $loc->id;
                                    $checked = in_array((int) $loc->id, $selectedAreas, true);
                                @endphp
                                @if($isPrimary)
                                    <span class="apx-chip is-on is-locked">
                                        <span>{{ $loc->localized_name ?? $loc->city }}</span>
                                        <em>{{ __('admin.primary_location_badge') }}</em>
                                    </span>
                                @else
                                    <label class="apx-chip {{ $checked ? 'is-on' : '' }}">
                                        <input type="checkbox" name="service_areas[]" value="{{ $loc->id }}"
                                               @checked($checked)
                                               onchange="this.closest('.apx-chip').classList.toggle('is-on', this.checked)">
                                        <span>{{ $loc->localized_name ?? $loc->city }}</span>
                                    </label>
                                @endif
                            @empty
                                <p class="apx-empty">{{ __('admin.no_filterable_locations') }}</p>
                            @endforelse
                        </div>
                        @error('service_areas') <span class="apx-error">{{ $message }}</span> @enderror
                        @error('service_areas.*') <span class="apx-error">{{ $message }}</span> @enderror
                    </section>

                    <section class="apx-panel" id="apx-gallery">
                        <header>
                            <h3>{{ __('admin.provider_section_gallery') }}</h3>
                            <p>{{ __('admin.provider_section_gallery_hint', ['max' => $galleryMax, 'size' => $galleryMaxMb]) }}</p>
                        </header>

                        <div class="apx-gallery" data-max="{{ $galleryMax }}">
                            @foreach($galleryMedia as $media)
                                @php
                                    $thumb = method_exists($provider, 'getMediaPublicUrl')
                                        ? ($provider->getMediaPublicUrl($media, $media->hasGeneratedConversion('gallery_thumb') ? 'gallery_thumb' : null) ?? $media->getUrl())
                                        : $media->getUrl();
                                @endphp
                                <figure class="apx-gallery-item" data-media-id="{{ $media->id }}">
                                    <img src="{{ $thumb }}" alt="">
                                    <label class="apx-gallery-remove">
                                        <input type="checkbox" name="remove_gallery_ids[]" value="{{ $media->id }}">
                                        <span><i class="fas fa-trash"></i> {{ __('admin.remove') }}</span>
                                    </label>
                                </figure>
                            @endforeach

                            <label class="apx-gallery-add">
                                <input type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp" multiple data-max-mb="{{ $galleryMaxMb }}">
                                <i class="fas fa-plus"></i>
                                <span>{{ __('admin.add_gallery_images') }}</span>
                            </label>
                        </div>
                        @error('gallery_images') <span class="apx-error">{{ $message }}</span> @enderror
                        @error('gallery_images.*') <span class="apx-error">{{ $message }}</span> @enderror
                    </section>

                    <section class="apx-panel" id="apx-consent">
                        <header>
                            <h3>{{ __('admin.provider_section_consent_proof') }}</h3>
                            <p>{{ __('admin.provider_section_consent_proof_hint') }}</p>
                        </header>

                        @if($provider->hasPhotoConsentProof())
                            <div class="apx-cert-current mb-3">
                                <a href="{{ Storage::url($provider->photo_consent_proof_path) }}" target="_blank" rel="noopener">
                                    <i class="fas fa-paperclip"></i> {{ __('admin.photo_consent_proof_view') }}
                                </a>
                                <label class="apx-inline-check">
                                    <input type="checkbox" name="remove_consent_proof" value="1">
                                    {{ __('admin.remove_consent_proof') }}
                                </label>
                            </div>
                        @endif

                        <div class="apx-field">
                            <label for="photo_consent_proof">{{ __('admin.upload_consent_proof') }}</label>
                            <input type="file" name="photo_consent_proof" id="photo_consent_proof" accept="image/*,.pdf" data-max-mb="{{ $fileMaxMb }}">
                            <small class="apx-hint">{{ __('admin.photo_consent_proof_hint') }}</small>
                            @error('photo_consent_proof') <span class="apx-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="apx-consent mt-3">
                            <span class="apx-consent-label">{{ __('admin.photo_consent_status_title') }}</span>
                            @if(!$provider->photoWasUploadedByAdmin())
                                <p>{{ __('admin.photo_consent_status_none') }}</p>
                            @else
                                <p class="is-ok">
                                    <i class="fas fa-circle-check"></i>
                                    {{ __('admin.photo_consent_status_uploaded', [
                                        'admin' => $provider->photoConsentAdmin->name ?? '—',
                                        'date' => $provider->photo_uploaded_by_admin_at?->format('M d, Y'),
                                    ]) }}
                                </p>
                            @endif
                        </div>
                    </section>

                    <section class="apx-panel" id="apx-photo">
                        <header>
                            <h3>{{ __('admin.provider_photo') }}</h3>
                            <p>{{ __('admin.photo_consent_hidden_note') }}</p>
                        </header>

                        <div class="apx-photo-row">
                            <div class="apx-photo-preview">
                                @if($provider->profile_image_url)
                                    <img src="{{ $provider->profile_image_url }}" alt="">
                                @else
                                    <div class="apx-photo-placeholder"><i class="fas fa-user"></i></div>
                                @endif
                            </div>
                            <div class="apx-field" style="flex:1;margin:0;">
                                <label for="profile_image">{{ __('admin.provider_photo_replace') }}</label>
                                <input type="file" name="profile_image" id="profile_image" accept="image/*" data-max-mb="{{ $fileMaxMb }}">
                                <small class="apx-hint">{{ __('admin.provider_photo_hint', ['max' => $fileMaxMb]) }}</small>
                                @error('profile_image') <span class="apx-error">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="apx-panel" id="apx-status">
                        <header>
                            <h3>{{ __('admin.listing_status') }}</h3>
                            <p>{{ __('admin.listing_visible_hint') }}</p>
                        </header>

                        <label class="apx-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $provider->is_active))>
                            <span>
                                <strong>{{ __('admin.listing_visible') }}</strong>
                                <small>{{ __('admin.listing_visible_hint') }}</small>
                            </span>
                        </label>
                    </section>
                </div>

                <div class="apx-savebar">
                    <p>{{ __('admin.provider_save_note') }}</p>
                    <button type="submit" class="admin-btn admin-btn-primary text-white">
                        <i class="fas fa-save"></i> {{ __('admin.save') }}
                    </button>
                </div>
            </form>

            <div class="apx-danger">
                <div>
                    <h4>{{ __('admin.delete_provider') }}</h4>
                    <p>{{ __('admin.delete_provider_warning') }}</p>
                </div>
                <form action="{{ route('admin.providers.destroy', $provider) }}" method="POST"
                      onsubmit="return confirm(@js(__('admin.confirm_delete_provider')));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn admin-btn-danger">
                        <i class="fas fa-trash"></i> {{ __('admin.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        .apx {
            --apx-ink: #122033;
            --apx-muted: #5b6b7c;
            --apx-line: rgba(18, 32, 51, 0.1);
            --apx-surface: #ffffff;
            --apx-canvas: #f4f7fb;
            --apx-accent: #0f766e;
            --apx-accent-soft: rgba(15, 118, 110, 0.1);
            --apx-gold: #b45309;
            --apx-radius: .5rem;
            background: var(--apx-canvas);
            min-height: 100%;
            color: var(--apx-ink);
        }
        .apx-shell { width: min(1180px, calc(100% - 2rem)); margin: 0 auto; padding: 1.25rem 0 3rem; }
        .apx-hero {
            display: grid; grid-template-columns: auto 1fr auto; gap: 1.25rem; align-items: center;
            padding: 1.35rem 1.5rem; margin: 1rem 0 1.25rem; border-radius: .75rem;
            background: linear-gradient(135deg, #163447 0%, #0f766e 58%, #134e4a 100%);
            color: #f8fafc; box-shadow: 0 24px 50px rgba(15, 42, 48, 0.18);
        }
        @media (max-width: 900px) { .apx-hero { grid-template-columns: 1fr; } }
        .apx-hero-photo {
            width: 88px; height: 88px; border-radius: 1.1rem; overflow: hidden; background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2); display: grid; place-items: center; font-size: 1.8rem;
        }
        .apx-hero-photo img { width: 100%; height: 100%; object-fit: cover; }
        .apx-kicker { margin: 0 0 .35rem; font-size: .72rem; letter-spacing: .12em; text-transform: uppercase; opacity: .75; font-weight: 700; }
        .apx-hero-copy { min-width: 0; }
        .apx-hero-copy h2 { margin: 0; color: #f8fafc; font-size: clamp(1.35rem, 2vw, 1.9rem); font-weight: 800; letter-spacing: 0; overflow-wrap: anywhere; }
        .apx-hero-meta { margin: .35rem 0 .85rem; display: flex; flex-wrap: wrap; gap: .4rem; opacity: .85; font-size: .92rem; }
        .apx-pills { display: flex; flex-wrap: wrap; gap: .5rem; }
        .apx-pill {
            display: inline-flex; align-items: center; gap: .35rem; padding: .35rem .7rem; border-radius: 999px;
            font-size: .78rem; font-weight: 700; background: rgba(255,255,255,.12); color: #fff !important; text-decoration: none;
        }
        .apx-pill-ok { background: rgba(34, 197, 94, .22); }
        .apx-pill-bad { background: rgba(248, 113, 113, .22); }
        .apx-pill-gold { background: rgba(251, 191, 36, .22); color: #fef3c7; }
        .apx-pill-link:hover { background: rgba(255,255,255,.22); }
        .apx-hero-stats { display: flex; gap: 1rem; align-items: center; }
        .apx-ring {
            --p: 0; width: 84px; height: 84px; border-radius: 50%; display: grid; place-items: center; text-align: center;
            background: conic-gradient(#fbbf24 calc(var(--p) * 1%), rgba(255,255,255,.15) 0);
            position: relative;
        }
        .apx-ring::before {
            content: ""; position: absolute; inset: 7px; border-radius: 50%; background: #134e4a;
        }
        .apx-ring strong, .apx-ring span { position: relative; z-index: 1; display: block; }
        .apx-ring strong { font-size: 1.05rem; }
        .apx-ring span { font-size: .58rem; text-transform: uppercase; letter-spacing: .06em; opacity: .8; max-width: 54px; line-height: 1.15; }
        .apx-hero-stats dl { margin: 0; display: grid; gap: .35rem; min-width: 120px; }
        .apx-hero-stats dl div { display: flex; justify-content: space-between; gap: 1rem; font-size: .82rem; }
        .apx-hero-stats dt { opacity: .7; }
        .apx-hero-stats dd { margin: 0; font-weight: 700; }

        .apx-banner {
            display: flex; gap: .75rem; align-items: center; padding: .9rem 1.1rem; margin-bottom: 1rem;
            border-radius: 1rem; background: rgba(180, 83, 9, .1); border: 1px solid rgba(180, 83, 9, .28); color: #9a3412;
        }
        .apx-banner p { margin: 0; font-size: .92rem; }

        .apx-layout { display: grid; grid-template-columns: 190px minmax(0, 1fr); gap: 1.25rem; align-items: start; }
        .apx-nav {
            position: sticky; top: 5rem; display: grid; gap: .25rem; padding: .65rem;
            background: #ffffff; border: 1px solid var(--apx-line); border-radius: var(--apx-radius);
            box-shadow: 0 8px 24px rgba(18, 32, 51, 0.05);
        }
        .apx-nav a {
            text-decoration: none; color: var(--apx-muted); font-size: .86rem; font-weight: 700;
            padding: .6rem .7rem; border-radius: .4rem; border: 1px solid transparent;
        }
        .apx-nav a:hover { color: var(--apx-ink); background: rgba(255,255,255,.7); border-color: var(--apx-line); }

        .apx-main { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; min-width: 0; }
        #apx-basics,
        #apx-gallery,
        #apx-status { grid-column: 1 / -1; }
        .apx-panel {
            background: var(--apx-surface); border: 1px solid var(--apx-line); border-radius: var(--apx-radius);
            padding: 1.35rem 1.45rem 1.5rem; box-shadow: 0 10px 30px rgba(18, 32, 51, 0.04);
            scroll-margin-top: 5.5rem;
        }
        .apx-panel > header { margin-bottom: 1.15rem; padding-bottom: .9rem; border-bottom: 1px solid var(--apx-line); }
        .apx-panel > header h3 { margin: 0 0 .3rem; font-size: 1.05rem; font-weight: 800; letter-spacing: -0.01em; }
        .apx-panel > header p { margin: 0; color: var(--apx-muted); font-size: .9rem; }

        .apx-field { margin-bottom: 1rem; }
        .apx-field label { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-bottom: .4rem; font-size: .82rem; font-weight: 750; color: #334155; }
        .apx-field label em { font-style: normal; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--apx-accent); background: var(--apx-accent-soft); padding: .15rem .45rem; border-radius: 999px; }
        .apx-field input, .apx-field select, .apx-field textarea {
            width: 100%; padding: .7rem .9rem; border: 1px solid #d5dde7; border-radius: .9rem;
            background: #fff; color: var(--apx-ink); font-size: .95rem;
        }
        .apx-field input:focus, .apx-field select:focus, .apx-field textarea:focus {
            outline: 0; border-color: var(--apx-accent); box-shadow: 0 0 0 4px var(--apx-accent-soft);
        }
        .apx-field input[type="file"] { padding: .35rem; min-height: 2.8rem; }
        .apx-field input[type="file"]::file-selector-button {
            margin-inline-end: .7rem; padding: .45rem .7rem; border: 0; border-radius: .35rem;
            background: #e8f3f2; color: #115e59; font: inherit; font-weight: 700; cursor: pointer;
        }
        .apx-field textarea { resize: vertical; min-height: 140px; }
        .apx-grid-2, .apx-grid-3 { display: grid; gap: 1rem; }
        .apx-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .apx-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        @media (max-width: 760px) { .apx-grid-2, .apx-grid-3 { grid-template-columns: 1fr; } }
        .apx-hint { display: block; margin-top: .35rem; color: var(--apx-muted); font-size: .78rem; }
        .apx-error { display: block; margin-top: .35rem; color: #dc2626; font-size: .8rem; }
        .apx-empty { color: var(--apx-muted); margin: 0; }

        .apx-fieldset { border: 0; padding: 0; margin: 0 0 .5rem; }
        .apx-fieldset legend { font-size: .82rem; font-weight: 750; color: #334155; margin-bottom: .55rem; }
        .apx-chip-grid { display: flex; flex-wrap: wrap; gap: .55rem; }
        .apx-chip-grid-dense { max-height: 280px; overflow: auto; padding-right: .25rem; }
        .apx-chip {
            display: inline-flex; align-items: center; gap: .45rem; padding: .55rem .85rem; border-radius: 999px;
            border: 1px solid #d5dde7; background: #fff; cursor: pointer; font-weight: 700; font-size: .86rem; color: #334155;
            transition: .15s ease;
        }
        .apx-chip input { position: absolute; opacity: 0; pointer-events: none; }
        .apx-chip.is-on { background: var(--apx-accent-soft); border-color: rgba(15,118,110,.35); color: #115e59; }
        .apx-chip.is-locked { opacity: .85; cursor: default; }
        .apx-chip em { font-style: normal; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; opacity: .7; }

        .apx-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .85rem; }
        .apx-gallery-item, .apx-gallery-add {
            aspect-ratio: 1; border-radius: 1rem; overflow: hidden; border: 1px solid var(--apx-line); background: #f8fafc; position: relative;
        }
        .apx-gallery-item img { width: 100%; height: 100%; object-fit: cover; }
        .apx-gallery-remove {
            position: absolute; inset: auto .45rem .45rem .45rem; display: flex; justify-content: center;
            background: rgba(15, 23, 42, .78); color: #fff; border-radius: .7rem; padding: .4rem; cursor: pointer; font-size: .75rem; font-weight: 700;
        }
        .apx-gallery-remove input { accent-color: #f87171; }
        .apx-gallery-add {
            display: grid; place-items: center; text-align: center; gap: .35rem; cursor: pointer; color: var(--apx-muted);
            border-style: dashed; font-size: .82rem; font-weight: 700;
        }
        .apx-gallery-add input { display: none; }
        .apx-gallery-add:hover { color: var(--apx-accent); border-color: rgba(15,118,110,.4); background: var(--apx-accent-soft); }

        .apx-switch { display: flex; gap: .75rem; align-items: flex-start; padding: .85rem 0; cursor: pointer; }
        .apx-switch input[type="checkbox"] { width: 18px; height: 18px; margin-top: .2rem; flex: 0 0 auto; accent-color: var(--apx-accent); }
        .apx-switch strong { display: block; font-size: .95rem; }
        .apx-switch small { display: block; margin-top: .2rem; color: var(--apx-muted); font-size: .8rem; }

        .apx-cert-current { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; margin: .5rem 0 1rem; }
        .apx-cert-current a { color: var(--apx-accent); font-weight: 700; text-decoration: none; }
        .apx-inline-check { display: inline-flex; gap: .4rem; align-items: center; font-size: .85rem; color: #b91c1c; font-weight: 700; }

        .apx-photo-row { display: flex; gap: 1.1rem; align-items: flex-start; margin-bottom: 1rem; }
        .apx-photo-preview { width: 88px; height: 88px; border-radius: 1rem; overflow: hidden; background: #eef2f6; border: 1px solid var(--apx-line); flex: 0 0 auto; }
        .apx-photo-preview img { width: 100%; height: 100%; object-fit: cover; }
        .apx-photo-placeholder { width: 100%; height: 100%; display: grid; place-items: center; color: #94a3b8; font-size: 1.7rem; }
        .apx-consent { margin-top: .5rem; padding: .9rem 1rem; border-radius: 1rem; background: #f8fafc; border: 1px dashed #cbd5e1; }
        .apx-consent-label { display: block; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; margin-bottom: .35rem; }
        .apx-consent p { margin: 0 0 .35rem; font-size: .88rem; }
        .apx-consent .is-ok { color: #047857; }
        .apx-consent .is-muted { color: #94a3b8; font-style: italic; }
        .apx-link { color: #0f766e; font-weight: 700; text-decoration: none; }

        .apx-savebar {
            grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
            position: sticky; bottom: .75rem; z-index: 6; margin-top: .25rem; padding: .9rem 1.1rem;
            border-radius: 1rem; background: rgba(18, 32, 51, 0.92); color: #fff; backdrop-filter: blur(8px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
        }
        .apx-savebar p { margin: 0; font-size: .85rem; opacity: .85; }
        .apx-savebar .admin-btn { color: #fff !important; }
        .apx-danger {
            display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
            margin-top: 1.25rem; padding: 1.15rem 1.35rem; background: #fff; border: 1px solid #fecaca;
            border-inline-start: 5px solid #ef4444; border-radius: var(--apx-radius);
        }
        .apx-danger h4 { margin: 0 0 .25rem; color: #b91c1c; font-size: 1rem; font-weight: 800; }
        .apx-danger p { margin: 0; color: var(--apx-muted); font-size: .85rem; }
        .admin-btn-danger { background: #dc2626; color: #fff; border: none; }
        .admin-btn-danger:hover { background: #b91c1c; color: #fff; }

        @media (max-width: 1050px) {
            .apx-main { grid-template-columns: 1fr; }
            .apx-main > .apx-panel { grid-column: 1; }
        }

        @media (max-width: 900px) {
            .apx-shell { width: min(100% - 1rem, 760px); }
            .apx-layout { grid-template-columns: 1fr; }
            .apx-nav {
                position: static; display: flex; overflow-x: auto; gap: .35rem; padding: .5rem;
                scrollbar-width: thin;
            }
            .apx-nav a { white-space: nowrap; flex: 0 0 auto; }
        }

        @media (max-width: 600px) {
            .apx-hero { padding: 1rem; }
            .apx-hero-stats { align-items: flex-start; flex-wrap: wrap; }
            .apx-panel { padding: 1rem; }
            .apx-photo-row { flex-direction: column; }
            .apx-savebar { align-items: stretch; bottom: .35rem; }
            .apx-savebar .admin-btn { width: 100%; justify-content: center; }
            .apx-danger { align-items: stretch; }
            .apx-danger .admin-btn { width: 100%; justify-content: center; }
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.apx-gallery-item').forEach((item) => {
                const box = item.querySelector('input[type="checkbox"]');
                if (!box) return;
                box.addEventListener('change', () => {
                    item.style.opacity = box.checked ? '0.45' : '1';
                });
            });

            // Two separate ceilings can reject an upload, and PHP enforces both
            // before the application sees the request — so the browser has to
            // check both or the person gets an error with no cause attached.
            // Per file: upload_max_filesize. All files together: post_max_size,
            // which discards the ENTIRE request when exceeded, losing every
            // text field on the form along with it.
            const form = document.getElementById('apx-form');
            if (!form) return;
            const requestMaxMb = @js($requestMaxMb);
            const locale = @js(app()->getLocale());

            const text = {
                ar: {
                    tooBig: (name, max) => `الملف "${name}" حجمه أكبر من الحد المسموح (${max} م.ب) — اختر ملفاً أصغر.`,
                    tooMuch: (total, max) => `إجمالي الملفات ${total} م.ب، والسيرفر بيقبل ${max} م.ب في المرة الواحدة — ارفعهم على دفعات.`,
                },
                fr: {
                    tooBig: (name, max) => `Le fichier « ${name} » dépasse la limite de ${max} Mo — choisissez un fichier plus petit.`,
                    tooMuch: (total, max) => `Les fichiers totalisent ${total} Mo alors que le serveur en accepte ${max} à la fois — envoyez-les en plusieurs fois.`,
                },
            };
            const t = text[locale] || {
                tooBig: (name, max) => `File "${name}" exceeds the ${max} MB limit — please choose a smaller file.`,
                tooMuch: (total, max) => `The files total ${total} MB but this server accepts ${max} MB per submit — upload them in smaller batches.`,
            };

            let banner = document.getElementById('apx-upload-errors');
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'apx-upload-errors';
                banner.className = 'apx-banner';
                banner.style.cssText = 'background:rgba(220,38,38,.08);border-color:rgba(220,38,38,.35);color:#b91c1c;margin-bottom:1rem;';
                banner.hidden = true;
                form.prepend(banner);
            }

            function problems() {
                const found = [];
                let total = 0;

                form.querySelectorAll('input[type="file"][data-max-mb]').forEach((input) => {
                    const maxMb = parseFloat(input.dataset.maxMb);
                    Array.from(input.files || []).forEach((file) => {
                        total += file.size;
                        if (file.size > maxMb * 1024 * 1024) {
                            found.push(t.tooBig(file.name, maxMb));
                        }
                    });
                });

                // Only worth flagging once nothing is individually oversized —
                // otherwise the same files get reported twice.
                if (found.length === 0 && requestMaxMb > 0 && total > requestMaxMb * 1024 * 1024 * 0.95) {
                    found.push(t.tooMuch((total / 1048576).toFixed(1), requestMaxMb));
                }

                return found;
            }

            function showErrors(found) {
                banner.innerHTML = '';
                found.forEach((message) => {
                    const p = document.createElement('p');
                    p.style.margin = '.25rem 0';
                    p.textContent = message;
                    banner.appendChild(p);
                });
                banner.hidden = found.length === 0;
            }

            form.querySelectorAll('input[type="file"][data-max-mb]').forEach((input) => {
                input.addEventListener('change', () => showErrors(problems()));
            });

            form.addEventListener('submit', (e) => {
                const found = problems();
                if (found.length > 0) {
                    e.preventDefault();
                    showErrors(found);
                    banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    </script>
@endpush
