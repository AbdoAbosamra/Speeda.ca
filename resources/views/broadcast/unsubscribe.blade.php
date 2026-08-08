@extends('layouts.app')

@section('content')
    <div class="unsubscribe-page">
        <div class="unsubscribe-card">
            @if(!empty($justResubscribed))
                <i class="fas fa-circle-check unsubscribe-icon is-good"></i>
                <h1>You're subscribed again</h1>
                <p>You'll keep receiving news and updates from Speeda at <strong>{{ $user->email }}</strong>.</p>
            @elseif($alreadyOptedOut)
                <i class="fas fa-circle-check unsubscribe-icon is-good"></i>
                <h1>{{ !empty($justUnsubscribed) ? 'You have been unsubscribed' : 'You are already unsubscribed' }}</h1>
                <p>
                    We will no longer send news or announcement emails to <strong>{{ $user->email }}</strong>.
                </p>
                <p class="unsubscribe-note">
                    You will still receive essential account emails — password resets and notifications about
                    your own reviews — because your account depends on them.
                </p>

                <form action="{{ URL::signedRoute('broadcast.resubscribe', ['user' => $user->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="unsubscribe-btn is-secondary">
                        Changed your mind? Resubscribe
                    </button>
                </form>
            @else
                <i class="fas fa-envelope-circle-check unsubscribe-icon"></i>
                <h1>Unsubscribe from Speeda emails</h1>
                <p>
                    Stop sending news and announcements to <strong>{{ $user->email }}</strong>?
                </p>
                <p class="unsubscribe-note">
                    Essential account emails — password resets and notifications about your own reviews —
                    will keep arriving.
                </p>

                <form action="{{ URL::signedRoute('broadcast.unsubscribe.confirm', ['user' => $user->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="unsubscribe-btn">Yes, unsubscribe me</button>
                </form>

                <a href="{{ route('home') }}" class="unsubscribe-link">No thanks, take me to Speeda</a>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .unsubscribe-page { min-height: 62vh; display: flex; align-items: center; justify-content: center; padding: 48px 20px; background: #F0F4FA; }
        .unsubscribe-card { max-width: 520px; width: 100%; background: #fff; border-radius: 20px; padding: 44px 40px; text-align: center; box-shadow: 0 4px 40px rgba(15,31,61,.12); }
        .unsubscribe-icon { font-size: 3rem; color: #3B82F6; margin-bottom: 18px; }
        .unsubscribe-icon.is-good { color: #16a34a; }
        .unsubscribe-card h1 { font-size: 1.6rem; font-weight: 800; color: #0F1F3D; margin: 0 0 14px; }
        .unsubscribe-card p { color: #475569; line-height: 1.7; margin: 0 0 14px; }
        .unsubscribe-note { font-size: .88rem; color: #64748b; background: #f8fafc; border-radius: 10px; padding: 14px 16px; }
        .unsubscribe-btn { display: inline-block; margin-top: 10px; border: 0; background: linear-gradient(135deg, #1D4ED8, #0F1F3D); color: #fff; font-weight: 700; padding: 14px 34px; border-radius: 100px; cursor: pointer; }
        .unsubscribe-btn.is-secondary { background: #fff; color: #1D4ED8; border: 1.5px solid #cbd5e1; }
        .unsubscribe-link { display: inline-block; margin-top: 16px; color: #64748b; font-size: .88rem; }
    </style>
@endpush
