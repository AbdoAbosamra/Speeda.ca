@extends('emails.provider.layout')

@section('content')
    <span class="step-badge">Step 2 of 6</span>
    <h2 class="main-headline">Help Customers Find You Instantly 🛠️</h2>
    
    <p class="lead-text">
        Great progress, {{ $providerName }}! Your profile is looking sharper. Now, let's make sure you appear in search results when local customers look for the exact tasks they need.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">Action Item</p>
        <h4 class="next-step-title">Specify the Services You Provide</h4>
        <p class="next-step-desc">
            Selecting all the specific services you offer ensures Speeda's search algorithm matches you with the right customers. Don't let potential jobs slip by!
        </p>
    </div>

    <div class="why-card">
        <p class="why-label">💡 Why It Matters</p>
        <p class="why-text">
            If you don't list your services, you might remain invisible when customers search. Listing them unlocks direct match traffic and targets clients looking for your exact skillset.
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button">Add My Services</a>
        <span class="cta-subtext">Add your services in just 2 minutes</span>
    </div>

    <div class="progress-section">
        <p class="progress-label">Onboarding Progress</p>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" style="width: 17%"></div>
        </div>
        <span class="progress-text">Step 1 of 6 complete (17%)</span>
    </div>

    <blockquote class="closing-quote">
        "The easier it is for customers to see what you offer, the easier it is for them to hire you."
    </blockquote>
@endsection
