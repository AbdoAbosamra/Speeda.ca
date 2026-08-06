@extends('emails.provider.layout')

@section('content')
    <span class="step-badge">Step 1 of 6</span>
    <h2 class="main-headline">One Photo Can Change Everything 📸</h2>
    
    <p class="lead-text">
        Welcome back, {{ $providerName }}! Your profile is live, but it's missing the single most powerful trust builder: **your professional photo**. Customers want to see the face behind the business before they reach out.
    </p>

    <div class="stats-strip">
        <div class="stat-cell">
            <span class="stat-number">3x</span>
            <span class="stat-label">More Views</span>
        </div>
        <div class="stat-cell">
            <span class="stat-number">2x</span>
            <span class="stat-label">More Leads</span>
        </div>
        <div class="stat-cell">
            <span class="stat-number">30s</span>
            <span class="stat-label">To Complete</span>
        </div>
    </div>

    <div class="next-step-card">
        <p class="next-step-label">Action Item</p>
        <h4 class="next-step-title">Upload a High-Quality Profile Photo</h4>
        <p class="next-step-desc">
            A friendly, professional photo makes your profile instantly look credible, making customers significantly more likely to choose you over a generic icon.
        </p>
    </div>

    <div class="why-card">
        <p class="why-label">💡 Why It Matters</p>
        <p class="why-text">
            Trust is the ultimate currency. In a local marketplace, profiles with genuine, high-quality photos establish an immediate personal connection and feel more reliable.
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button">Add My Profile Photo</a>
        <span class="cta-subtext">Quickly upload from your phone or desktop</span>
    </div>

    <div class="progress-section">
        <p class="progress-label">Onboarding Progress</p>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: 0%"></div>
        </div>
        <span class="progress-text">Step 0 of 6 complete (0%)</span>
    </div>

    <blockquote class="closing-quote">
        "Every great business starts with a great first impression."
    </blockquote>
@endsection
