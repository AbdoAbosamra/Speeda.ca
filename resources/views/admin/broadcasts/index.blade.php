@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <x-admin.header
                eyebrow="Provider Emails"
                title="Email All Providers"
                subtitle="Write a one-off email and send it to every active service provider."
            >
                <x-slot:actions>
                    <a href="{{ route('admin.broadcasts.create') }}" class="admin-btn admin-btn-primary text-white">
                        <i class="fas fa-plus"></i>
                        <span>Compose Email</span>
                    </a>
                </x-slot:actions>
            </x-admin.header>

            <div class="broadcast-stat-row">
                <div class="broadcast-stat">
                    <span class="broadcast-stat-value">{{ number_format($audienceCount) }}</span>
                    <span class="broadcast-stat-label">Providers reachable now</span>
                </div>
                <div class="broadcast-stat">
                    <span class="broadcast-stat-value">{{ number_format($counts['sent']) }}</span>
                    <span class="broadcast-stat-label">Emails sent</span>
                </div>
                <div class="broadcast-stat">
                    <span class="broadcast-stat-value">{{ number_format($counts['draft']) }}</span>
                    <span class="broadcast-stat-label">Drafts</span>
                </div>
            </div>

            <x-admin.table-card>
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Delivery</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($broadcasts as $broadcast)
                            <tr>
                                <td>
                                    <strong>{{ $broadcast->subject }}</strong>
                                    @if($broadcast->preheader)
                                        <div class="text-muted small">{{ Str::limit($broadcast->preheader, 80) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badge = match($broadcast->status) {
                                            \App\Models\ProviderBroadcast::STATUS_SENT => ['success', 'Sent'],
                                            \App\Models\ProviderBroadcast::STATUS_SENDING => ['info', 'Sending'],
                                            \App\Models\ProviderBroadcast::STATUS_QUEUED => ['info', 'Queued'],
                                            default => ['secondary', 'Draft'],
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge[0] }}">{{ $badge[1] }}</span>
                                </td>
                                <td>
                                    @if($broadcast->status === \App\Models\ProviderBroadcast::STATUS_DRAFT)
                                        <span class="text-muted">—</span>
                                    @else
                                        <span>{{ number_format($broadcast->sent_count) }} / {{ number_format($broadcast->recipients_total) }}</span>
                                        @if($broadcast->failed_count > 0)
                                            <span class="badge bg-danger ms-1">{{ number_format($broadcast->failed_count) }} failed</span>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $broadcast->author?->name ?? '—' }}</td>
                                <td>{{ optional($broadcast->sent_at ?: $broadcast->created_at)->format('M j, Y H:i') }}</td>
                                <td class="text-end">
                                    @if($broadcast->isEditable())
                                        <a href="{{ route('admin.broadcasts.edit', $broadcast) }}" class="admin-btn admin-btn-sm admin-btn-secondary">
                                            <i class="fas fa-pen"></i>
                                            <span>Edit</span>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.broadcasts.show', $broadcast) }}" class="admin-btn admin-btn-sm admin-btn-secondary">
                                            <i class="fas fa-chart-simple"></i>
                                            <span>Report</span>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    No emails yet. Use <strong>Compose Email</strong> to write your first one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-admin.table-card>

            <div class="mt-3">
                {{ $broadcasts->links() }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .broadcast-stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 22px; }
        .broadcast-stat { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 22px; }
        .broadcast-stat-value { display: block; font-size: 1.9rem; font-weight: 800; color: #0F1F3D; line-height: 1; }
        .broadcast-stat-label { display: block; margin-top: 6px; font-size: .82rem; color: #64748b; }
    </style>
@endpush
