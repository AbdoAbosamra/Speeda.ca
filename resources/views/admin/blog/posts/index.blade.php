@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4" style="padding-top: 4rem; padding-bottom: 3rem;">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="display-6 fw-bold text-dark mb-2">📝 {{ __('Blog Posts') }}</h1>
                    <p class="text-muted">{{ $posts->total() }} {{ __('post(s) available') }}</p>
                </div>
                <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.boxShadow='0 8px 16px rgba(102, 126, 234, 0.4)'; this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow=''; this.style.transform='translateY(0)'">
                    ➕ {{ __('Create Post') }}
                </a>
            </div>
            <div style="height: 3px; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); width: 80px; border-radius: 2px;"></div>
        </div>

        @if($posts->count() > 0)
            <div class="card border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <tr>
                                <th class="px-4 py-3" style="font-weight: 600;">📄 {{ __('Title') }}</th>
                                <th class="px-4 py-3" style="font-weight: 600;">🔖 {{ __('Status') }}</th>
                                <th class="px-4 py-3" style="font-weight: 600;">📁 {{ __('Category') }}</th>
                                <th class="px-4 py-3" style="font-weight: 600;">📅 {{ __('Published') }}</th>
                                <th class="px-4 py-3 text-end" style="font-weight: 600;">⚙️ {{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $post)
                                <tr style="border-bottom: 1px solid #f0f0f0; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8f9ff'" onmouseout="this.style.backgroundColor='transparent'">
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $post->title_en }}</div>
                                        <small class="text-muted">{{ Str::limit($post->excerpt_en ?? '', 60) }}</small>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge" style="background: {{ $post->status->value === 'published' ? '#28a745' : ($post->status->value === 'draft' ? '#ffc107' : '#dc3545') }}; color: white; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                            {{ $post->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span style="background: #e3f2fd; color: #1976d2; padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.9rem; font-weight: 500;">
                                            {{ $post->category?->name_en ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-muted" style="font-size: 0.9rem;">
                                            @if($post->published_at)
                                                <span style="display: flex; align-items: center; gap: 6px;">
                                                    <span>📆</span>
                                                    {{ optional($post->published_at)->format('M d, Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">Not published</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('admin.blog.posts.edit', $post) }}" class="btn btn-sm" style="background: #e3f2fd; color: #1976d2; border: none; padding: 0.5rem 0.9rem; border-radius: 6px; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#bbdefb'; this.style.transform='translateY(-2px)'" onmouseout="this.style.backgroundColor='#e3f2fd'; this.style.transform='translateY(0)'">
                                                ✏️ {{ __('Edit') }}
                                            </a>
                                            <form action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm" style="background: #ffebee; color: #c62828; border: none; padding: 0.5rem 0.9rem; border-radius: 6px; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#ffcdd2'; this.style.transform='translateY(-2px)'" onmouseout="this.style.backgroundColor='#ffebee'; this.style.transform='translateY(0)'">
                                                    🗑️ {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 d-flex justify-content-center">
                {{ $posts->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 4rem 2rem;">
                <div class="text-center">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <h3 class="fw-bold text-dark mb-2">{{ __('No blog posts yet') }}</h3>
                    <p class="text-muted mb-4">{{ __('Create your first blog post to get started') }}</p>
                    <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600;">
                        ➕ {{ __('Create First Post') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
