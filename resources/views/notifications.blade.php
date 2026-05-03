@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="premium-shell home-section-panel fade-in-up">
                <div class="home-section-head mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1 class="section-title mb-1 h2">
                            <i class="fas fa-bell me-2 text-primary"></i>
                            {{ __('admin.notifications') }}
                        </h1>
                        <p class="section-subtitle mb-0">
                            {{ __('general.stay_updated_with_latest_alerts') }}
                        </p>
                    </div>
                    
                    @if($notifications->whereNotIn('id', $readNotificationIds)->isNotEmpty())
                        <form action="{{ route('notifications.mark-as-read') }}" method="POST" id="markAllReadForm">
                            @csrf
                            <button type="submit" class="premium-btn-outline home-inline-action">
                                <i class="fas fa-check-double me-2"></i>
                                {{ __('general.mark_all_read') }}
                            </button>
                        </form>
                    @endif
                </div>

                <div class="notifications-list">
                    @forelse($notifications as $notif)
                        @php $isRead = in_array($notif->id, $readNotificationIds); @endphp
                        <article class="notification-item p-4 mb-3 rounded-xl border {{ $isRead ? 'bg-white' : 'bg-light border-primary-subtle shadow-sm' }}" 
                                 style="transition: all 0.3s ease; position: relative; overflow: hidden;">
                            @if(!$isRead)
                                <div class="read-indicator" style="position: absolute; top: 0; left: 0; bottom: 0; width: 4px; background: var(--speeda-blue);"></div>
                            @endif
                            
                            <div class="d-flex gap-4">
                                <div class="notif-icon-box d-flex align-items-center justify-content-center flex-shrink-0" 
                                     style="width: 48px; height: 48px; border-radius: 12px; background: {{ $isRead ? '#f8fafc' : 'var(--speeda-blue-soft)' }}; color: {{ $isRead ? '#94a3b8' : 'var(--speeda-blue)' }};">
                                    <i class="fas {{ $isRead ? 'fa-envelope-open' : 'fa-envelope' }} fa-lg"></i>
                                </div>
                                
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h3 class="h6 mb-0 fw-bold {{ $isRead ? 'text-secondary' : 'text-dark' }}">
                                            {{ $notif->title }}
                                        </h3>
                                        <span class="small text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $notif->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <p class="mb-0 {{ $isRead ? 'text-muted' : 'text-secondary' }}" style="line-height: 1.6;">
                                        {{ $notif->message }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-bell-slash fa-4x text-muted opacity-25"></i>
                            </div>
                            <h2 class="h4 fw-bold mb-2">{{ __('admin.no_notifications') }}</h2>
                            <p class="text-muted">{{ __('general.all_caught_up') }}</p>
                            <a href="{{ route('home') }}" class="premium-btn-primary mt-3">
                                {{ __('general.back_to_home') }}
                            </a>
                        </div>
                    @endforelse
                </div>

                @if($notifications instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-5">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .notification-item:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--speeda-blue-soft) !important;
    }
</style>
@endsection
