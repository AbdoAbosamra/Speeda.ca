{{--
    Home page video section.

    Included only when HomeVideo::forHomePage() returned a record, so this
    file never has to defend against a missing or unplayable video.

    Performance is the reason for the "facade" pattern here: a YouTube iframe
    pulls well over a megabyte of script before anyone presses play, which
    would land squarely on the home page's Largest Contentful Paint. Instead
    the poster image is rendered as a plain <img> and the iframe is created
    only on click. Uploaded files use preload="none" for the same reason —
    the browser fetches nothing until the visitor asks for it.

    $homeVideo  App\Models\HomeVideo
--}}
@php
    $hvTitle = $homeVideo->localized_title;
    $hvDescription = $homeVideo->localized_description;
    $hvPoster = $homeVideo->displayPosterUrl();
@endphp

<section class="home-section home-video-section">
    <div class="container">
        <div class="home-shell home-video-panel fade-in-up">
            @if($hvTitle || $hvDescription)
                <div class="home-video-head">
                    @if($hvTitle)
                        <h2 class="home-video-title">{{ $hvTitle }}</h2>
                    @endif
                    @if($hvDescription)
                        <p class="home-video-desc">{{ $hvDescription }}</p>
                    @endif
                </div>
            @endif

            <div class="home-video-frame">
                @if($homeVideo->isUpload())
                    {{-- preload="none": nothing is downloaded until play is pressed. --}}
                    <video class="home-video-player"
                           src="{{ $homeVideo->uploadUrl() }}"
                           @if($hvPoster) poster="{{ $hvPoster }}" @endif
                           controls
                           preload="none"
                           playsinline></video>
                @else
                    <div class="home-video-facade"
                         data-home-video-embed="{{ $homeVideo->embedUrl() }}"
                         role="button"
                         tabindex="0"
                         aria-label="{{ $hvTitle ?: __('home.video_play') }}">
                        @if($hvPoster)
                            <img class="home-video-poster"
                                 src="{{ $hvPoster }}"
                                 alt="{{ $hvTitle ?: '' }}"
                                 loading="lazy"
                                 decoding="async"
                                 width="1280" height="720">
                        @else
                            <div class="home-video-poster home-video-poster-fallback" aria-hidden="true"></div>
                        @endif

                        <span class="home-video-play" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="34" height="34">
                                <path d="M8 5.14v13.72a1 1 0 0 0 1.54.84l10.28-6.86a1 1 0 0 0 0-1.68L9.54 4.3A1 1 0 0 0 8 5.14z"/>
                            </svg>
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@push('styles')
    <style>
        .home-shell.home-video-panel {
            position: relative;
            overflow: hidden;
            padding: clamp(1.75rem, 4vw, 3rem);
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 55%, #eaf2ff 100%);
            border: 1px solid #d7e5fb;
            border-radius: 28px;
            box-shadow: 0 20px 50px -34px rgba(37, 99, 235, 0.35);
        }

        .home-video-head {
            position: relative;
            text-align: center;
            max-width: 680px;
            margin: 0 auto clamp(1.5rem, 3vw, 2.25rem);
        }

        .home-video-title {
            color: #0f172a !important;
            font-weight: 800;
            font-size: clamp(1.5rem, 3.2vw, 2.15rem);
            line-height: 1.25;
            margin: 0;
        }

        .home-video-desc {
            color: #475569 !important;
            font-size: clamp(.95rem, 1.6vw, 1.05rem);
            line-height: 1.7;
            margin: .75rem 0 0;
        }

        .home-video-frame {
            position: relative;
            aspect-ratio: 16 / 9;
            border-radius: 20px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 24px 60px rgba(2, 8, 23, .45);
        }

        .home-video-frame > video,
        .home-video-frame iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }

        .home-video-facade {
            position: absolute;
            inset: 0;
            cursor: pointer;
            border: 0;
            padding: 0;
        }

        .home-video-poster {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .6s cubic-bezier(.2, .8, .2, 1);
        }

        .home-video-poster-fallback {
            background: linear-gradient(135deg, #1e293b, #0f172a);
        }

        .home-video-facade::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(2, 8, 23, .1), rgba(2, 8, 23, .5));
            transition: background .35s ease;
        }

        .home-video-play {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 2;
            width: clamp(64px, 9vw, 84px);
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #0f1f3d;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 12px 36px rgba(2, 8, 23, .4);
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1), background .3s ease;
        }

        /* The play glyph is optically centred by nudging it right. */
        .home-video-play svg { margin-inline-start: 5px; }

        .home-video-facade:hover .home-video-poster,
        .home-video-facade:focus-visible .home-video-poster { transform: scale(1.04); }
        .home-video-facade:hover::after,
        .home-video-facade:focus-visible::after { background: linear-gradient(180deg, rgba(2, 8, 23, .05), rgba(2, 8, 23, .35)); }
        .home-video-facade:hover .home-video-play,
        .home-video-facade:focus-visible .home-video-play { transform: translate(-50%, -50%) scale(1.09); background: #fff; }

        .home-video-facade:focus-visible {
            outline: 3px solid #93c5fd;
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            .home-video-poster,
            .home-video-play { transition: none; }
            .home-video-facade:hover .home-video-poster { transform: none; }
        }

        @media (max-width: 575.98px) {
            .home-video-panel { border-radius: 20px; }
            .home-video-frame { border-radius: 14px; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Swap the poster for the real player only when asked. Until then the
        // page has downloaded one image and nothing else.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-home-video-embed]').forEach(function (facade) {
                function play() {
                    const src = facade.dataset.homeVideoEmbed;
                    if (!src) return;

                    const iframe = document.createElement('iframe');
                    iframe.src = src; // already autoplay=1, so the click carries through
                    iframe.title = facade.getAttribute('aria-label') || 'Video';
                    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                    iframe.referrerPolicy = 'strict-origin-when-cross-origin';
                    iframe.allowFullscreen = true;

                    facade.replaceWith(iframe);
                }

                facade.addEventListener('click', play);
                facade.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        play();
                    }
                });
            });
        });
    </script>
@endpush
