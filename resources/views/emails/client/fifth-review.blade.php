@extends('emails.provider.layout')

@section('footer_note', "You're receiving this because you have an account on Speeda.")

@section('content')
    <span class="step-badge">🏆 5 Reviews Milestone</span>
    <h2 class="main-headline">You're a Speeda star, {{ $userName }}!</h2>

    <p class="lead-text">
        Five reviews — that's a real milestone! You've become one of the trusted voices shaping our community. Thanks to contributors like you, choosing a local service provider on Speeda keeps getting easier and more reliable for everyone.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">Top Contributor</p>
        <h4 class="next-step-title">Your reviews carry real weight</h4>
        <p class="next-step-desc">
            People notice active, honest reviewers. Every review you add builds a stronger, more transparent community — and we're grateful to have you leading the way.
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $browseUrl }}" class="cta-button">Keep Exploring Providers</a>
        <span class="cta-subtext">There's always another great pro to discover</span>
    </div>

    <blockquote class="closing-quote">
        "Great communities are built by people who show up and share — thank you for being one of them."
    </blockquote>
@endsection
