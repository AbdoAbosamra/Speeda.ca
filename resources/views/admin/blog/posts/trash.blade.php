@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Blog CMS"
                title="Deleted Blog Posts"
                subtitle="Soft-deleted articles. Restore one to bring it back to the blog list."
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.blog.posts.index')"
                        variant="secondary"
                        icon="fas fa-arrow-left"
                        class="admin-btn admin-btn-secondary"
                    >
                        Back to Blogs
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            <x-admin.table-card>
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Deleted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <td>
                                    <div class="admin-table-title">{{ $post->title_en ?: $post->title }}</div>
                                    <div class="admin-table-subtitle">{{ $post->slug }}</div>
                                </td>
                                <td>
                                    <x-ui.badge variant="warning" class="admin-badge admin-badge-draft">
                                        {{ ucfirst($post->status ?: 'draft') }}
                                    </x-ui.badge>
                                </td>
                                <td>{{ optional($post->deleted_at)->diffForHumans() ?: '—' }}</td>
                                <td>
                                    <div class="admin-row-actions">
                                        <form action="{{ route('admin.blog.posts.restore', $post->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="admin-icon-action" title="Restore blog">
                                                <i class="fas fa-undo"></i>
                                                <span>Restore</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-admin.empty-state
                                        icon="fas fa-trash"
                                        title="Trash is empty"
                                        description="Deleted blog posts will appear here and can be restored."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table-card>

            @if($posts->hasPages())
                <div class="admin-pagination-wrap">
                    {{ $posts->links('components.global-pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection
