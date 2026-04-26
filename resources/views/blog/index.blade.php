@extends('layouts.app')

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-body p-4 p-lg-5">
                            <div class="row g-4 align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge text-bg-primary rounded-pill px-3 py-2 mb-3">{{ __('blog.insights_badge') }}</span>
                                    <h1 class="display-6 fw-bold mb-3">{{ __('blog.index_heading') }}</h1>
                                    <p class="text-secondary mb-0">{{ __('blog.index_intro') }}</p>
                                </div>
                                <div class="col-lg-4">
                                    <form action="{{ route('blogs.index') }}" method="GET" class="d-grid gap-2">
                                        <label for="blog-search" class="form-label fw-semibold mb-0">{{ __('blog.search_label') }}</label>
                                        <input
                                            id="blog-search"
                                            type="text"
                                            name="search"
                                            value="{{ $search }}"
                                            class="form-control form-control-lg"
                                            placeholder="{{ __('blog.search_placeholder') }}"
                                        >
                                        <button type="submit" class="btn btn-primary btn-lg">{{ __('blog.search_button') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($featuredPosts->count() > 0)
                <div class="row g-4 mb-5">
                    @foreach($featuredPosts as $featuredPost)
                        <div class="col-lg-4">
                            <article class="card h-100 border-0 shadow-sm overflow-hidden">
                                <a href="{{ route('blogs.show', $featuredPost) }}" class="text-decoration-none text-reset">
                                    <img
                                        src="{{ $featuredPost->image_url }}"
                                        alt="{{ $featuredPost->localized_featured_image_alt }}"
                                        class="w-100"
                                        style="height: 220px; object-fit: cover;"
                                    >
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge text-bg-light border">{{ __('blog.featured_label') }}</span>
                                            <span class="small text-secondary">{{ $featuredPost->published_date }}</span>
                                        </div>
                                        <h2 class="h4 mb-2">{{ $featuredPost->localized_title }}</h2>
                                        <p class="text-secondary mb-0">{{ $featuredPost->localized_excerpt }}</p>
                                    </div>
                                </a>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="row g-4">
                @forelse($posts as $post)
                    <div class="col-md-6 col-xl-4">
                        <article class="card h-100 border-0 shadow-sm overflow-hidden">
                            <a href="{{ route('blogs.show', $post) }}" class="text-decoration-none text-reset h-100 d-flex flex-column">
                                <img
                                    src="{{ $post->image_url }}"
                                    alt="{{ $post->localized_featured_image_alt }}"
                                    class="w-100"
                                    style="height: 220px; object-fit: cover;"
                                >
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                                        @if($post->category)
                                            <span class="badge text-bg-light border">{{ $post->category->translated_name ?? $post->category->name }}</span>
                                        @endif
                                        <span class="small text-secondary">{{ $post->published_date }}</span>
                                    </div>
                                    <h2 class="h4 mb-3">{{ $post->localized_title }}</h2>
                                    <p class="text-secondary flex-grow-1">{{ $post->localized_excerpt }}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="fw-semibold text-primary">{{ __('blog.read_more') }}</span>
                                        <span class="small text-secondary">{{ $post->author?->name ?? config('app.name') }}</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-5 text-center">
                                <h2 class="h4 mb-3">{{ __('blog.empty_title') }}</h2>
                                <p class="text-secondary mb-0">{{ __('blog.empty_description') }}</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($posts->hasPages())
                <div class="mt-5">
                    {{ $posts->links('components.global-pagination') }}
                </div>
            @endif
        </div>
    </section>
@endsection
