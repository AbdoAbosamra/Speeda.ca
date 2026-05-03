@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Blog CMS</p>
                    <h1>Create Blog</h1>
                    <p>Write a new draft or publish an article directly to the public blog.</p>
                </div>
                <a href="{{ route('admin.blog.posts.index') }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Blogs</span>
                </a>
            </section>

            @include('admin.blog.posts.partials.form', [
                'action' => route('admin.blog.posts.store'),
                'method' => 'POST',
                'post' => $post,
                'categories' => $categories,
            ])
        </div>
    </div>
@endsection
