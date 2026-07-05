@extends('layouts.app')

@push('json-ld')
    <script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@push('styles')
    <style>
        /* Typographic styles for the rich-text article body (rendered HTML). */
        .blog-content > *:first-child { margin-top: 0; }
        .blog-content h2 { font-size: 1.7rem; font-weight: 700; color: #1f2937; margin: 2rem 0 1rem; line-height: 1.3; }
        .blog-content h3 { font-size: 1.35rem; font-weight: 700; color: #1f2937; margin: 1.75rem 0 .85rem; line-height: 1.35; }
        .blog-content h4 { font-size: 1.15rem; font-weight: 700; color: #374151; margin: 1.5rem 0 .75rem; }
        .blog-content p { margin: 0 0 1.15rem; }
        .blog-content a { color: #2563eb; text-decoration: underline; text-underline-offset: 2px; }
        .blog-content a:hover { color: #1d4ed8; }
        .blog-content ul, .blog-content ol { margin: 0 0 1.15rem; padding-inline-start: 1.6rem; }
        .blog-content li { margin-bottom: .5rem; }
        .blog-content blockquote { margin: 1.5rem 0; padding: .85rem 1.25rem; border-inline-start: 4px solid #2563eb; background: #f8fafc; color: #475569; font-style: italic; border-radius: 6px; }
        .blog-content img { max-width: 100%; height: auto; border-radius: 10px; margin: 1.25rem 0; }
        .blog-content table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
        .blog-content th, .blog-content td { border: 1px solid #e5e7eb; padding: .6rem .85rem; text-align: start; }
        .blog-content th { background: #f9fafb; font-weight: 700; }
        .blog-content hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0; }
    </style>
@endpush

@section('head')
    @include('seo.meta', ['seo' => $seo ?? null])
@endsection

@section('content')
    <section class="py-8 py-lg-12">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Article Header -->
                    <article class="card border-0 shadow-lg overflow-hidden mb-8" style="border-radius: 12px;">
                        <div style="position: relative; overflow: hidden; height: 520px;">
                            <img
                                src="{{ $post->image_url }}"
                                alt="{{ $post->localized_featured_image_alt }}"
                                class="w-100"
                                loading="eager"
                                decoding="async"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            >
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 120px; background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.3) 100%);"></div>
                        </div>

                        <div class="card-body p-5 p-lg-7" style="background: #ffffff;">
                            <!-- Meta Information -->
                            <div class="d-flex flex-wrap gap-3 align-items-center mb-4 pb-4 border-bottom">
                                @if($post->category)
                                    <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                        {{ $post->category->translated_name ?? $post->category->name }}
                                    </span>
                                @endif

                                <span class="small text-muted" style="display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-calendar"></i> {{ $post->published_date }}
                                </span>

                                <span class="small text-muted" style="display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-user"></i> {{ __('blog.by_author', ['name' => $post->author?->name ?? config('app.name')]) }}
                                </span>

                                @if($post->reading_time_minutes)
                                    <span class="small text-muted" style="display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-clock"></i> {{ __('blog.reading_time', ['minutes' => $post->reading_time_minutes]) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Title -->
                            <h1 class="display-5 fw-bold text-dark mb-4" style="line-height: 1.3;">{{ $post->localized_title }}</h1>

                            <!-- Excerpt -->
                            @if($post->localized_excerpt)
                                <p class="lead text-muted mb-6" style="font-size: 1.1rem; line-height: 1.6;">{{ $post->localized_excerpt }}</p>
                            @endif

                            <!-- Content -->
                            @php
                                $articleBody = $post->localized_content;
                                // New posts are authored as HTML in the editor; legacy posts are plain text.
                                $bodyIsHtml = $articleBody !== strip_tags($articleBody);
                            @endphp
                            <div class="blog-content" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" style="font-size: 1.05rem; line-height: 1.8; color: #495057;">
                                @if($bodyIsHtml)
                                    {!! $articleBody !!}
                                @else
                                    {!! nl2br(e($articleBody)) !!}
                                @endif
                            </div>
                        </div>
                    </article>

                    <!-- Related Posts Section -->
                    @if($relatedPosts->count() > 0)
                        <section class="mt-8 pt-6 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-6">
                                <h2 class="h3 fw-bold text-dark mb-0">{{ __('blog.related_heading') }}</h2>
                                <a href="{{ route('blogs.index') }}" class="fw-bold text-primary text-decoration-none" style="display: flex; align-items: center; gap: 6px;">{{ __('blog.view_all') }} <i class="fas fa-arrow-right"></i></a>
                            </div>

                            <div class="row g-4">
                                @foreach($relatedPosts as $relatedPost)
                                    <div class="col-md-6 col-lg-4">
                                        <article class="card h-100 border-0 shadow-sm overflow-hidden transition" style="border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 16px 32px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''">
                                            <a href="{{ route('blogs.show', $relatedPost) }}" class="text-decoration-none text-reset h-100 d-flex flex-column">
                                                <div style="position: relative; overflow: hidden; height: 220px;">
                                                    <img
                                                        src="{{ $relatedPost->image_url }}"
                                                        alt="{{ $relatedPost->localized_featured_image_alt }}"
                                                        class="w-100"
                                                        loading="lazy"
                                                        decoding="async"
                                                        style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                                                        onmouseover="this.style.transform='scale(1.08)'"
                                                        onmouseout="this.style.transform='scale(1)'"
                                                    >
                                                </div>
                                                <div class="card-body p-4 d-flex flex-column">
                                                    <span class="small text-muted mb-2" style="display: flex; align-items: center; gap: 4px;">{{ $relatedPost->published_date }}</span>
                                                    <h3 class="h5 fw-bold text-dark mb-2" style="line-height: 1.4;">{{ $relatedPost->localized_title }}</h3>
                                                    <p class="text-muted mb-0 flex-grow-1">{{ $relatedPost->localized_excerpt }}</p>
                                                </div>
                                            </a>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
