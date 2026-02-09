@props(['type' => 'client'])

@php
    $benefits = [
        'client' => [
            'title' => __('home.client_benefits_title'),
            'promo' => [
                'text' => 'Free — Forever: Access Speeda\'s full features in this version at no cost, for life.',
                'icon' => 'fas fa-star',
                'class' => 'client-promo'
            ],
            'items' => [
                ['title' => __('home.client_benefit1_title'), 'desc' => __('home.client_benefit1_desc')],
                ['title' => __('home.client_benefit2_title'), 'desc' => __('home.client_benefit2_desc')],
                ['title' => __('home.client_benefit3_title'), 'desc' => __('home.client_benefit3_desc')],
                ['title' => __('home.client_benefit4_title'), 'desc' => __('home.client_benefit4_desc')],
                ['title' => __('home.client_benefit5_title'), 'desc' => __('home.client_benefit5_desc')],
            ],
            'closing' => __('home.client_closing'),
            'button' => [
                'text' => __('home.start_project'),
                'route' => route('location'),
                'class' => 'btn-warning',
                'icon' => 'fas fa-rocket'
            ],
            'card_class' => 'client-card'
        ],
        'provider' => [
            'title' => __('home.provider_benefits_title'),
            'promo' => [
                'text' => 'Join Free — Limited Time Offer: Become a service provider today and keep your account free before subscription plans launch.',
                'icon' => 'fas fa-clock',
                'class' => 'provider-promo'
            ],
            'items' => [
                ['title' => __('home.provider_benefit1_title'), 'desc' => __('home.provider_benefit1_desc')],
                ['title' => __('home.provider_benefit2_title'), 'desc' => __('home.provider_benefit2_desc')],
                ['title' => __('home.provider_benefit3_title'), 'desc' => __('home.provider_benefit3_desc')],
                ['title' => __('home.provider_benefit4_title'), 'desc' => __('home.provider_benefit4_desc')],
                ['title' => __('home.provider_benefit5_title'), 'desc' => __('home.provider_benefit5_desc')],
            ],
            'closing' => __('home.provider_closing'),
            'button' => [
                'text' => __('home.join_today'),
                'route' => route('register'),
                'class' => 'btn-success',
                'icon' => 'fas fa-user-plus'
            ],
            'card_class' => 'provider-card'
        ]
    ];

    $data = $benefits[$type];
@endphp

<div class="benefit-card {{ $data['card_class'] }}">
    <h3 class="card-title">{{ $data['title'] }}</h3>
    <div class="promo-badge {{ $data['promo']['class'] }}">
        <i class="{{ $data['promo']['icon'] }} me-2"></i>
        <strong>{{ $data['promo']['text'] }}</strong>
    </div>
    <ul class="benefit-list">
        @foreach($data['items'] as $item)
        <li>
            <span class="icon"><i class="fas fa-check-circle"></i></span>
            <div>
                <strong>{{ $item['title'] }}</strong>
                {{ $item['desc'] }}
            </div>
        </li>
        @endforeach
    </ul>
    <p class="text-center fw-bold mb-3">{{ $data['closing'] }}</p>
    <a href="{{ $data['button']['route'] }}" class="btn {{ $data['button']['class'] }} text-white">
        <i class="{{ $data['button']['icon'] }} me-2"></i> {{ $data['button']['text'] }}
    </a>
</div>
