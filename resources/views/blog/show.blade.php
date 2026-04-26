@extends('layouts.app')

@push('json-ld')
    <script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <article class="card border-0 shadow-sm overflow-hidden">
                        <img
                            src="{{ $post->image_url }}"
                            alt="{{ $post->localized_featured_image_alt }}"
                            class="w-100"
                            style="max-height: 520px; object-fit: cover;"
                        >

                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                @if($post->category)
                                    <span class="badge text-bg-light border">{{ $post->category->translated_name ?? $post->category->name }}</span>
                                @endif
                                <span class="small text-secondary">{{ $post->published_date }}</span>
                                <span class="small text-secondary">{{ __('blog.by_author', ['name' => $post->author?->name ?? config('app.name')]) }}</span>
                                @if($post->reading_time_minutes)
                                    <span class="small text-secondary">{{ __('blog.reading_time', ['minutes' => $post->reading_time_minutes]) }}</span>
                                @endif
                            </div>

                            <h1 class="display-6 fw-bold mb-3">{{ $post->localized_title }}</h1>

                            @if($post->localized_excerpt)
                                <p class="lead text-secondary mb-4">{{ $post->localized_excerpt }}</p>
                            @endif

                            <div class="blog-content fs-5 lh-lg">
                                {!! $post->localized_content !!}
                            </div>
                        </div>
                    </article>
                </div>
            </div>

            @if($relatedPosts->count() > 0)
                <div class="row justify-content-center mt-5">
                    <div class="col-xl-10">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h2 class="h3 mb-0">{{ __('blog.related_heading') }}</h2>
                            <a href="{{ route('blogs.index') }}" class="fw-semibold">{{ __('blog.view_all') }}</a>
                        </div>

                        <div class="row g-4">
                            @foreach($relatedPosts as $relatedPost)
                                <div class="col-md-4">
                                    <article class="card h-100 border-0 shadow-sm overflow-hidden">
                                        <a href="{{ route('blogs.show', $relatedPost) }}" class="text-decoration-none text-reset h-100 d-flex flex-column">
                                            <img
                                                src="{{ $relatedPost->image_url }}"
                                                alt="{{ $relatedPost->localized_featured_image_alt }}"
                                                class="w-100"
                                                style="height: 200px; object-fit: cover;"
                                            >
                                            <div class="card-body p-4 d-flex flex-column">
                                                <span class="small text-secondary mb-2">{{ $relatedPost->published_date }}</span>
                                                <h3 class="h5 mb-2">{{ $relatedPost->localized_title }}</h3>
                                                <p class="text-secondary mb-0 flex-grow-1">{{ $relatedPost->localized_excerpt }}</p>
                                            </div>
                                        </a>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
