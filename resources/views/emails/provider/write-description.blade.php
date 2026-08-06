@extends('emails.provider.layout')

@section('content')
    <span class="step-badge">Step 3 of 6</span>
    <h2 class="main-headline">Your Story Is Your Greatest Sales Tool 📝</h2>
    
    <p class="lead-text">
        Fantastic work, {{ $providerName }}! Your photo and services are set. Now, it's time to build a real connection with customers by sharing what makes your business unique and reliable.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">Action Item</p>
        <h4 class="next-step-title">Write a Professional Business Description</h4>
        <p class="next-step-desc">
            Introduce yourself, highlight your expertise, outline your standards of quality, and tell customers why choosing you is their best option.
        </p>
    </div>

    <div class="why-card">
        <p class="why-label">💡 Why It Matters</p>
        <p class="why-text">
            A clear and honest description answers common customer questions upfront, builds immediate confidence, and differentiates you from generic competitors.
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button">Write My Description</a>
        <span class="cta-subtext">A short paragraph of 3-4 sentences is perfect</span>
    </div>

    <div class="progress-section">
        <p class="progress-label">Onboarding Progress</p>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: 33%"></div>
        </div>
        <span class="progress-text">Step 2 of 6 complete (33%)</span>
    </div>

    <blockquote class="closing-quote">
        "Your story is often the reason customers choose you over someone else."
    </blockquote>
@endsection
