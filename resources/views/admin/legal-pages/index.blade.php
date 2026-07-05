@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Legal CMS"
                title="Policies & Privacy"
                subtitle="Manage public legal pages in English, Arabic, and French without editing Blade files."
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.legal-pages.create')"
                        icon="fas fa-plus"
                        class="admin-btn admin-btn-primary text-white"
                    >
                        Create Legal Page
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            <x-admin.metric-grid>
                <x-admin.metric label="CMS Pages" :value="$counts['total']" />
                <x-admin.metric label="Published" :value="$counts['published']" />
                <x-admin.metric label="Drafts" :value="$counts['draft']" />
            </x-admin.metric-grid>

            <section class="admin-section-block">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-section-eyebrow">Core Legal Pages</p>
                        <h2 class="admin-section-title">Existing Website Links</h2>
                    </div>
                </div>

                <x-admin.table-card>
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>Page</th>
                                <th>Public Slug</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($defaultPages as $default)
                                @php($override = $default['override'])
                                <tr>
                                    <td>
                                        <div class="admin-table-title">{{ $default['title_en'] }}</div>
                                        <div class="admin-table-subtitle">{{ $default['title_ar'] }} · {{ $default['title_fr'] }}</div>
                                    </td>
                                    <td><code>/{{ $default['slug'] }}</code></td>
                                    <td>
                                        @if($override)
                                            <x-ui.badge variant="primary" icon="fas fa-database">CMS override</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral" icon="fas fa-file-code">Static fallback</x-ui.badge>
                                        @endif
                                    </td>
                                    <td>
                                        @if($override)
                                            <x-ui.badge :variant="$override->isPublished() ? 'success' : 'warning'">
                                                {{ ucfirst($override->status) }}
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="success">Live</x-ui.badge>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="admin-row-actions">
                                            <a href="{{ $default['public_url'] }}" class="admin-icon-action" target="_blank" rel="noopener">
                                                <i class="fas fa-arrow-up-right-from-square"></i>
                                                <span>View</span>
                                            </a>
                                            @if($override)
                                                <a href="{{ route('admin.legal-pages.edit', $override) }}" class="admin-icon-action">
                                                    <i class="fas fa-pen"></i>
                                                    <span>Edit</span>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.legal-pages.create', ['slug' => $default['slug']]) }}" class="admin-icon-action">
                                                    <i class="fas fa-wand-magic-sparkles"></i>
                                                    <span>Customize</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-admin.table-card>
            </section>

            <section class="admin-section-block">
                <form action="{{ route('admin.legal-pages.index') }}" method="GET" class="admin-filter-bar">
                    <label class="admin-filter-field">
                        <span>Search</span>
                        <input type="search" name="search" value="{{ $search }}" placeholder="Search title or slug">
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
                        <x-ui.button :href="route('admin.legal-pages.index')" variant="secondary" class="admin-btn admin-btn-secondary">
                            Reset
                        </x-ui.button>
                    </div>
                </form>
            </section>

            <x-admin.table-card>
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                            <tr>
                                <td>
                                    <div class="admin-table-title">{{ $page->title_en }}</div>
                                    <div class="admin-table-subtitle">{{ $page->title_ar }} · {{ $page->title_fr }}</div>
                                </td>
                                <td><code>/{{ $page->slug }}</code></td>
                                <td>{{ str_replace('_', ' ', ucfirst($page->page_type)) }}</td>
                                <td>
                                    <x-ui.badge :variant="$page->isPublished() ? 'success' : 'warning'">
                                        {{ ucfirst($page->status) }}
                                    </x-ui.badge>
                                </td>
                                <td>{{ optional($page->updated_at)->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="admin-row-actions">
                                        @if($page->isPublished())
                                            <a href="{{ $page->public_url }}" class="admin-icon-action" target="_blank" rel="noopener">
                                                <i class="fas fa-arrow-up-right-from-square"></i>
                                                <span>View</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.legal-pages.edit', $page) }}" class="admin-icon-action">
                                            <i class="fas fa-pen"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form action="{{ route('admin.legal-pages.destroy', $page) }}" method="POST" onsubmit="return confirm('Delete this legal CMS page? Core pages will fall back to their static Blade version.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-icon-action admin-icon-danger">
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
                                    <x-admin.empty-state
                                        icon="fas fa-scale-balanced"
                                        title="No CMS legal pages yet"
                                        description="Use Customize for the existing privacy/terms pages, or create a new legal page."
                                    >
                                        <x-slot:actions>
                                            <x-ui.button :href="route('admin.legal-pages.create')" class="admin-btn admin-btn-primary text-white">
                                                Create Legal Page
                                            </x-ui.button>
                                        </x-slot:actions>
                                    </x-admin.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table-card>

            @if($pages->hasPages())
                <div class="admin-pagination-wrap">
                    {{ $pages->links('components.global-pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection
