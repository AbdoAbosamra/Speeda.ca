@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Blog CMS</p>
                    <h1>Blog Management</h1>
                    <p>Create, search, edit, publish, and safely delete public blog articles.</p>
                </div>
                <a href="{{ route('admin.blog.posts.create') }}" class="admin-btn admin-btn-primary text-white">
                    <i class="fas fa-plus"></i>
                    <span>Create Blog</span>
                </a>
            </section>

            <div class="admin-mini-stat-grid">
                <article><span>Total</span><strong>{{ $counts['total'] }}</strong></article>
                <article><span>Published</span><strong>{{ $counts['published'] }}</strong></article>
                <article><span>Drafts</span><strong>{{ $counts['draft'] }}</strong></article>
            </div>

            <section class="admin-section-block">
                <form action="{{ route('admin.blog.posts.index') }}" method="GET" class="admin-filter-bar">
                    <label class="admin-filter-field">
                        <span>Search</span>
                        <input type="search" name="search" value="{{ $search }}" placeholder="Search by title or slug">
                    </label>
                    <label class="admin-filter-field">
                        <span>Status</span>
                        <select name="status">
                            <option value="">All statuses</option>
                            <option value="draft" @selected($status === 'draft')>Draft</option>
                            <option value="published" @selected($status === 'published')>Published</option>
                        </select>
                    </label>
                    <div class="admin-filter-actions">
                        <button type="submit" class="admin-btn admin-btn-primary text-white">
                            <i class="fas fa-magnifying-glass"></i>
                            <span>Apply</span>
                        </button>
                        <a href="{{ route('admin.blog.posts.index') }}" class="admin-btn admin-btn-secondary">Reset</a>
                    </div>
                </form>
            </section>

            <section class="admin-table-card">
                <div class="table-responsive">
                    <table class="admin-data-table">
                        <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Published</th>
                                    <th>Created Date</th>
                                    <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($posts as $post)
                                @php($postStatus = $post->status ?: ($post->is_published ? 'published' : 'draft'))
                                <tr>
                                    <td>
                                        <img src="{{ $post->image_url }}" alt="{{ $post->title_en ?: $post->title }}" class="admin-table-thumb" loading="lazy">
                                    </td>
                                    <td>
                                        <div class="admin-table-title">{{ $post->title_en ?: $post->title }}</div>
                                        <div class="admin-table-subtitle">{{ $post->slug }}</div>
                                    </td>
                                    <td>
                                        <span class="admin-badge admin-badge-{{ $postStatus === 'published' ? 'published' : 'draft' }}">
                                            {{ ucfirst($postStatus) }}
                                        </span>
                                    </td>
                                    <td>{{ optional($post->published_at)->format('M d, Y') ?: '-' }}</td>
                                    <td>{{ optional($post->created_at)->format('M d, Y') }}</td>
                                    <td>
                                        <div class="admin-row-actions">
                                            @if($postStatus === 'published')
                                                <a href="{{ route('blogs.show', $post) }}" class="admin-icon-action" title="View public blog" target="_blank" rel="noopener">
                                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                                    <span>View</span>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.blog.posts.edit', $post) }}" class="admin-icon-action" title="Edit blog">
                                                <i class="fas fa-pen"></i>
                                                <span>Edit</span>
                                            </a>
                                            <form action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Delete this blog post? It will be soft deleted and hidden from the public site.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-icon-action admin-icon-danger" title="Delete blog">
                                                    <i class="fas fa-trash"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="admin-empty-state">
                                            <i class="fas fa-newspaper"></i>
                                            <h2>No blog posts found</h2>
                                            <p>Create a blog post or adjust the current filters.</p>
                                            <a href="{{ route('admin.blog.posts.create') }}" class="admin-btn admin-btn-primary text-white">Create Blog</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if($posts->hasPages())
                <div class="admin-pagination-wrap">
                    {{ $posts->links('components.global-pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection
