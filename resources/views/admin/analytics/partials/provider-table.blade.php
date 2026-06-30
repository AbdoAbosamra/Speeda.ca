@php
    use Illuminate\Support\Carbon;
    $showConversion = $showConversion ?? true;
    $showLastClick = $showLastClick ?? false;
    $suggestAction = $suggestAction ?? false;

    $convClass = function ($rate) {
        if ($rate >= 15) return 'wa-conv-good';
        if ($rate >= 5) return 'wa-conv-mid';
        return 'wa-conv-bad';
    };
@endphp

<section class="admin-section-block">
    <div class="admin-section-heading">
        <div>
            <p class="admin-section-eyebrow">{{ $eyebrow }}</p>
            <h2 class="admin-section-title">{{ $title }}</h2>
        </div>
    </div>
    <div class="wa-panel">
        <div class="wa-table-wrap">
            <table class="wa-table">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Views</th>
                        <th>WhatsApp Clicks</th>
                        @if($showConversion)<th>Conversion</th>@endif
                        @if($showLastClick)<th>Last Click</th>@endif
                        @if($suggestAction)<th>Suggested Action</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>
                                <a href="{{ route('admin.provider_activity_monitor.show', $row['id']) }}" class="wa-link">
                                    {{ $row['company_name'] }}
                                </a>
                            </td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['location'] }}</td>
                            <td>{{ number_format($row['views']) }}</td>
                            <td>{{ number_format($row['clicks']) }}</td>
                            @if($showConversion)
                                <td><strong class="{{ $convClass($row['conversion_rate']) }}">{{ $row['conversion_rate'] }}%</strong></td>
                            @endif
                            @if($showLastClick)
                                <td>{{ !empty($row['last_click_at']) ? Carbon::parse($row['last_click_at'])->diffForHumans() : '—' }}</td>
                            @endif
                            @if($suggestAction)
                                <td>
                                    @if($row['clicks'] == 0)
                                        <span class="wa-pill" style="background:#fef2f2;color:#b91c1c;">Check WhatsApp number</span>
                                    @else
                                        <span class="wa-pill" style="background:#fffbeb;color:#b45309;">Improve profile / CTA</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="wa-empty">{{ $emptyText ?? 'No data available.' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
