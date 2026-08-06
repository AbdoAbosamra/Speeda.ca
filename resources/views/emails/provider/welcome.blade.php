@extends('emails.provider.layout')

@section('content')
    <span class="step-badge">🎉 You're In!</span>
    <h2 class="main-headline">Welcome to Speeda, {{ $providerName }}!</h2>
    
    <p class="lead-text">
        We're thrilled to welcome you to our growing network of top-rated local service providers! Your profile is now officially live, opening the doors for local customers to discover and connect with your business.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">Your Next Move</p>
        <h4 class="next-step-title">Visit Your Dashboard & Claim Your First Step</h4>
        <p class="next-step-desc">
            To help you succeed, we've designed a quick step-by-step path to optimize your profile. Let's make sure you stand out from day one!
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button">Open My Dashboard</a>
        <span class="cta-subtext">Takes less than 30 seconds to begin</span>
    </div>

    <div class="progress-section">
        <p class="progress-label">Onboarding Progress</p>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: 0%"></div>
        </div>
        <span class="progress-text">Step 0 of 6 complete (0%)</span>
    </div>

    <blockquote class="closing-quote">
        "Your online presence is your modern storefront. A complete profile builds customer confidence before they even contact you."
    </blockquote>
@endsection
