@extends('emails.provider.layout')

@section('content')
    <span class="step-badge">Step 4 of 6</span>
    <h2 class="main-headline">Experience Wins Customers – Show Yours 📅</h2>
    
    <p class="lead-text">
        You're building an incredible profile, {{ $providerName }}! Let's strengthen your authority and customer trust by highlighting your professional background.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">Action Item</p>
        <h4 class="next-step-title">Add Your Years of Experience</h4>
        <p class="next-step-desc">
            Let customers know how long you've been working in your trade. Every year counts toward demonstrating your capability and dedication to quality.
        </p>
    </div>

    <div class="why-card">
        <p class="why-label">💡 Why It Matters</p>
        <p class="why-text">
            Experience is one of the top factors customers evaluate when comparing service providers. Explicitly stating your experience builds immediate trust and removes uncertainty.
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button">Add My Experience</a>
        <span class="cta-subtext">Takes only a single click to select</span>
    </div>

    <div class="progress-section">
        <p class="progress-label">Onboarding Progress</p>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: 50%"></div>
        </div>
        <span class="progress-text">Step 3 of 6 complete (50%)</span>
    </div>

    <blockquote class="closing-quote">
        "Experience builds confidence—and confident customers are far more likely to hire."
    </blockquote>
@endsection
