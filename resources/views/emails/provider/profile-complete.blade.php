@extends('emails.provider.layout')

@section('content')
    <h2 class="main-headline">🏆 You're a Speeda All-Star, {{ $providerName }}!</h2>
    
    <p class="lead-text">
        ✨ 🎉 Confetti time! You have successfully completed your profile. You are now officially ready to attract and impress customers as a fully verified, optimized service provider on Speeda.
    </p>

    <div class="next-step-card" style="background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%); border-color: #A7F3D0;">
        <p class="next-step-label" style="color: #059669;">Achievement Unlocked</p>
        <h4 class="next-step-title">100% Completed Profile!</h4>
        <p class="next-step-desc">
            Your profile has everything a customer looks for: a professional photo, detailed services, years of experience, a compelling bio, and service areas. Well done!
        </p>
    </div>

    <div class="cta-container">
        <a href="{{ $dashboardUrl }}" class="cta-button" style="background: linear-gradient(135deg, #10B981 0%, #064E3B 100%); box-shadow: 0 8px 32px rgba(16, 185, 129, 0.35);">View My Live Profile</a>
        <span class="cta-subtext">See what customers see when searching for you</span>
    </div>

    <blockquote class="closing-quote" style="border-left-color: #10B981;">
        "A complete profile creates immediate customer trust and stands out significantly from the competition."
    </blockquote>
@endsection
