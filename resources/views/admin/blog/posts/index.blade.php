@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Blog CMS"
                title="Blog Management"
                subtitle="Create, search, edit, publish, and safely delete public blog articles."
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.blog.posts.trash')"
                        variant="secondary"
                        icon="fas fa-trash"
                        class="admin-btn admin-btn-secondary"
                    >
                        Trash
                    </x-ui.button>
                    <x-ui.button
                        :href="route('admin.blog.posts.create')"
                        icon="fas fa-plus"
                        class="admin-btn admin-btn-primary text-white"
                    >
                        Create Blog
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            <x-admin.metric-grid>
                <x-admin.metric label="Total" :value="$counts['total']" />
                <x-admin.metric label="Published" :value="$counts['published']" />
                <x-admin.metric label="Drafts" :value="$counts['draft']" />
            </x-admin.metric-grid>

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
                        <x-ui.button type="submit" icon="fas fa-magnifying-glass" class="admin-btn admin-btn-primary text-white">
                            Apply
                        </x-ui.button>
                        <x-ui.button :href="route('admin.blog.posts.index')" variant="secondary" class="admin-btn admin-btn-secondary">
                            Reset
                        </x-ui.button>
                    </div>
                </form>
            </section>

            <x-admin.bulk-form
                :action="route('admin.blog.posts.bulk')"
                label="posts"
                :actions="[
                    'publish' => ['label' => __('admin.publish'), 'icon' => 'fa-upload', 'variant' => 'success'],
                    'draft'   => ['label' => __('admin.unpublish'), 'icon' => 'fa-file-pen', 'variant' => 'warning'],
                    'delete'  => ['label' => __('admin.delete'), 'icon' => 'fa-trash', 'variant' => 'danger', 'confirm' => __('admin.bulk_confirm_delete')],
                ]"
            >
            <x-admin.table-card>
                    <table class="admin-data-table">
                        <thead>
                                <tr>
                                    <th style="width:1%;"><x-admin.bulk-checkbox master /></th>
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
                                    <td><x-admin.bulk-checkbox :value="$post->id" /></td>
                                    <td>
                                        <img src="{{ $post->image_url }}" alt="{{ $post->title_en ?: $post->title }}" class="admin-table-thumb" loading="lazy">
                                    </td>
                                    <td>
                                        <div class="admin-table-title">{{ $post->title_en ?: $post->title }}</div>
                                        <div class="admin-table-subtitle">{{ $post->slug }}</div>
                                    </td>
                                    <td>
                                        <x-ui.badge
                                            :variant="$postStatus === 'published' ? 'success' : 'warning'"
                                            class="admin-badge admin-badge-{{ $postStatus === 'published' ? 'published' : 'draft' }}"
                                        >
                                            {{ ucfirst($postStatus) }}
                                        </x-ui.badge>
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
                                    <td colspan="7">
                                        <x-admin.empty-state
                                            icon="fas fa-newspaper"
                                            title="No blog posts found"
                                            description="Create a blog post or adjust the current filters."
                                        >
                                            <x-slot:actions>
                                                <x-ui.button :href="route('admin.blog.posts.create')" class="admin-btn admin-btn-primary text-white">
                                                    Create Blog
                                                </x-ui.button>
                                            </x-slot:actions>
                                        </x-admin.empty-state>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
            </x-admin.table-card>
            </x-admin.bulk-form>

            @if($posts->hasPages())
                <div class="admin-pagination-wrap">
                    {{ $posts->links('components.global-pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection
