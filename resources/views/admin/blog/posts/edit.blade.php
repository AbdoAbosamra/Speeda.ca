@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4" style="padding-top: 4rem; padding-bottom: 3rem;">
        <div class="mb-6">
            <h1 class="display-6 fw-bold text-dark mb-2">✏️ {{ __('Edit Blog Post') }}</h1>
            <p class="text-muted">{{ __('Update your blog post details') }}</p>
            <div style="height: 3px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); width: 80px; border-radius: 2px; margin-top: 1rem;"></div>
        </div>
        @include('admin.blog.posts.partials.form', [
            'action' => route('admin.blog.posts.update', $post),
            'method' => 'PUT',
            'post' => $post,
            'categories' => $categories,
            'statuses' => $statuses,
        ])
    </div>
@endsection
