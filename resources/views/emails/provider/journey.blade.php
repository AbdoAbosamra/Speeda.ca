@extends('emails.provider.layout')

@section('footer_note', $footerNote ?? "You're receiving this because you registered as a service provider on Speeda.")

{{--
    Content-driven journey email.

    Every string comes from EmailTemplate::resolve(), which returns the admin's
    saved copy or the built-in default. The markup and inlined styles are
    unchanged from the original per-email templates, so the design is identical
    — only the words are now editable from the dashboard.

    $c            resolved content (plain text; escaped here by Blade)
    $dashboardUrl CTA target
    $stats        optional [['value' => '3x', 'label' => 'More Views'], ...]
    $progress     optional ['label' => ..., 'text' => ..., 'percent' => 0-100]
--}}

@section('content')
    @if(!empty($c['badge']))
        <span class="step-badge">{{ $c['badge'] }}</span>
    @endif

    <h2 class="main-headline">{{ $c['headline'] }}</h2>

    @if(!empty($c['lead']))
        <p class="lead-text">{{ $c['lead'] }}</p>
    @endif

    @if(!empty($stats))
        <div class="stats-strip">
            @foreach($stats as $stat)
                <div class="stat-cell">
                    <span class="stat-number">{{ $stat['value'] }}</span>
                    <span class="stat-label">{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($c['next_step_title']) || !empty($c['next_step_desc']))
        <div class="next-step-card">
            @if(!empty($c['next_step_label']))
                <p class="next-step-label">{{ $c['next_step_label'] }}</p>
            @endif
            @if(!empty($c['next_step_title']))
                <h4 class="next-step-title">{{ $c['next_step_title'] }}</h4>
            @endif
            @if(!empty($c['next_step_desc']))
                <p class="next-step-desc">{{ $c['next_step_desc'] }}</p>
            @endif
        </div>
    @endif

    @if(!empty($c['why_text']))
        <div class="why-card">
            @if(!empty($c['why_label']))
                <p class="why-label">{{ $c['why_label'] }}</p>
            @endif
            <p class="why-text">{{ $c['why_text'] }}</p>
        </div>
    @endif

    @if(!empty($c['cta_label']))
        <div class="cta-container">
            <a href="{{ $dashboardUrl }}" class="cta-button">{{ $c['cta_label'] }}</a>
            @if(!empty($c['cta_subtext']))
                <span class="cta-subtext">{{ $c['cta_subtext'] }}</span>
            @endif
        </div>
    @endif

    @if(!empty($progress))
        <div class="progress-section">
            <p class="progress-label">{{ $progress['label'] }}</p>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" style="width: {{ (int) $progress['percent'] }}%"></div>
            </div>
            <span class="progress-text">{{ $progress['text'] }}</span>
        </div>
    @endif

    @if(!empty($c['quote']))
        <blockquote class="closing-quote">{{ $c['quote'] }}</blockquote>
    @endif
@endsection
