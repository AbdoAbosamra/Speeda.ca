@extends('emails.provider.layout')

@section('content')
    <span class="step-badge">Step 5 of 6</span>
    <h2 class="main-headline">Show, Don't Just Tell – Let Your Work Speak 🖼️</h2>
    
    <p class="lead-text">
        You're doing awesome, {{ $providerName }}! Your profile is almost complete. Now let's let your actual craftsmanship do the talking by showcasing visual proof of your best work.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">Action Item</p>
        <h4 class="next-step-title">Add Photos to Your Work Gallery</h4>
        <p class="next-step-desc">
            Upload clear before-and-after photos, pictures of your tools, completed projects, or your team on site. Let clients see the quality they are paying for!
        </p>
    </div>

    <div class="why-card">
        <p class="why-label">💡 Why It Matters</p>
        <p class="why-text">
            A picture is worth a thousand words. Profiles with a gallery see massive increases in user engagement. Clients love visualizing the quality of work they can expect.
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button">Upload My Photos</a>
        <span class="cta-subtext">Add photos directly from your phone's camera roll</span>
    </div>

    <div class="progress-section">
        <p class="progress-label">Onboarding Progress</p>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: 67%"></div>
        </div>
        <span class="progress-text">Step 4 of 6 complete (67%)</span>
    </div>

    <blockquote class="closing-quote">
        "Your work tells a story that words alone never can."
    </blockquote>
@endsection
