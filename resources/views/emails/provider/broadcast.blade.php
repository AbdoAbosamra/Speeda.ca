@extends('emails.provider.layout')

{{--
    Admin-composed broadcast.

    $copy['body'] is the ONLY unescaped value in the whole email system. It is
    admin-authored HTML from the dashboard editor and has already passed through
    AdminHtml::clean() twice — once on save, once in the Mailable — so no active
    content, event handler, or javascript: URL can survive to this point.

    $copy           subject / preheader / body / cta_label / cta_url
    $unsubscribeUrl signed, per-recipient opt-out link (CASL)
--}}

@section('footer_note')
    You're receiving this because you registered as a service provider on Speeda.<br>
    <a href="{{ $unsubscribeUrl }}" style="color: rgba(255,255,255,0.55); text-decoration: underline;">
        Unsubscribe from these emails
    </a>
@endsection

@section('content')
    {{-- Preheader: the grey preview line mail clients show next to the subject.
         Hidden in the body itself so it is never rendered twice. --}}
    @if(!empty($copy['preheader']))
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;height:0;width:0;">
            {{ $copy['preheader'] }}
        </div>
    @endif

    <div class="broadcast-body" style="font-size:16px;line-height:1.7;color:#374151;">
        {!! $copy['body'] !!}
    </div>

    @if(!empty($copy['cta_label']) && !empty($copy['cta_url']))
        <div class="cta-container" style="margin-top:32px;">
            <a href="{{ $copy['cta_url'] }}" class="cta-button">{{ $copy['cta_label'] }}</a>
        </div>
    @endif
@endsection
