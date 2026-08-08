@extends('layouts.app')

@section('content')
    <div class="admin-cms-page" dir="ltr">
        <div class="container-fluid px-4 px-xl-5 py-4 py-lg-5">
            <section class="admin-page-header">
                <div>
                    <p class="admin-section-eyebrow">Provider Emails</p>
                    <h1>{{ $broadcast->subject }}</h1>
                    <p>
                        Sent by {{ $broadcast->author?->name ?? 'a removed admin' }}
                        @if($broadcast->queued_at)
                            on {{ $broadcast->queued_at->format('M j, Y \a\t H:i') }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.broadcasts.index') }}" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Emails</span>
                </a>
            </section>

            {{-- Live delivery progress, polled while the queue drains --}}
            <div class="admin-form-card" id="broadcast-progress-card"
                 data-progress-url="{{ route('admin.broadcasts.progress', $broadcast) }}"
                 data-finished="{{ $broadcast->isFinished() ? '1' : '0' }}">
                <div class="admin-form-section-head">
                    <h2>Delivery</h2>
                </div>

                <div class="broadcast-stat-row">
                    <div class="broadcast-stat">
                        <span class="broadcast-stat-value" data-stat="total">{{ number_format($broadcast->recipients_total) }}</span>
                        <span class="broadcast-stat-label">Recipients</span>
                    </div>
                    <div class="broadcast-stat">
                        <span class="broadcast-stat-value text-success" data-stat="sent">{{ number_format($broadcast->sent_count) }}</span>
                        <span class="broadcast-stat-label">Delivered</span>
                    </div>
                    <div class="broadcast-stat">
                        <span class="broadcast-stat-value text-danger" data-stat="failed">{{ number_format($broadcast->failed_count) }}</span>
                        <span class="broadcast-stat-label">Failed</span>
                    </div>
                </div>

                <div class="broadcast-progress-bar">
                    <div class="broadcast-progress-fill" data-progress-fill
                         style="width: {{ $broadcast->progressPercent() }}%"></div>
                </div>
                <p class="broadcast-note" data-progress-text>
                    {{ $broadcast->progressPercent() }}% complete
                    @unless($broadcast->isFinished())
                        — this page updates itself while the queue sends.
                    @endunless
                </p>
            </div>

            <div class="admin-form-card mt-4">
                <div class="admin-form-section-head">
                    <h2>Message</h2>
                </div>
                <div class="broadcast-archive">
                    {!! $broadcast->body !!}
                </div>
            </div>

            <div class="admin-form-card mt-4">
                <div class="admin-form-section-head">
                    <h2>Recipients</h2>
                    <p>Failed rows show the reason returned by the mail server.</p>
                </div>

                <div class="broadcast-filter-row">
                    @foreach(['' => 'All', 'sent' => 'Delivered', 'failed' => 'Failed', 'pending' => 'Pending'] as $value => $label)
                        <a href="{{ route('admin.broadcasts.show', ['broadcast' => $broadcast, 'status' => $value]) }}"
                           class="admin-btn admin-btn-sm {{ $status === $value ? 'admin-btn-primary text-white' : 'admin-btn-secondary' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <table class="admin-data-table mt-3">
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipients as $recipient)
                            <tr>
                                <td>{{ $recipient->serviceProvider?->company_name ?: ($recipient->name ?: '—') }}</td>
                                <td>{{ $recipient->email }}</td>
                                <td>
                                    @if($recipient->status === \App\Models\ProviderBroadcastRecipient::STATUS_SENT)
                                        <span class="badge bg-success">Delivered</span>
                                    @elseif($recipient->status === \App\Models\ProviderBroadcastRecipient::STATUS_FAILED)
                                        <span class="badge bg-danger">Failed</span>
                                        @if($recipient->error)
                                            <div class="text-muted small">{{ Str::limit($recipient->error, 120) }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>{{ optional($recipient->sent_at)->format('M j, H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No recipients match this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $recipients->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .broadcast-stat-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 18px; }
        .broadcast-stat { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; }
        .broadcast-stat-value { display: block; font-size: 1.8rem; font-weight: 800; color: #0F1F3D; line-height: 1; }
        .broadcast-stat-label { display: block; margin-top: 6px; font-size: .8rem; color: #64748b; }
        .broadcast-progress-bar { background: #e2e8f0; border-radius: 100px; height: 10px; overflow: hidden; }
        .broadcast-progress-fill { height: 10px; border-radius: 100px; background: linear-gradient(90deg, #1D4ED8, #3B82F6); transition: width .4s ease; }
        .broadcast-note { font-size: .84rem; color: #64748b; margin: 10px 0 0; }
        .broadcast-filter-row { display: flex; gap: 8px; flex-wrap: wrap; }
        .broadcast-archive { border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; line-height: 1.7; color: #374151; }
        .broadcast-archive img { max-width: 100%; height: auto; }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const card = document.getElementById('broadcast-progress-card');
            if (!card || card.dataset.finished === '1') return;

            const url = card.dataset.progressUrl;
            const fill = card.querySelector('[data-progress-fill]');
            const text = card.querySelector('[data-progress-text]');
            const stat = (name) => card.querySelector(`[data-stat="${name}"]`);

            // Polls only while a send is still in flight, and stops for good the
            // moment it finishes — no timer left running on an idle page.
            const timer = setInterval(async () => {
                try {
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;

                    const data = await res.json();
                    stat('total').textContent = data.total.toLocaleString();
                    stat('sent').textContent = data.sent.toLocaleString();
                    stat('failed').textContent = data.failed.toLocaleString();
                    fill.style.width = data.percent + '%';
                    text.textContent = data.percent + '% complete';

                    if (data.finished) {
                        clearInterval(timer);
                        text.textContent = 'Delivery complete.';
                    }
                } catch (e) {
                    // A transient network blip should not kill the poller.
                }
            }, 4000);
        })();
    </script>
@endpush
