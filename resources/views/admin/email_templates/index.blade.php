@extends('layouts.app')

@section('title', __('admin.email_templates'))

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Emails"
                :title="__('admin.email_templates')"
                :subtitle="__('admin.email_templates_subtitle')"
            >
                <x-slot:actions>
                    <x-ui.button
                        :href="route('admin.email_journey.index')"
                        variant="secondary"
                        icon="fas fa-envelope-open-text"
                        class="admin-btn admin-btn-secondary"
                    >
                        Email Journey
                    </x-ui.button>
                </x-slot:actions>
            </x-admin.header>

            @foreach($groups as $groupKey => $groupLabel)
                @php($rows = $templates->where('group', $groupKey))
                @if($rows->isNotEmpty())
                    <h3 class="etpl-group-title">{{ $groupLabel }}</h3>

                    <x-admin.table-card>
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Subject line</th>
                                    <th>Status</th>
                                    <th>Last edited</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $t)
                                    <tr>
                                        <td>
                                            <div class="admin-table-title">{{ $t['label'] }}</div>
                                            <div class="admin-table-subtitle"><code>{{ $t['key'] }}</code></div>
                                        </td>
                                        <td>
                                            <div class="etpl-subject">{{ $t['subject'] }}</div>
                                        </td>
                                        <td>
                                            @if($t['customised'] && $t['is_active'])
                                                <span class="etpl-badge etpl-badge-custom">
                                                    <i class="fas fa-pen"></i> {{ __('admin.email_customised') }}
                                                </span>
                                            @else
                                                <span class="etpl-badge etpl-badge-default">
                                                    <i class="fas fa-box"></i> {{ __('admin.email_default_copy') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($t['updated_at'])
                                                <span title="{{ $t['updated_at'] }}">{{ $t['updated_at']->diffForHumans() }}</span>
                                                @if($t['updated_by'])
                                                    <div class="admin-table-subtitle">{{ $t['updated_by'] }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="admin-row-actions">
                                                <a href="{{ route('admin.email_templates.edit', $t['key']) }}"
                                                   class="admin-icon-action admin-icon-primary">
                                                    <i class="fas fa-pen"></i><span>Edit</span>
                                                </a>
                                                <a href="{{ route('admin.email_templates.preview', $t['key']) }}"
                                                   class="admin-icon-action" target="_blank" rel="noopener">
                                                    <i class="fas fa-eye"></i><span>{{ __('admin.email_preview') }}</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-admin.table-card>
                @endif
            @endforeach
        </div>
    </div>

    <style>
        .etpl-group-title {
            font-size: 0.78rem; font-weight: 800; letter-spacing: 0.1em;
            text-transform: uppercase; color: #94a3b8;
            margin: 1.75rem 0 0.75rem;
        }
        .etpl-group-title:first-of-type { margin-top: 0; }
        .etpl-subject { color: #334155; font-size: 0.9rem; }
        .etpl-badge {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.22rem 0.65rem; border-radius: 99px;
            font-size: 0.76rem; font-weight: 700; white-space: nowrap;
        }
        .etpl-badge-custom { background: rgba(99, 102, 241, 0.12); color: #4338ca; }
        .etpl-badge-default { background: #f1f5f9; color: #64748b; }
        .admin-table-subtitle code {
            background: #f1f5f9; padding: 0.1rem 0.35rem;
            border-radius: 5px; font-size: 0.75rem; color: #475569;
        }
    </style>
@endsection
