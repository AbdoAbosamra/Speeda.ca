@extends('layouts.app')

@section('title', 'Email Journey Timeline – Admin')

@push('styles')
<style>
    .ej-page { padding: 32px 0; }

    .ej-back-btn {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 14px; font-weight: 600; color: #64748B;
        text-decoration: none; margin-bottom: 24px;
        transition: color 0.2s;
    }
    .ej-back-btn:hover { color: #0F1F3D; }

    .ej-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        padding: 32px;
        margin-bottom: 28px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }

    .ej-card-title { font-size: 18px; font-weight: 700; color: #0F1F3D; margin: 0 0 24px; }

    /* Provider Header */
    .ej-profile-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
        flex-wrap: wrap;
        border-bottom: 1px solid #F1F5F9;
        padding-bottom: 24px;
        margin-bottom: 28px;
    }
    .ej-profile-info h1 { font-size: 24px; font-weight: 800; color: #0F1F3D; margin: 0 0 6px; }
    .ej-profile-info p { font-size: 14px; color: #64748B; margin: 0; }

    /* Checklist Grid */
    .ej-checklist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .ej-check-card {
        background: #F8FAFC;
        border: 1.5px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .ej-check-card.is-done { border-color: #A7F3D0; background: #ECFDF5; }
    .ej-check-icon { font-size: 20px; }
    .ej-check-label { font-size: 13px; font-weight: 600; color: #374151; }

    /* Timeline */
    .ej-timeline { position: relative; padding-left: 32px; margin-top: 10px; }
    .ej-timeline::before {
        content: '';
        position: absolute;
        top: 8px; bottom: 8px; left: 7px;
        width: 2px; background: #E2E8F0;
    }
    .ej-timeline-item { position: relative; margin-bottom: 32px; }
    .ej-timeline-item:last-child { margin-bottom: 0; }
    .ej-timeline-dot {
        position: absolute;
        top: 4px; left: -31px;
        width: 16px; height: 16px;
        border-radius: 50%; background: #CBD5E1;
        border: 3px solid #fff; box-shadow: 0 0 0 2px #E2E8F0;
    }
    .ej-timeline-item.is-sent .ej-timeline-dot { background: #3B82F6; box-shadow: 0 0 0 2px #DBEAFE; }
    .ej-timeline-item.is-completed .ej-timeline-dot { background: #10B981; box-shadow: 0 0 0 2px #D1FAE5; }

    .ej-timeline-time { font-size: 12px; font-weight: 600; color: #94A3B8; margin-bottom: 4px; }
    .ej-timeline-content {
        background: #F8FAFC;
        border-radius: 12px;
        padding: 16px 20px;
        border: 1px solid #E2E8F0;
    }
    .ej-timeline-title { font-size: 15px; font-weight: 700; color: #0F1F3D; margin: 0 0 6px; }
    .ej-timeline-desc { font-size: 13px; color: #475569; margin: 0 0 10px; }

    .ej-badge {
        display: inline-flex; align-items: center;
        padding: 4px 10px; border-radius: 100px;
        font-size: 12px; font-weight: 600;
    }
    .ej-badge-blue   { background: #DBEAFE; color: #1D4ED8; }
    .ej-badge-green  { background: #DCFCE7; color: #15803D; }
    .ej-badge-amber  { background: #FEF3C7; color: #B45309; }
    .ej-badge-gray   { background: #F1F5F9; color: #475569; }

    /* Button */
    .ej-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 100px; font-size: 14px; font-weight: 600;
        text-decoration: none; border: none; cursor: pointer; transition: all 0.2s;
    }
    .ej-btn-primary { background: linear-gradient(135deg, #1D4ED8 0%, #0F1F3D 100%); color: #fff; }
    .ej-btn-primary:hover { opacity: 0.9; color: #fff; }
</style>
@endpush

@section('content')
<div class="ej-page">
    <div class="container px-4">

        {{-- Back Button --}}
        <a href="{{ route('admin.email_journey.index') }}" class="ej-back-btn">
            <i class="fas fa-arrow-left"></i> Back to Email Journey Dashboard
        </a>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:12px;">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Provider Info Card --}}
        <div class="ej-card">
            <div class="ej-profile-header">
                <div class="ej-profile-info">
                    <h1>{{ $serviceProvider->company_name ?? $serviceProvider->user?->name ?? 'Unknown' }}</h1>
                    <p><i class="fas fa-envelope"></i> {{ $serviceProvider->user?->email }} · Registered {{ $serviceProvider->created_at->format('M d, Y') }} ({{ $serviceProvider->created_at->diffForHumans() }})</p>
                </div>
                <div>
                    <span class="ej-badge {{ $serviceProvider->profile_completion_percent >= 100 ? 'ej-badge-green' : 'ej-badge-amber' }}" style="font-size: 15px; padding: 6px 16px;">
                        Profile Complete: {{ $serviceProvider->profile_completion_percent }}%
                    </span>
                </div>
            </div>

            <h3 class="ej-card-title">🔍 Onboarding Checklist Status</h3>
            <div class="ej-checklist-grid">
                @foreach([
                    ['label'=>'Profile Photo','done'=>$timeline['snapshot']['has_photo'],'emoji'=>'📸'],
                    ['label'=>'Services Selected','done'=>$timeline['snapshot']['has_services'],'emoji'=>'🛠️'],
                    ['label'=>'Business Bio','done'=>$timeline['snapshot']['has_bio'],'emoji'=>'📝'],
                    ['label'=>'Years of Experience','done'=>$timeline['snapshot']['has_experience'],'emoji'=>'📅'],
                    ['label'=>'Gallery Photos','done'=>$timeline['snapshot']['has_gallery'],'emoji'=>'🖼️'],
                    ['label'=>'Service Areas','done'=>$timeline['snapshot']['has_service_areas'],'emoji'=>'🌍'],
                    ['label'=>'Customer Reviews','done'=>$timeline['snapshot']['has_reviews'],'emoji'=>'⭐'],
                ] as $item)
                    <div class="ej-check-card {{ $item['done'] ? 'is-done' : '' }}">
                        <span class="ej-check-icon">{{ $item['done'] ? '✅' : '❌' }}</span>
                        <span class="ej-check-label">{{ $item['emoji'] }} {{ $item['label'] }}</span>
                    </div>
                @endforeach
            </div>

            @if($timeline['next_email'])
                <div class="alert alert-info d-flex justify-content-between align-items-center mb-0" style="border-radius:12px; background-color:#EFF6FF; border-color:#BFDBFE; color:#1E40AF;">
                    <div>
                        <i class="fas fa-info-circle"></i> <strong>Next Up in Onboarding:</strong> 
                        {{ $emailTypeLabels[$timeline['next_email']] ?? $timeline['next_email'] }} email
                    </div>
                    <form action="{{ route('admin.email_journey.send_test', $serviceProvider) }}" method="POST"
                          onsubmit="return confirm('This sends a REAL email to {{ addslashes($serviceProvider->user?->email ?? 'this provider') }}. Continue?');">
                        @csrf
                        <input type="hidden" name="expected_type" value="{{ $timeline['next_email'] }}">
                        <button type="submit" class="ej-btn ej-btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Next Email Now
                        </button>
                    </form>
                </div>
            @else
                <div class="alert alert-success d-flex align-items-center mb-0" style="border-radius:12px; background-color:#ECFDF5; border-color:#A7F3D0; color:#065F46;">
                    <i class="fas fa-check-circle me-2"></i> Provider has completed all onboarding phases!
                </div>
            @endif
        </div>

        {{-- Email Timeline Card --}}
        <div class="ej-card">
            <h3 class="ej-card-title">⏰ Email Timeline & Journey History</h3>
            
            @if(count($timeline['logs']) > 0)
                <div class="ej-timeline">
                    @foreach($timeline['logs'] as $log)
                        @php
                            $isCompleted = !is_null($log->completed_at);
                        @endphp
                        <div class="ej-timeline-item {{ $isCompleted ? 'is-completed' : 'is-sent' }}">
                            <div class="ej-timeline-dot"></div>
                            <div class="ej-timeline-time">
                                Sent {{ $log->sent_at->format('M d, Y \a\t h:i A') }} ({{ $log->sent_at->diffForHumans() }})
                            </div>
                            <div class="ej-timeline-content">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <h4 class="ej-timeline-title">
                                        {{ $emailTypeLabels[$log->email_type] ?? $log->email_type }} Email
                                    </h4>
                                    <div>
                                        <span class="ej-badge ej-badge-blue">Attempt #{{ $log->attempt_number }}</span>
                                        @if($isCompleted)
                                            <span class="ej-badge ej-badge-green"><i class="fas fa-check"></i> Action Completed</span>
                                        @else
                                            <span class="ej-badge ej-badge-amber">Action Pending</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="ej-timeline-desc">
                                    This email urges the provider to complete their profile setup step.
                                </p>
                                <div class="text-muted" style="font-size: 12px;">
                                    @if($isCompleted)
                                        <i class="fas fa-calendar-check text-success"></i> Provider completed this action on {{ $log->completed_at->format('M d, Y \a\t h:i A') }}
                                    @elseif($log->next_send_at)
                                        <i class="fas fa-clock text-warning"></i> Next resend scheduled for: {{ $log->next_send_at->format('M d, Y \a\t h:i A') }} ({{ $log->next_send_at->diffForHumans() }})
                                    @else
                                        <i class="fas fa-ban text-danger"></i> No further resends scheduled (max attempts reached or welcome).
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <span style="font-size: 48px; display:block; margin-bottom:12px;">✉️</span>
                    No emails have been dispatched to this provider yet.
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
