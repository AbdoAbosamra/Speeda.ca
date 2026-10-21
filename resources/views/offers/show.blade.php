@extends('layouts.app')

@include('partials.offers.styles')
@include('partials.offers.scripts')

@push('json-ld')
    <script type="application/ld+json">{!! \App\Support\SafeJsonLd::encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => __('offers.breadcrumb_home'), 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => __('offers.page_title'), 'item' => route('offers.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $offer->localized_title, 'item' => route('offers.show', $offer->slug)],
        ],
    ]) !!}</script>
@endpush

@section('content')
@php
    $provider = $offer->serviceProvider;
    $cta = $offer->effectiveCta();
    $rating = (float) ($provider?->calculated_rating ?: $provider?->rating);
    $rating = $reviewCount > 0 && $rating > 0 ? $rating : null;
    $badgeClass = match ($offer->deal_type) {
        \App\Models\Offer::TYPE_FREE => 'so-badge--free',
        \App\Models\Offer::TYPE_CUSTOM => 'so-badge--custom',
        default => '',
    };
    $claimLabel = match ($cta) {
        \App\Models\Offer::CTA_WHATSAPP => __('offers.claim_whatsapp'),
        \App\Models\Offer::CTA_URL => __('offers.claim_url'),
        default => __('offers.claim_profile'),
    };
@endphp

<div class="container so-scope pb-5">
    <nav class="so-fine mb-3" aria-label="breadcrumb">
        <a href="{{ route('home') }}" class="text-decoration-none">{{ __('offers.breadcrumb_home') }}</a> /
        <a href="{{ route('offers.index') }}" class="text-decoration-none">{{ __('offers.page_title') }}</a> /
        <span>{{ $offer->localized_title }}</span>
    </nav>

    @if($isAdmin && $status !== \App\Models\Offer::STATUS_LIVE)
        <div class="so-notice so-notice--warn mb-3">
            <i class="fas fa-eye" aria-hidden="true"></i>
            <span>{{ __('offers.not_live_preview', ['status' => $status]) }}
                <a href="{{ route('admin.offers.edit', $offer) }}">Edit offer</a></span>
        </div>
    @endif

    @if(! $isLive && ! $isAdmin)
        <div class="so-notice so-notice--muted mb-3">
            <i class="fas fa-circle-info" aria-hidden="true"></i>
            <span><strong>{{ __('offers.ended_title') }}.</strong> {{ __('offers.ended_text') }}</span>
        </div>
    @endif

    <div class="so-detail">
        <div class="d-grid gap-3" style="min-width:0">
            <div class="so-detail-media">
                @include('partials.offers.cover', ['offer' => $offer, 'eager' => true])
                <span class="so-badge {{ $badgeClass }}">{{ $offer->badgeLabel() }}</span>
            </div>

            <div class="so-panel d-grid gap-2">
                <span class="so-tag">{{ __('offers.exclusive_tag') }}</span>
                <h1>{{ $offer->localized_title }}</h1>
                @if($offer->localized_summary)
                    <p class="so-lead">{{ $offer->localized_summary }}</p>
                @endif

                {{-- Offer likes and comments are temporarily disabled; keep their code for later. --}}
                @if(! auth()->check() || auth()->user()->isClient())
                <div class="so-engage" id="engage">
                    {{-- Temporarily disabled: offer likes.
                    @if($isLive)
                        <form method="POST" action="{{ route('offers.like', $offer->slug) }}" class="m-0" data-so-like>
                            @csrf
                            <input type="hidden" name="source" value="{{ $source }}">
                            <button type="submit" class="so-pill {{ $viewerLiked ? 'is-on' : '' }}" aria-pressed="{{ $viewerLiked ? 'true' : 'false' }}">
                                <i class="{{ $viewerLiked ? 'fas' : 'far' }} fa-heart" aria-hidden="true"></i>
                                <span data-label>{{ $viewerLiked ? __('offers.liked') : __('offers.like') }}</span>
                            </button>
                        </form>
                    @endif
                    <span class="so-fine" data-like-count>{{ trans_choice('offers.likes_count', $offer->likes_count, ['count' => $offer->likes_count]) }}</span>
                    --}}

                    @if(! auth()->check() || auth()->user()->isClient())
                        <form method="POST" action="{{ route('offers.favorite', $offer->slug) }}" class="m-0 ms-auto" data-so-favorite>
                            @csrf
                            <input type="hidden" name="source" value="{{ $source }}">
                            <button type="submit" class="so-pill so-pill--save {{ $viewerSaved ? 'is-on' : '' }}" aria-pressed="{{ $viewerSaved ? 'true' : 'false' }}"
                                @guest title="{{ __('offers.favorites_login') }}" @endguest>
                                <i class="{{ $viewerSaved ? 'fas' : 'far' }} fa-bookmark" aria-hidden="true"></i>
                                <span data-label>{{ $viewerSaved ? __('offers.saved_favorite') : __('offers.save_favorite') }}</span>
                            </button>
                        </form>
                    @endif

                    {{-- Temporarily disabled: link to offer comments.
                    <a href="#comments" class="so-pill so-pill--link {{ (! auth()->check() || auth()->user()->isClient()) ? '' : 'ms-auto' }}">
                        <i class="far fa-comment" aria-hidden="true"></i>
                        <span>{{ trans_choice('offers.comments_count', $comments->count(), ['count' => $comments->count()]) }}</span>
                    </a>
                    --}}
                </div>
                <p class="so-fine text-danger mb-0" data-engage-error role="alert" hidden></p>
                @endif
            </div>

            @if($offer->localized_details)
                <div class="so-panel">
                    <h2 class="h6 fw-bold mb-2">{{ __('offers.details_title') }}</h2>
                    <p class="so-lead">{{ $offer->localized_details }}</p>
                </div>
            @endif

            @if(count($offer->termsLines()))
                <div class="so-panel">
                    <h2 class="h6 fw-bold mb-2">{{ __('offers.terms_title') }}</h2>
                    <ul class="so-terms">
                        @foreach($offer->termsLines() as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Temporarily disabled: approved offer comments and the submission form.
            <section class="so-panel so-comments" id="comments" aria-labelledby="so-comments-title">
                <h2 class="so-comments-title" id="so-comments-title">
                    <i class="far fa-comments" aria-hidden="true"></i> {{ __('offers.comments_title') }}
                    <small>{{ trans_choice('offers.comments_count', $comments->count(), ['count' => $comments->count()]) }}</small>
                </h2>

                @forelse($comments as $comment)
                    <article class="so-comment">
                        <span class="so-comment-avatar" aria-hidden="true">{{ $comment->initial() }}</span>
                        <div class="so-comment-body">
                            <div class="so-comment-head">
                                <b>{{ $comment->author_name }}</b>
                                <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->locale(app()->getLocale())->diffForHumans() }}</time>
                            </div>
                            <p>{{ $comment->body }}</p>
                        </div>
                    </article>
                @empty
                    <p class="so-fine mb-0">{{ __('offers.comments_empty') }}</p>
                @endforelse

                @if(session('comment_sent'))
                    <div class="so-notice so-notice--ok" role="status"><i class="fas fa-check-circle" aria-hidden="true"></i><span>{{ session('comment_sent') }}</span></div>
                @endif

                @if($isLive)
                    <form method="POST" action="{{ route('offers.comment', $offer->slug) }}" class="so-comment-form" data-so-comment novalidate>
                        @csrf
                        <input type="hidden" name="source" value="{{ $source }}">
                        <!-- Honeypot: hidden from people, filled in by bots. -->
                        <div class="so-hp" aria-hidden="true">
                            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>
                        <h3 class="so-comment-form-title">{{ __('offers.leave_comment') }}</h3>
                        @auth
                            <p class="so-fine mb-0">{{ __('offers.comment_as', ['name' => auth()->user()->name]) }}</p>
                        @else
                            <label class="d-grid gap-1">
                                <span class="so-fine fw-semibold text-dark">{{ __('offers.comment_name') }}</span>
                                <input type="text" name="author_name" class="form-control @error('author_name') is-invalid @enderror" maxlength="60" required value="{{ old('author_name') }}" autocomplete="name">
                                @error('author_name')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </label>
                        @endauth
                        <label class="d-grid gap-1">
                            <span class="so-fine fw-semibold text-dark">{{ __('offers.comment_body') }}</span>
                            <textarea name="body" rows="3" maxlength="1000" required class="form-control @error('body') is-invalid @enderror" placeholder="{{ __('offers.comment_placeholder') }}">{{ old('body') }}</textarea>
                            @error('body')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                        </label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <button type="submit" class="so-btn so-btn-primary">{{ __('offers.comment_submit') }}</button>
                            <span class="so-fine"><i class="fas fa-shield-halved" aria-hidden="true"></i> {{ __('offers.comment_review_note') }}</span>
                        </div>
                        <p class="so-fine text-danger mb-0" data-comment-error role="alert" hidden></p>
                    </form>
                @else
                    <p class="so-fine mb-0">{{ __('offers.comment_closed') }}</p>
                @endif
            </section>
            --}}
        </div>

        <aside class="so-side">
            <div class="so-panel d-grid gap-3" id="so-claim-panel">
                <div class="so-big-badge">
                    <b class="{{ $offer->deal_type === \App\Models\Offer::TYPE_FREE ? 'is-free' : '' }}">{{ $offer->badgeLabel() }}</b>
                    @if($offer->providerCity())
                        <span>{{ __('offers.in_city', ['city' => $offer->providerCity()]) }}</span>
                    @endif
                </div>

                @if($isLive && $offer->ends_at)
                    <div>
                        <p class="so-fine mb-1">{{ __('offers.offer_ends_in') }}</p>
                        <div class="so-countdown" data-ends="{{ $offer->endsAtMoment()->toIso8601String() }}">
                            <div><span data-unit="d">--</span><small>{{ __('offers.days') }}</small></div>
                            <div><span data-unit="h">--</span><small>{{ __('offers.hours') }}</small></div>
                            <div><span data-unit="m">--</span><small>{{ __('offers.minutes') }}</small></div>
                            <div><span data-unit="s">--</span><small>{{ __('offers.seconds') }}</small></div>
                        </div>
                    </div>
                @endif

                @if($isLive || $isAdmin)
                    {{-- One plain link: it counts the click, then opens WhatsApp with a
                         message that names this SPEEDA offer and links to it. No code,
                         no limit. --}}
                    <a href="{{ route('offers.go', ['slug' => $offer->slug, 'src' => $source]) }}" rel="nofollow"
                        class="so-btn {{ $cta === \App\Models\Offer::CTA_WHATSAPP ? 'so-btn-wa' : 'so-btn-primary' }}">
                        @if($cta === \App\Models\Offer::CTA_WHATSAPP)<i class="fab fa-whatsapp fs-5" aria-hidden="true"></i>@else<i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>@endif
                        <span>{{ $claimLabel }}</span>
                    </a>
                @endif

                <button type="button" class="so-btn so-btn-share" id="so-share" data-copied="{{ __('offers.link_copied') }}">
                    <i class="fas fa-share-nodes" aria-hidden="true"></i>
                    <span>{{ __('offers.share_offer') }}</span>
                </button>
                <p id="so-share-status" class="so-fine mb-0" role="status" aria-live="polite"></p>
                <div id="so-share-fallback" hidden>
                    <label for="so-share-url" class="so-fine">{{ __('readiness.copy_manually') }}</label>
                    <input id="so-share-url" class="form-control" type="text" readonly dir="ltr">
                </div>

                @if($isLive || $isAdmin)
                    {{-- Plain text: the green box below already carries the trust message. --}}
                    <p class="so-fine mb-0">
                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                        {{ $cta === \App\Models\Offer::CTA_WHATSAPP
                            ? __('offers.claim_note', ['provider' => $offer->providerName()])
                            : __('offers.claim_note_profile', ['provider' => $offer->providerName()]) }}
                    </p>

                    @include('partials.offers.trust')
                @endif

                @if($offer->ends_at)
                    <p class="so-fine mb-0"><i class="far fa-calendar" aria-hidden="true"></i> {{ __('offers.valid_until', ['date' => \App\Models\Offer::localDate($offer->ends_at)]) }}</p>
                @endif
            </div>

            @if($provider)
                <div class="so-panel d-grid gap-3">
                    <div class="so-prov-row">
                        <div class="so-logo">
                            @if($provider->profile_image)
                                <img src="{{ $provider->profile_image_url }}" onerror="this.onerror=null;this.src='{{ asset('images/user.png') }}';" alt="">
                            @else
                                <span style="background: {{ $offer->fallbackGradient() }};">{{ $offer->providerInitials() }}</span>
                            @endif
                        </div>
                        <div style="min-width:0">
                            <b class="d-block text-dark">{{ $offer->providerName() }}</b>
                            @if($offer->providerCategory())<span class="so-fine">{{ $offer->providerCategory() }}</span>@endif
                            @if($rating)
                                <div class="so-stars" aria-label="{{ round($rating, 1) }} / 5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= round($rating) ? 'fas' : 'far' }} fa-star" aria-hidden="true"></i>
                                    @endfor
                                    <em>{{ number_format($rating, 1) }} {{ trans_choice('offers.reviews', $reviewCount, ['count' => $reviewCount]) }}</em>
                                </div>
                            @endif
                        </div>
                    </div>
                    <a class="so-btn so-btn-ghost" href="{{ route('service-providers.show', $provider) }}">{{ __('offers.view_profile') }}</a>
                </div>
            @endif

            @include('partials.offers.publish-cta')
        </aside>
    </div>

    @if($moreFromProvider->isNotEmpty())
        <section class="mt-5">
            <h2 class="so-strip-title">{{ __('offers.more_from_provider', ['provider' => $offer->providerName()]) }}</h2>
            <div class="so-grid" data-so-impressions="similar">
                @foreach($moreFromProvider as $o)
                    @include('partials.offers.card', ['offer' => $o, 'source' => 'similar'])
                @endforeach
            </div>
        </section>
    @endif

    @if($similar->isNotEmpty())
        <section class="mt-5">
            <h2 class="so-strip-title">{{ __('offers.more_offers') }}</h2>
            <div class="so-grid" data-so-impressions="similar">
                @foreach($similar as $o)
                    @include('partials.offers.card', ['offer' => $o, 'source' => 'similar'])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const offerId = {{ $offer->id }};

    // Countdown to the end of the offer's last day.
    const box = document.querySelector('.so-countdown');
    if (box) {
        const end = new Date(box.getAttribute('data-ends')).getTime();
        const set = function (unit, v) { const el = box.querySelector('[data-unit="' + unit + '"]'); if (el) el.textContent = String(v).padStart(2, '0'); };
        const tick = function () {
            let s = Math.max(0, Math.floor((end - Date.now()) / 1000));
            set('d', Math.floor(s / 86400)); set('h', Math.floor(s % 86400 / 3600)); set('m', Math.floor(s % 3600 / 60)); set('s', s % 60);
        };
        tick();
        setInterval(tick, 1000);
    }

    // Save: answered as JSON so the page updates in place; a guest
    // pressing save is sent to log in, then back to this offer.
    const engageError = document.querySelector('[data-engage-error]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const postForm = function (form) {
        return fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: new FormData(form),
            credentials: 'same-origin'
        }).then(function (r) { return r.json().then(function (data) { return { status: r.status, data: data }; }); });
    };
    const setPill = function (button, on, label, icon) {
        button.classList.toggle('is-on', on);
        button.setAttribute('aria-pressed', on ? 'true' : 'false');
        button.querySelector('i').className = (on ? 'fas' : 'far') + ' ' + icon;
        if (label) button.querySelector('[data-label]').textContent = label;
    };
    const showEngageError = function (message) {
        if (!engageError) return;
        engageError.textContent = message || @json(__('offers.action_failed'));
        engageError.hidden = false;
    };

    {{-- Temporarily disabled: offer like handler.
    const likeForm = document.querySelector('[data-so-like]');
    if (likeForm) {
        likeForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const button = likeForm.querySelector('button');
            button.disabled = true;
            postForm(likeForm).then(function (res) {
                button.disabled = false;
                if (res.status !== 200) { showEngageError(res.data.message); return; }
                setPill(button, res.data.liked, res.data.liked ? @json(__('offers.liked')) : @json(__('offers.like')), 'fa-heart');
                const count = document.querySelector('[data-like-count]');
                if (count) count.textContent = res.data.label;
            }).catch(function () { button.disabled = false; showEngageError(); });
        });
    }

    --}}

    const favForm = document.querySelector('[data-so-favorite]');
    if (favForm) {
        favForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const button = favForm.querySelector('button');
            button.disabled = true;
            postForm(favForm).then(function (res) {
                button.disabled = false;
                if (res.status === 401 && res.data.login) { window.location.href = res.data.login; return; }
                if (res.status !== 200) { showEngageError(res.data.message); return; }
                setPill(button, res.data.saved, res.data.label, 'fa-bookmark');
            }).catch(function () { button.disabled = false; showEngageError(); });
        });
    }

    {{-- Temporarily disabled: offer comment handler.
    // Comments: the thank-you replaces the form; nothing appears until approved.
    const commentForm = document.querySelector('[data-so-comment]');
    if (commentForm) {
        commentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const button = commentForm.querySelector('button[type=submit]');
            const errorBox = commentForm.querySelector('[data-comment-error]');
            errorBox.hidden = true;
            button.disabled = true;
            postForm(commentForm).then(function (res) {
                button.disabled = false;
                if (res.status === 200 && res.data.ok) {
                    const done = document.createElement('div');
                    done.className = 'so-notice so-notice--ok';
                    done.setAttribute('role', 'status');
                    done.innerHTML = '<i class="fas fa-check-circle" aria-hidden="true"></i><span></span>';
                    done.querySelector('span').textContent = res.data.message;
                    commentForm.replaceWith(done);
                    return;
                }
                const errors = res.data.errors ? Object.values(res.data.errors).flat() : [res.data.message];
                errorBox.textContent = errors.filter(Boolean).join(' ') || @json(__('offers.action_failed'));
                errorBox.hidden = false;
            }).catch(function () {
                button.disabled = false;
                errorBox.textContent = @json(__('offers.action_failed'));
                errorBox.hidden = false;
            });
        });
    }

    --}}

    const share = document.getElementById('so-share');
    if (share) {
        share.addEventListener('click', function () {
            const url = @json(route('offers.show', ['slug' => $offer->slug, 'src' => 'share']));
            const title = @json($offer->localized_title);
            window.speedaOfferTrack && window.speedaOfferTrack('share', [offerId], 'direct');
            const manual = function () {
                document.getElementById('so-share-fallback').hidden = false;
                const input = document.getElementById('so-share-url');
                input.value = url;
                input.focus();
                input.select();
                document.getElementById('so-share-status').textContent = @json(__('readiness.copy_manually'));
            };
            const copy = function () {
                if (!navigator.clipboard || !window.isSecureContext) { manual(); return; }
                navigator.clipboard.writeText(url).then(function () {
                    document.getElementById('so-share-status').textContent = share.dataset.copied;
                }).catch(manual);
            };
            if (navigator.share) {
                navigator.share({ title: title, url: url }).catch(function (error) {
                    if (error.name !== 'AbortError') copy();
                });
            } else { copy(); }
        });
    }
})();
</script>
@endpush
