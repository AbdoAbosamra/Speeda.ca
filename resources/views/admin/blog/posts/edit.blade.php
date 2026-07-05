@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Blog CMS</p>
                    <h1>Edit Blog</h1>
                    <p>Update title, slug, content, image, and publishing status.</p>
                </div>
                <a href="{{ route('admin.blog.posts.index') }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Blogs</span>
                </a>
            </section>

            @include('admin.blog.posts.partials.form', [
                'action' => route('admin.blog.posts.update', $post),
                'method' => 'PUT',
                'post' => $post,
                'categories' => $categories,
            ])
        </div>
    </div>
@endsection
