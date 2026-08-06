@extends('emails.provider.layout')

@section('content')
    <span class="step-badge">Step 6 of 6 – Final Step!</span>
    <h2 class="main-headline">Expand Your Reach – More Areas = More Customers 🌍</h2>
    
    <p class="lead-text">
        You are at the finish line, {{ $providerName }}! Your profile is looking absolutely top-tier. Let's take the final step to define everywhere you are available to work, so we can connect you with as many local clients as possible.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">Action Item</p>
        <h4 class="next-step-title">Specify Your Active Service Areas</h4>
        <p class="next-step-desc">
            Select the cities, towns, or specific radius where you provide services. This ensures we show your business to the right search queries based on location.
        </p>
    </div>

    <div class="why-card">
        <p class="why-label">💡 Why It Matters</p>
        <p class="why-text">
            If a customer searches for your services in a nearby suburb but you haven't explicitly added it to your areas, you won't show up. Multiply your lead potential by listing all regions you cover!
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button">Update My Service Areas</a>
        <span class="cta-subtext">Add all surrounding cities you serve in seconds</span>
    </div>

    <div class="progress-section">
        <p class="progress-label">Onboarding Progress</p>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: 83%"></div>
        </div>
        <span class="progress-text">Step 5 of 6 complete (83%)</span>
    </div>

    <blockquote class="closing-quote">
        "The more places you serve, the more opportunities you create."
    </blockquote>
@endsection
