@extends('layouts.app')

@section('title', $offer->exists ? 'Edit offer' : 'New offer')

@section('content')
@php
    $locales = ['en' => 'English', 'ar' => 'العربية', 'fr' => 'Français'];
    $val = fn (string $field) => old($field, $offer->{$field});
    $dateVal = fn (string $field) => old($field, $offer->{$field}?->format('Y-m-d'));

    // Everything the live preview needs, computed with the same translations
    // the public card uses, so the preview cannot drift from the real thing.
    $badgeTemplates = [];
    foreach (array_keys($locales) as $l) {
        $badgeTemplates[$l] = [
            'percent' => __('offers.badge_percent', ['value' => '{v}'], $l),
            'fixed' => __('offers.badge_fixed', ['value' => '{v}'], $l),
            'free' => __('offers.badge_free', [], $l),
            'special' => __('offers.badge_special', [], $l),
            'tag' => __('offers.exclusive_tag', [], $l),
            'view' => __('offers.view_offer', [], $l),
            'valid' => __('offers.valid_until', ['date' => '{d}'], $l),
            'ongoing' => __('offers.no_end_date', [], $l),
        ];
    }
    $config = [
        'providers' => $providers,
        'badges' => $badgeTemplates,
        'state' => [
            'provider' => (string) $val('service_provider_id'),
            'type' => $val('deal_type'),
            'value' => (string) ($val('deal_value') !== null ? rtrim(rtrim(number_format((float) $val('deal_value'), 2, '.', ''), '0'), '.') : ''),
            'badge' => ['en' => $val('badge_text_en'), 'ar' => $val('badge_text_ar'), 'fr' => $val('badge_text_fr')],
            'title' => ['en' => $val('title_en'), 'ar' => $val('title_ar'), 'fr' => $val('title_fr')],
            'summary' => ['en' => $val('summary_en'), 'ar' => $val('summary_ar'), 'fr' => $val('summary_fr')],
            'starts' => $dateVal('starts_at'),
            'ends' => $dateVal('ends_at'),
            'active' => (bool) old('is_active', $offer->is_active),
            'featured' => (bool) old('is_featured', $offer->is_featured),
            'cta' => $val('cta_type'),
            'image' => $offer->image_url,
            'today' => now()->toDateString(),
        ],
    ];
    $firstErrorLocale = collect(array_keys($locales))->first(fn ($l) => $errors->hasAny(["title_$l", "summary_$l", "details_$l", "terms_$l", "badge_text_$l"])) ?? 'en';
@endphp

<div class="admin-cms-page ao-page" dir="ltr">
    <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
        <x-admin.header
            eyebrow="Local Offers"
            :title="$offer->exists ? 'Edit offer' : 'New offer'"
            :subtitle="$offer->exists ? $offer->title_en . ' · ' . $offer->providerName() : 'Pick the provider, write the deal, choose the dates. The preview on the right shows exactly what visitors will see.'"
        >
            <x-slot:actions>
                <a href="{{ route('admin.offers.index') }}" class="admin-btn admin-btn-secondary"><i class="fas fa-arrow-left"></i><span>All offers</span></a>
                @if($offer->exists)
                    <a href="{{ route('offers.show', $offer->slug) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary"><i class="fas fa-arrow-up-right-from-square"></i><span>Open page</span></a>
                    <a href="{{ route('admin.offers.analytics', ['offer_id' => $offer->id, 'range' => '90']) }}" class="admin-btn admin-btn-secondary"><i class="fas fa-chart-line"></i><span>Statistics</span></a>
                @endif
            </x-slot:actions>
        </x-admin.header>

        @if($errors->any())
            <div class="ao-status ao-status--empty"><i class="fas fa-triangle-exclamation"></i><span>Please fix the highlighted fields ({{ $errors->count() }}).</span></div>
        @endif

        <form method="POST" enctype="multipart/form-data"
              action="{{ $offer->exists ? route('admin.offers.update', $offer) : route('admin.offers.store') }}"
              x-data="offerForm(@js($config))" class="ao-editor" novalidate>
            @csrf
            @if($offer->exists) @method('PUT') @endif

            <div class="ao-editor-main">
                {{-- ============ 1. PROVIDER ============ --}}
                <section class="ao-card ao-sec">
                    <h3><span>1</span> Provider</h3>
                    <div class="ao-field">
                        <label for="provider_search">Service provider</label>
                        <input type="search" id="provider_search" class="form-control" placeholder="Type to filter providers…" x-model="providerQuery" autocomplete="off">
                        <select name="service_provider_id" id="service_provider_id" class="form-select" size="6" x-model="s.provider" required>
                            <template x-for="p in filteredProviders()" :key="p.id">
                                <option :value="String(p.id)" :selected="String(p.id) === String(s.provider)" x-text="p.name + (p.category ? ' — ' + p.category : '') + (p.city ? ', ' + p.city : '') + (p.visible ? '' : '  (profile hidden)')"></option>
                            </template>
                        </select>
                        @error('service_provider_id')<span class="ao-error">{{ $message }}</span>@enderror
                        <template x-if="provider() && !provider().has_whatsapp">
                            <span class="ao-inline-warn"><i class="fas fa-triangle-exclamation"></i> This provider has no WhatsApp number. The button will send visitors to the profile instead.</span>
                        </template>
                        <template x-if="provider() && !provider().visible">
                            <span class="ao-inline-warn"><i class="fas fa-eye-slash"></i> This provider's profile is not public, so the offer stays hidden until the profile is active.</span>
                        </template>
                    </div>
                </section>

                {{-- ============ 2. DEAL ============ --}}
                <section class="ao-card ao-sec">
                    <h3><span>2</span> The deal</h3>
                    <div class="ao-types" role="radiogroup" aria-label="Deal type">
                        @foreach(['percent' => ['%', 'Percent off'], 'fixed' => ['$', 'Amount off'], 'free' => ['FREE', 'Something free'], 'custom' => ['Aa', 'Custom label']] as $type => [$glyph, $label])
                            <label class="ao-type" :class="s.type === '{{ $type }}' && 'is-on'">
                                <input type="radio" name="deal_type" value="{{ $type }}" x-model="s.type" class="visually-hidden">
                                <b>{{ $glyph }}</b>{{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('deal_type')<span class="ao-error">{{ $message }}</span>@enderror

                    <div class="ao-field" x-show="s.type === 'percent' || s.type === 'fixed'" style="max-width: 240px;">
                        <label for="deal_value" x-text="s.type === 'percent' ? 'Discount (%)' : 'Discount amount ($)'">Value</label>
                        <input type="number" step="0.01" min="1" :max="s.type === 'percent' ? 100 : 100000" name="deal_value" id="deal_value" class="form-control" x-model="s.value">
                        @error('deal_value')<span class="ao-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="ao-locale-grid" x-show="s.type === 'custom'" x-cloak>
                        @foreach($locales as $code => $label)
                            <div class="ao-field">
                                <label for="badge_text_{{ $code }}">Badge text · {{ $label }} @if($code === 'en')<span class="text-danger">*</span>@endif</label>
                                <input type="text" maxlength="40" name="badge_text_{{ $code }}" id="badge_text_{{ $code }}" class="form-control" dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                                       x-model="s.badge.{{ $code }}" placeholder="{{ $code === 'en' ? '2 WEEKS FREE' : '' }}">
                                @error('badge_text_' . $code)<span class="ao-error">{{ $message }}</span>@enderror
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- ============ 3. TEXT ============ --}}
                <section class="ao-card ao-sec">
                    <h3><span>3</span> Text</h3>
                    <div class="ao-lang-tabs" role="tablist">
                        @foreach($locales as $code => $label)
                            <button type="button" role="tab" :aria-selected="lang === '{{ $code }}'" :class="lang === '{{ $code }}' && 'is-on'" @click="lang = '{{ $code }}'; previewLang = '{{ $code }}'">
                                {{ $label }} <i :class="(s.title.{{ $code }} || '').trim() ? 'is-done' : ''"></i>
                                @if($errors->hasAny(["title_$code", "summary_$code", "details_$code", "terms_$code"]))<em class="text-danger">!</em>@endif
                            </button>
                        @endforeach
                    </div>
                    @foreach($locales as $code => $label)
                        <div class="ao-lang-panel" x-show="lang === '{{ $code }}'" @if($code !== $firstErrorLocale) x-cloak @endif dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="ao-field">
                                <label for="title_{{ $code }}">Title @if($code === 'en')<span class="text-danger">*</span>@endif</label>
                                <input type="text" maxlength="120" name="title_{{ $code }}" id="title_{{ $code }}" class="form-control" x-model="s.title.{{ $code }}"
                                       placeholder="{{ $code === 'en' ? 'New Student Special' : ($code === 'ar' ? 'اختياري: يظهر الإنجليزي لو فاضي' : 'Optionnel') }}">
                                <span class="ao-hint" x-text="(s.title.{{ $code }} || '').length + ' / 120'"></span>
                                @error('title_' . $code)<span class="ao-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="ao-field">
                                <label for="summary_{{ $code }}">Short description (on the card) @if($code === 'en')<span class="text-danger">*</span>@endif</label>
                                <textarea maxlength="200" rows="2" name="summary_{{ $code }}" id="summary_{{ $code }}" class="form-control" x-model="s.summary.{{ $code }}"></textarea>
                                <span class="ao-hint" x-text="(s.summary.{{ $code }} || '').length + ' / 200 · two lines show on the card'"></span>
                                @error('summary_' . $code)<span class="ao-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="ao-field">
                                <label for="details_{{ $code }}">Full details (offer page)</label>
                                <textarea maxlength="5000" rows="4" name="details_{{ $code }}" id="details_{{ $code }}" class="form-control">{{ $val('details_' . $code) }}</textarea>
                                @error('details_' . $code)<span class="ao-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="ao-field">
                                <label for="terms_{{ $code }}">Terms &amp; conditions — one per line</label>
                                <textarea maxlength="3000" rows="3" name="terms_{{ $code }}" id="terms_{{ $code }}" class="form-control" placeholder="{{ $code === 'en' ? "Valid for new customers only.\nCannot be combined with other offers." : '' }}">{{ $val('terms_' . $code) }}</textarea>
                                @error('terms_' . $code)<span class="ao-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    @endforeach
                    <p class="ao-hint mb-0">English is required. Arabic and French are optional: when empty, visitors see the English text.</p>
                </section>

                {{-- ============ 4. IMAGE ============ --}}
                <section class="ao-card ao-sec">
                    <h3><span>4</span> Cover image</h3>
                    <div class="ao-image-row">
                        <div class="ao-image-preview so-scope">
                            <template x-if="imagePreview && !removeImage"><img :src="imagePreview" alt=""></template>
                            <template x-if="!imagePreview || removeImage">
                                <div class="so-cover-fallback" style="background: linear-gradient(135deg, #60a5fa, #1d4ed8);"><i class="fas fa-image"></i></div>
                            </template>
                        </div>
                        <div class="ao-field flex-grow-1">
                            <label for="image">Upload a photo</label>
                            <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp" class="form-control" @change="pickImage($event)">
                            <span class="ao-hint">JPG, PNG or WebP, up to 5 MB. A wide photo (about 1200×750) looks best; it is resized and converted to WebP automatically. Without an image, the card shows the category icon on a colour.</span>
                            @error('image')<span class="ao-error">{{ $message }}</span>@enderror
                            @if($offer->image_path)
                                <label class="ao-check"><input type="checkbox" name="remove_image" value="1" x-model="removeImage"> Remove the current image</label>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- ============ 5. SCHEDULE ============ --}}
                <section class="ao-card ao-sec">
                    <h3><span>5</span> Dates</h3>
                    <div class="two" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:.9rem">
                        <div class="ao-field">
                            <label for="starts_at">Starts</label>
                            <input type="date" name="starts_at" id="starts_at" class="form-control" x-model="s.starts">
                            <span class="ao-hint">Empty = starts now.</span>
                            @error('starts_at')<span class="ao-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="ao-field">
                            <label for="ends_at">Ends (last day included)</label>
                            <input type="date" name="ends_at" id="ends_at" class="form-control" x-model="s.ends">
                            <span class="ao-hint">Empty = no end date.</span>
                            @error('ends_at')<span class="ao-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </section>

                {{-- ============ 6. BUTTON ============ --}}
                <section class="ao-card ao-sec">
                    <h3><span>6</span> What the button does</h3>
                    <p class="ao-hint mb-0">No codes and no limits: anyone can use the offer as many times as they like.</p>
                    <div class="ao-types ao-types-3">
                        <label class="ao-type" :class="s.cta === 'whatsapp' && 'is-on'">
                            <input type="radio" name="cta_type" value="whatsapp" x-model="s.cta" class="visually-hidden">
                            <b><i class="fab fa-whatsapp"></i></b>Opens WhatsApp with a message about this offer
                        </label>
                        <label class="ao-type" :class="s.cta === 'profile' && 'is-on'">
                            <input type="radio" name="cta_type" value="profile" x-model="s.cta" class="visually-hidden">
                            <b><i class="fas fa-id-card"></i></b>Goes to the provider profile
                        </label>
                        <label class="ao-type" :class="s.cta === 'url' && 'is-on'">
                            <input type="radio" name="cta_type" value="url" x-model="s.cta" class="visually-hidden">
                            <b><i class="fas fa-link"></i></b>Opens a link
                        </label>
                    </div>
                    <div class="ao-field" x-show="s.cta === 'url'" x-cloak>
                        <label for="cta_url">Link</label>
                        <input type="url" name="cta_url" id="cta_url" class="form-control" value="{{ $val('cta_url') }}" placeholder="https://">
                        @error('cta_url')<span class="ao-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="ao-wa-sample" x-show="s.cta === 'whatsapp'">
                        <span class="ao-label">Message the provider receives</span>
                        <pre x-text="waSample()"></pre>
                    </div>
                </section>

                {{-- ============ 7. PLACEMENT ============ --}}
                <section class="ao-card ao-sec">
                    <h3><span>7</span> Visibility</h3>
                    <label class="ao-switch-row">
                        <input type="hidden" name="is_active" value="0">
                        <span class="ao-switch"><input type="checkbox" name="is_active" value="1" x-model="s.active"><span></span></span>
                        <span><strong>Active</strong><small>Off = draft, invisible everywhere.</small></span>
                    </label>
                    <label class="ao-switch-row">
                        <input type="hidden" name="is_featured" value="0">
                        <span class="ao-switch"><input type="checkbox" name="is_featured" value="1" x-model="s.featured"><span></span></span>
                        <span><strong>Show on the home page</strong><small>Also always listed on /offers and on the provider profile.</small></span>
                    </label>
                    <label class="ao-switch-row">
                        <input type="hidden" name="mark_verified" value="0">
                        <span class="ao-switch"><input type="checkbox" name="mark_verified" value="1" @checked(old('mark_verified', (bool) $offer->verified_at))><span></span></span>
                        <span>
                            <strong>Reviewed with the provider</strong>
                            <small>Shows "Reviewed by the SPEEDA team on &lt;date&gt;" on the offer. The date renews when you change the provider, deal, dates or button with this on.
                                @if($offer->verified_at)
                                    <span class="text-success d-block">Reviewed {{ $offer->verified_at->format('M j, Y') }}{{ $offer->verifier ? ' by ' . $offer->verifier->name : '' }}.</span>
                                @endif
                            </small>
                        </span>
                    </label>
                    <details class="ao-advanced">
                        <summary>Advanced</summary>
                        <div class="ao-grid-3 mt-3">
                            <div class="ao-field">
                                <label for="sort_order">Position</label>
                                <input type="number" min="1" name="sort_order" id="sort_order" class="form-control" value="{{ $val('sort_order') }}">
                                <span class="ao-hint">You can also drag rows in the list.</span>
                            </div>
                            <div class="ao-field" style="grid-column: span 2;">
                                <label for="slug">Link</label>
                                <div class="input-group">
                                    <span class="input-group-text">/offers/</span>
                                    <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $offer->slug) }}" placeholder="created from the title">
                                </div>
                                <span class="ao-hint">Changing it breaks links already shared.</span>
                                @error('slug')<span class="ao-error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </details>
                </section>
            </div>

            {{-- ============ PREVIEW + SAVE ============ --}}
            <aside class="ao-editor-side">
                <div class="ao-card">
                    <div class="ao-side-head">
                        <strong>Live preview</strong>
                        <div class="ao-seg" role="group" aria-label="Preview language">
                            <template x-for="l in ['en','ar','fr']" :key="l">
                                <button type="button" :class="previewLang === l && 'is-on'" @click="previewLang = l" x-text="l.toUpperCase()"></button>
                            </template>
                        </div>
                    </div>
                    <div class="ao-preview so-scope" :dir="previewLang === 'ar' ? 'rtl' : 'ltr'">
                        <article class="so-card">
                            <div class="so-card-media">
                                <template x-if="imagePreview && !removeImage"><img :src="imagePreview" alt=""></template>
                                <template x-if="!imagePreview || removeImage"><div class="so-cover-fallback" style="background: linear-gradient(135deg, #60a5fa, #1d4ed8);"><i class="fas fa-tag"></i></div></template>
                                <span class="so-badge" :class="{ 'so-badge--free': s.type === 'free', 'so-badge--custom': s.type === 'custom' }" x-text="badge()"></span>
                                <template x-if="daysLeft() !== null && daysLeft() >= 0 && daysLeft() <= 7"><span class="so-urgent" x-text="daysLeft() + 'd'"></span></template>
                            </div>
                            <div class="so-card-body">
                                <div class="so-card-provider">
                                    <div class="so-logo">
                                        <template x-if="provider() && provider().image"><img :src="provider().image" alt=""></template>
                                        <template x-if="!provider() || !provider().image"><span style="background: linear-gradient(135deg, #60a5fa, #1d4ed8);" x-text="provider() ? provider().initials : '?'"></span></template>
                                    </div>
                                    <div class="so-card-provider-text">
                                        <strong x-text="provider() ? provider().name : 'Choose a provider'"></strong>
                                        <small x-text="provider() ? (provider().category || '') : ''"></small>
                                    </div>
                                </div>
                                <span class="so-tag" x-text="t('tag')"></span>
                                <h3 class="so-card-title" x-text="text('title') || 'Offer title'"></h3>
                                <p class="so-card-text" x-text="text('summary') || 'The short description appears here.'"></p>
                                <div class="so-meta">
                                    <span x-show="provider() && provider().city"><i class="fas fa-location-dot"></i><span x-text="provider() ? provider().city : ''"></span></span>
                                    <span><i class="far fa-calendar"></i><span x-text="s.ends ? t('valid').replace('{d}', shortDate(s.ends)) : t('ongoing')"></span></span>
                                </div>
                                <span class="so-btn so-btn-primary"><span x-text="t('view')"></span> <i class="fas fa-arrow-right"></i></span>
                            </div>
                        </article>
                    </div>
                </div>

                <div class="ao-card ao-sec">
                    <div class="ao-status-line">
                        <span class="ao-pill" :class="'ao-pill--' + status()" x-text="statusLabel()"></span>
                        <span class="ao-hint" x-text="statusText()"></span>
                    </div>
                    <ul class="ao-checks">
                        <li :class="s.provider && 'ok'"><i></i>Provider chosen</li>
                        <li :class="dealOk() && 'ok'"><i></i>Deal value</li>
                        <li :class="(s.title.en || '').trim() && 'ok'"><i></i>English title</li>
                        <li :class="(s.summary.en || '').trim() && 'ok'"><i></i>English short description</li>
                        <li :class="datesOk() && 'ok'"><i></i>End date on or after start date</li>
                        <li class="is-optional" :class="(s.title.ar || '').trim() && 'ok'"><i></i>Arabic translation (optional)</li>
                        <li class="is-optional" :class="(s.title.fr || '').trim() && 'ok'"><i></i>French translation (optional)</li>
                    </ul>
                    <button type="submit" class="admin-btn admin-btn-primary text-white w-100 justify-content-center">
                        <i class="fas fa-save"></i><span>{{ $offer->exists ? 'Save changes' : 'Create offer' }}</span>
                    </button>
                </div>

                @if($offer->exists)
                    <div class="ao-card ao-sec">
                        <strong>All-time results</strong>
                        <div class="ao-mini-stats">
                            <div><b>{{ number_format($stats['views']) }}</b><span>Page views</span></div>
                            <div><b>{{ number_format($stats['clicks']) }}</b><span>WhatsApp clicks</span></div>
                            <div><b>{{ number_format($stats['likes']) }}</b><span>Likes</span></div>
                        </div>
                        <a href="{{ route('admin.offers.analytics', ['offer_id' => $offer->id, 'range' => '90']) }}" class="ao-hint">Full statistics →</a>
                        <p class="ao-hint mb-0">Created {{ $offer->created_at?->format('M j, Y') }}@if($offer->creator) by {{ $offer->creator->name }}@endif · updated {{ $offer->updated_at?->diffForHumans() }}@if($offer->updater) by {{ $offer->updater->name }}@endif</p>
                    </div>
                @endif
            </aside>
        </form>

        @if($offer->exists)
            <div class="d-flex gap-2 flex-wrap mt-3">
                <form method="POST" action="{{ route('admin.offers.duplicate', $offer) }}">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-secondary"><i class="fas fa-clone"></i><span>Duplicate as draft</span></button>
                </form>
                <form method="POST" action="{{ route('admin.offers.destroy', $offer) }}" onsubmit="return confirm('Delete this offer? Its statistics are kept and you can undo from the activity log.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="admin-icon-action admin-icon-danger"><i class="fas fa-trash"></i><span>Delete offer</span></button>
                </form>
            </div>
        @endif
    </div>
</div>

@include('admin.offers.partials.styles')
@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .ao-editor { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(300px, 1fr); gap: 1.25rem; align-items: start; }
    @media (max-width: 1199.98px) { .ao-editor { grid-template-columns: 1fr; } }
    .ao-editor-main { display: grid; gap: 1rem; min-width: 0; }
    .ao-editor-side { display: grid; gap: 1rem; position: sticky; top: 90px; }
    @media (max-width: 1199.98px) { .ao-editor-side { position: static; } }
    .ao-sec { padding: 1.15rem 1.25rem; display: grid; gap: .9rem; }
    .ao-sec h3 { font-size: 1rem; font-weight: 800; margin: 0; display: flex; align-items: center; gap: .55rem; color: #0f172a; }
    .ao-sec h3 span { width: 24px; height: 24px; border-radius: 7px; background: #eff6ff; color: #2563eb; display: grid; place-items: center; font-size: .78rem; }
    .ao-types { display: grid; grid-template-columns: repeat(4, 1fr); gap: .6rem; }
    .ao-types-3 { grid-template-columns: repeat(3, 1fr); }
    @media (max-width: 640px) { .ao-types, .ao-types-3 { grid-template-columns: repeat(2, 1fr); } }
    .ao-type { border: 1.5px solid #e2e8f0; border-radius: 13px; padding: .7rem .5rem; text-align: center; cursor: pointer; font-weight: 600; font-size: .82rem; color: #475569; display: grid; gap: .2rem; transition: border-color .2s, background .2s; }
    .ao-type b { font-size: 1.15rem; font-weight: 800; color: #0f172a; }
    .ao-type.is-on { border-color: #2563eb; background: #eff6ff; color: #1d4ed8; }
    .ao-type.is-on b { color: #2563eb; }
    .ao-type:focus-within { outline: 3px solid #bfdbfe; outline-offset: 2px; }
    .ao-lang-tabs { display: flex; gap: .25rem; border-bottom: 1px solid #e6ecf3; }
    .ao-lang-tabs button { border: 0; background: none; padding: .55rem .85rem; font-weight: 700; color: #64748b; border-bottom: 2px solid transparent; display: flex; align-items: center; gap: .4rem; }
    .ao-lang-tabs button.is-on { color: #2563eb; border-bottom-color: #2563eb; }
    .ao-lang-tabs i { width: 7px; height: 7px; border-radius: 50%; background: #cbd5e1; }
    .ao-lang-tabs i.is-done { background: #059669; }
    .ao-lang-panel { display: grid; gap: .85rem; }
    .ao-inline-warn { font-size: .82rem; color: #92400e; background: #fffbeb; border-radius: 9px; padding: .45rem .65rem; }
    .ao-image-row { display: flex; gap: 1rem; align-items: flex-start; flex-wrap: wrap; }
    .ao-image-preview { width: 200px; aspect-ratio: 16 / 10; border-radius: 12px; overflow: hidden; flex: none; max-width: 100%; }
    .ao-image-preview img, .ao-image-preview .so-cover-fallback { width: 100%; height: 100%; object-fit: cover; font-size: 2rem; }
    .ao-check { display: flex; gap: .4rem; align-items: center; font-size: .85rem; }
    .ao-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .9rem; }
    @media (max-width: 767.98px) { .ao-grid-3 { grid-template-columns: 1fr; } .ao-grid-3 .ao-field { grid-column: auto !important; } }
    .ao-wa-sample pre { white-space: pre-wrap; background: #dcfce7; border-radius: 12px 12px 12px 4px; padding: .8rem 1rem; font-family: inherit; font-size: .85rem; color: #14532d; margin: .35rem 0 0; max-width: 420px; }
    .ao-advanced summary { cursor: pointer; font-weight: 700; color: #475569; font-size: .88rem; }
    .ao-side-head { display: flex; justify-content: space-between; align-items: center; padding: .85rem 1.1rem; border-bottom: 1px solid #e6ecf3; }
    .ao-preview { padding: 1.1rem; background: repeating-conic-gradient(#f1f5f9 0 25%, #fff 0 50%) 0 0 / 18px 18px; border-radius: 0 0 18px 18px; }
    .ao-preview .so-card { max-width: 320px; margin: 0 auto; }
    .ao-status-line { display: flex; gap: .6rem; align-items: center; flex-wrap: wrap; }
    .ao-checks { list-style: none; margin: 0; padding: 0; display: grid; gap: .35rem; font-size: .85rem; color: #94a3b8; }
    .ao-checks li { display: flex; gap: .5rem; align-items: center; }
    .ao-checks li i { width: 16px; height: 16px; border-radius: 50%; border: 2px solid #cbd5e1; flex: none; }
    .ao-checks li.ok { color: #0f172a; }
    .ao-checks li.ok i { background: #059669; border-color: #059669; box-shadow: inset 0 0 0 3px #fff; }
    .ao-mini-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; }
    .ao-mini-stats div { background: #f8fafc; border-radius: 12px; padding: .6rem; text-align: center; }
    .ao-mini-stats b { display: block; font-size: 1.25rem; color: #0f172a; font-variant-numeric: tabular-nums; }
    .ao-mini-stats span { font-size: .72rem; color: #64748b; }
</style>
@endpush
@endsection

@push('scripts')
<script>
function offerForm(config) {
    return {
        providers: config.providers,
        badges: config.badges,
        s: config.state,
        lang: @js($firstErrorLocale),
        previewLang: 'en',
        providerQuery: '',
        imagePreview: config.state.image,
        removeImage: false,

        filteredProviders() {
            const q = this.providerQuery.trim().toLowerCase();
            const list = q ? this.providers.filter(p => (p.name + ' ' + (p.city || '') + ' ' + (p.category || '')).toLowerCase().includes(q)) : this.providers;
            // Keep the selected provider in the list even when the filter hides it.
            const selected = this.provider();
            return selected && !list.includes(selected) ? [selected, ...list] : list;
        },
        provider() { return this.providers.find(p => String(p.id) === String(this.s.provider)) || null; },
        t(key) { return (this.badges[this.previewLang] || this.badges.en)[key]; },
        text(field) { return (this.s[field][this.previewLang] || '').trim() || (this.s[field].en || '').trim() || (this.s[field].ar || '').trim() || (this.s[field].fr || '').trim(); },
        fmt(v) { const n = parseFloat(v); return isNaN(n) ? '' : String(Math.round(n * 100) / 100); },
        badge() { return this.badgeFor(this.previewLang); },
        badgeFor(lang) {
            const b = this.badges[lang];
            if (this.s.type === 'percent') return b.percent.replace('{v}', this.fmt(this.s.value) || '0');
            if (this.s.type === 'fixed') return b.fixed.replace('{v}', this.fmt(this.s.value) || '0');
            if (this.s.type === 'free') return b.free;
            return (this.s.badge[lang] || '').trim() || (this.s.badge.en || '').trim() || b.special;
        },
        shortDate(iso) {
            const d = new Date(iso + 'T00:00:00');
            if (isNaN(d)) return iso;
            return d.toLocaleDateString(this.previewLang === 'ar' ? 'ar' : this.previewLang, { month: 'short', day: 'numeric' });
        },
        daysLeft() {
            if (!this.s.ends) return null;
            return Math.round((new Date(this.s.ends + 'T00:00:00') - new Date(this.s.today + 'T00:00:00')) / 86400000);
        },
        dealOk() {
            if (this.s.type === 'free') return true;
            if (this.s.type === 'custom') return !!(this.s.badge.en || '').trim();
            const v = parseFloat(this.s.value);
            return v >= 1 && (this.s.type !== 'percent' || v <= 100);
        },
        datesOk() { return !this.s.starts || !this.s.ends || this.s.ends >= this.s.starts; },
        status() {
            if (!this.s.active) return 'draft';
            if (this.s.starts && this.s.starts > this.s.today) return 'scheduled';
            if (this.s.ends && this.s.ends < this.s.today) return 'expired';
            return 'live';
        },
        statusLabel() { return { live: 'Live', scheduled: 'Scheduled', expired: 'Expired', draft: 'Draft' }[this.status()]; },
        statusText() {
            const st = this.status();
            if (st === 'draft') return 'Invisible to visitors until you switch it on.';
            if (st === 'scheduled') return 'Appears automatically on ' + this.s.starts + '.';
            if (st === 'expired') return 'The end date has passed, so visitors will not see it.';
            return this.s.featured ? 'Visible on the home page, /offers and the provider profile.' : 'Visible on /offers and the provider profile (not on the home page).';
        },
        waSample() {
            const p = this.provider();
            const title = (this.s.title.en || 'Offer title').trim();
            const badge = this.badgeFor('en');
            const link = @js(url('/offers')) + '/' + (@js($offer->slug) || 'your-offer');
            return 'Hi ' + (p ? p.name : 'Provider') + " 👋\n\nI saw your offer on SPEEDA and I'd like to get it:\n\n🏷️ " + title + ' (' + badge + ')\n\nThe offer on SPEEDA: ' + link;
        },
        pickImage(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            this.removeImage = false;
            const reader = new FileReader();
            reader.onload = ev => { this.imagePreview = ev.target.result; };
            reader.readAsDataURL(file);
        },
    };
}
</script>
@endpush
