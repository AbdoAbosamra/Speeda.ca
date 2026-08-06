@extends('emails.provider.layout')

@section('footer_note', "You're receiving this because you have an account on Speeda.")

@section('content')
    <span class="step-badge">🌟 First Review!</span>
    <h2 class="main-headline">Thank you, {{ $userName }}!</h2>

    <p class="lead-text">
        You just shared your very first review on Speeda — and it means a lot. Honest reviews like yours help other people find trustworthy local service providers, and they help great businesses get the recognition they deserve.
    </p>

    <div class="next-step-card">
        <p class="next-step-label">You're Making a Difference</p>
        <h4 class="next-step-title">Every review helps someone choose better</h4>
        <p class="next-step-desc">
            Your experience is now visible to the community and guides others toward the right provider. Keep exploring and sharing — your voice matters here.
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $browseUrl }}" class="cta-button">Discover More Providers</a>
        <span class="cta-subtext">Find and review the pros you work with</span>
    </div>

    <blockquote class="closing-quote">
        "The smallest act of sharing your experience can guide someone to exactly the help they were looking for."
    </blockquote>
@endsection
