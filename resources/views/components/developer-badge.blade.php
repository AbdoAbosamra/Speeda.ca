@props([
    // 'sm' for cards, 'lg' for the profile header
    'size' => 'sm',
])

@once
    <style>
        .dev-badge {
            --dev-badge-gold: #ffd76a;
            display: inline-flex;
            align-items: center;
            gap: 0.45em;
            position: relative;
            overflow: hidden;
            border-radius: 999px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0.2px;
            white-space: nowrap;
            color: #fff;
            text-decoration: none;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 45%, #2563eb 100%);
            border: 1.5px solid rgba(255, 215, 106, 0.85);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.45),
                        inset 0 1px 0 rgba(255, 255, 255, 0.25);
        }

        .dev-badge::after {
            content: "";
            position: absolute;
            top: 0;
            left: -60%;
            width: 45%;
            height: 100%;
            background: linear-gradient(115deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.55) 50%,
                rgba(255, 255, 255, 0) 100%);
            transform: skewX(-18deg);
            animation: devBadgeShimmer 3.2s ease-in-out infinite;
        }

        .dev-badge__icon {
            color: var(--dev-badge-gold);
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.25));
        }

        .dev-badge--sm {
            padding: 0.32em 0.7em;
            font-size: 0.72rem;
        }

        .dev-badge--lg {
            padding: 0.5em 1em;
            font-size: 0.95rem;
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        @keyframes devBadgeShimmer {
            0%   { left: -60%; }
            55%  { left: 130%; }
            100% { left: 130%; }
        }

        @media (prefers-reduced-motion: reduce) {
            .dev-badge::after { animation: none; opacity: 0; }
        }
    </style>
@endonce

<span {{ $attributes->merge(['class' => 'dev-badge dev-badge--' . $size]) }}
      title="{{ __('service_provider.site_developer_tooltip') }}">
    <i class="fas fa-code dev-badge__icon" aria-hidden="true"></i>
    <span>{{ __('service_provider.site_developer') }}</span>
    <i class="fas fa-crown dev-badge__icon" aria-hidden="true"></i>
</span>
