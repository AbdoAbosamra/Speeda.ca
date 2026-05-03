@extends('layouts.app')

@section('head')
    @include('seo.meta', ['seo' => $seo ?? null])
@endsection

@section('content')
    <section class="py-8 py-lg-12 bg-light">
        <div class="container">
            <!-- Header Section -->
            <div class="mb-8 pb-6 border-bottom">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-7">
                        <span class="badge text-bg-primary rounded-pill px-3 py-2 mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">{{ __('blog.insights_badge') }}</span>
                        <h1 class="display-5 fw-bold text-dark mb-3" style="line-height: 1.3;">{{ __('blog.index_heading') }}</h1>
                        <p class="fs-5 text-muted mb-0">{{ __('blog.index_intro') }}</p>
                        <div class="mt-4" style="height: 3px; background: linear-gradient(90deg, #667eea 0%, transparent 100%); width: 60px; border-radius: 2px;"></div>
                    </div>
                    <div class="col-lg-5">
                        <form action="{{ route('blogs.index') }}" method="GET" class="d-grid gap-3">
                            <label for="blog-search" class="form-label fw-bold mb-0">🔍 {{ __('blog.search_label') }}</label>
                            <div class="input-group input-group-lg">
                                <input
                                    id="blog-search"
                                    type="text"
                                    name="search"
                                    value="{{ $search }}"
                                    class="form-control"
                                    style="border-radius: 8px 0 0 8px; border: 1px solid #e0e0e0;"
                                    placeholder="{{ __('blog.search_placeholder') }}"
                                >
                                <button type="submit" class="btn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 0 8px 8px 0; border: none; font-weight: 600;">{{ __('blog.search_button') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Featured Posts Section -->
            @if($featuredPosts->count() > 0)
                <div class="mb-8 pb-6 border-bottom">
                    <h2 class="h4 fw-bold text-dark mb-4">⭐ {{ __('blog.featured_label') }}</h2>
                    <div class="row g-4">
                        @foreach($featuredPosts as $featuredPost)
                            <div class="col-lg-4">
                                <article class="card h-100 border-0 shadow-sm overflow-hidden transition" style="border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 16px 32px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''">
                                    <a href="{{ route('blogs.show', $featuredPost) }}" class="text-decoration-none text-reset">
                                        <div style="position: relative; overflow: hidden; height: 240px;">
                                            <img
                                                src="{{ $featuredPost->image_url }}"
                                                alt="{{ $featuredPost->localized_featured_image_alt }}"
                                                class="w-100"
                                                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                                                onmouseover="this.style.transform='scale(1.08)'"
                                                onmouseout="this.style.transform='scale(1)'"
                                            >
                                        </div>
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                                <span class="badge text-bg-warning">{{ __('blog.featured_label') }}</span>
                                                <span class="small text-muted">📅 {{ $featuredPost->published_date }}</span>
                                            </div>
                                            <h2 class="h5 fw-bold text-dark mb-2" style="line-height: 1.4;">{{ $featuredPost->localized_title }}</h2>
                                            <p class="text-muted mb-0">{{ $featuredPost->localized_excerpt }}</p>
                                        </div>
                                    </a>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Posts Grid -->
            <h2 class="h4 fw-bold text-dark mb-5">📚 {{ __('blog.latest_posts') ?? 'Latest Posts' }}</h2>
            <div class="row g-4">
                @forelse($posts as $post)
                    <div class="col-md-6 col-lg-4">
                        <article class="card h-100 border-0 shadow-sm overflow-hidden transition" style="border-radius: 12px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 16px 32px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''">
                            <a href="{{ route('blogs.show', $post) }}" class="text-decoration-none text-reset h-100 d-flex flex-column">
                                <div style="position: relative; overflow: hidden; height: 240px;">
                                    <img
                                        src="{{ $post->image_url }}"
                                        alt="{{ $post->localized_featured_image_alt }}"
                                        class="w-100"
                                        style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                                        onmouseover="this.style.transform='scale(1.08)'"
                                        onmouseout="this.style.transform='scale(1)'"
                                    >
                                    @if($post->category)
                                        <div style="position: absolute; top: 12px; right: 12px; background: rgba(102, 126, 234, 0.95); color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                            {{ $post->category->translated_name ?? $post->category->name }}
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                        <span class="small text-muted" style="display: flex; align-items: center; gap: 4px;"><span>📅</span> {{ $post->published_date }}</span>
                                    </div>
                                    <h3 class="h5 fw-bold text-dark mb-2" style="line-height: 1.4; min-height: 2.8rem;">{{ $post->localized_title }}</h3>
                                    <p class="text-muted flex-grow-1 mb-4">{{ $post->localized_excerpt }}</p>
                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                        <span class="small text-muted" style="display: flex; align-items: center; gap: 4px;"><span>✍️</span> {{ $post->author?->name ?? config('app.name') }}</span>
                                        <span class="fw-bold text-primary" style="display: flex; align-items: center; gap: 4px;">{{ __('blog.read_more') }} →</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 3rem;">
                            <div class="text-center">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                                <h2 class="h4 fw-bold text-dark mb-2">{{ __('blog.empty_title') }}</h2>
                                <p class="text-muted mb-0">{{ __('blog.empty_description') }}</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($posts->hasPages())
                <div class="mt-8 d-flex justify-content-center">
                    {{ $posts->links('components.global-pagination') }}
                </div>
            @endif
        </div>
    </section>
@endsection

