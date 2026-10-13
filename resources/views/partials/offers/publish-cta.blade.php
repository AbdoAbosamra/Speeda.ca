{{-- "Are you a provider? Publish your offer" — opens a WhatsApp chat with SPEEDA. --}}
@php
    $waUrl = \App\Support\WhatsApp::contactSpeedaUrl();
@endphp
@if($waUrl)
    <div class="so-publish so-scope">
        <div class="so-publish-icon" aria-hidden="true"><i class="fab fa-whatsapp"></i></div>
        <div class="so-publish-text">
            <b>{{ __('offers.publish_title') }}</b>
            <p>{{ __('offers.publish_text') }}</p>
            <bdi class="so-fine">{{ \App\Support\WhatsApp::display(\App\Support\WhatsApp::speedaNumber()) }}</bdi>
        </div>
        <a class="so-btn so-btn-wa" href="{{ $waUrl }}" target="_blank" rel="noopener">
            <i class="fab fa-whatsapp" aria-hidden="true"></i> {{ __('offers.contact_on_whatsapp') }}
        </a>
    </div>
@endif
