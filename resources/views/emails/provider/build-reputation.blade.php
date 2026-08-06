@extends('emails.provider.layout')

@section('content')
    <span class="step-badge" style="background: linear-gradient(90deg, #F59E0B, #D97706);">Next Level: Reputation</span>
    <h2 class="main-headline">You've Built the Profile. Now Build the Trust. ⭐</h2>
    
    <p class="lead-text">
        Congrats again on your completed profile, {{ $providerName }}! Now it's time to trigger the single most powerful factor in a customer's hiring decision: **authentic customer reviews**.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">Action Item</p>
        <h4 class="next-step-title">Ask Your Customers to Leave Their First Review</h4>
        <p class="next-step-desc">
            Even one genuine 5-star review makes your profile stand out, boosts your search ranking, and gives future clients the peace of mind to click "Contact".
        </p>
    </div>

    <div class="why-card">
        <p class="why-label">💡 Why It Matters</p>
        <p class="why-text">
            Modern clients rely on word of mouth. By showcasing happy customer reviews, you let your history of great service do the selling for you.
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button">Request My First Review</a>
        <span class="cta-subtext">Share your review link directly with past clients</span>
    </div>

    <blockquote class="closing-quote">
        "Every great reputation starts with a single satisfied customer."
    </blockquote>
@endsection
