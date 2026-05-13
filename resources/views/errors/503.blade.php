@php
    // منطق تحديد اللغة
    $locale = request()->query('lang', 'en');
    $supported = ['en', 'ar', 'fr'];
    if (!in_array($locale, $supported)) {
        $locale = 'en';
    }

    $isRtl = ($locale === 'ar');
    $dir = $isRtl ? 'rtl' : 'ltr';

    // النصوص المترجمة
    $translations = [
        'en' => [
            'heading_before' => "We're making improvements to",
            'heading_brand' => "Speeda",
            'heading_after' => "to serve you better.",
            'subheading' => "Our platform will be back online shortly with an enhanced experience.",
            'thank_you' => "Thank you for your patience.",
            'back_shortly' => "We'll be back shortly.",
        ],
        'ar' => [
            'heading_before' => "نقوم بإجراء تحسينات على",
            'heading_brand' => "Speeda",
            'heading_after' => "لنخدمك بشكل أفضل.",
            'subheading' => "ستعود منصتنا للعمل قريباً بتجربة مطورة ومميزة.",
            'thank_you' => "شكراً لصبركم معنا.",
            'back_shortly' => "سنعود إليكم في أقرب وقت.",
        ],
        'fr' => [
            'heading_before' => "Nous améliorons",
            'heading_brand' => "Speeda",
            'heading_after' => "pour mieux vous servir.",
            'subheading' => "Notre plateforme sera de nouveau en ligne sous peu avec une expérience améliorée.",
            'thank_you' => "Merci de votre patience.",
            'back_shortly' => "Nous serons de retour bientôt.",
        ]
    ];

    $t = $translations[$locale];
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/main-logo.png') }}">
    <title>Maintenance — Speeda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Noto+Kufi+Arabic:wght@400;700;900&display=swap"
        rel="stylesheet">

    <style>
        body {
            background: #f0f7ff;
            font-family: 'Inter', 'Noto Kufi Arabic', sans-serif;
        }

        /* ضبط حجم اللوجو */
        .maintenance-logo {
            width: clamp(160px, 20vw, 220px);
            height: auto;
        }

        /* خط الفاصل (النجمة) */
        .star-divider::before,
        .star-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: linear-gradient(90deg, transparent, rgba(0, 86, 179, 0.2), transparent);
        }

        /* منع المحتوى من النزول تحت الموجة */
        .content-container {
            padding-bottom: 120px;
        }
    </style>
</head>

<body class="antialiased min-h-full relative overflow-x-hidden flex flex-col">

    <nav class="fixed top-6 left-0 right-0 z-50 flex justify-center px-4">
        <div class="flex gap-1 bg-white/80 backdrop-blur-md border border-slate-200 rounded-full p-1 shadow-lg">
            @foreach(['en' => 'EN', 'ar' => 'عربي', 'fr' => 'FR'] as $code => $label)
                <a href="?lang={{ $code }}"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ $locale === $code ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>

    <main
        class="relative z-10 flex-grow flex flex-col items-center justify-center px-6 text-center content-container mt-20">

        <div class="max-w-4xl w-full">

            <div class="mb-10 flex justify-center animate-pulse">
                <img src="/images/main-logo.png" alt="Speeda" class="maintenance-logo drop-shadow-2xl">
            </div>

            <h1 class="text-3xl md:text-5xl font-black text-slate-800 mb-8 leading-tight tracking-tight">
                {{ $t['heading_before'] }}
                <span class="text-blue-700">{{ $t['heading_brand'] }}</span>
                {{ $t['heading_after'] }}
            </h1>

            <div class="star-divider flex items-center gap-6 mb-10 w-full max-w-xl mx-auto opacity-60">
                <div class="bg-white p-2.5 rounded-full shadow-md ring-1 ring-yellow-200">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#FBBF24">
                        <path d="M12 2L14.9 8.6L22 9.3L16.8 14L18.2 21L12 17.5L5.8 21L7.2 14L2 9.3L9.1 8.6L12 2Z" />
                    </svg>
                </div>
            </div>

            <p class="text-slate-500 text-lg md:text-xl font-medium mb-12 max-w-2xl mx-auto leading-relaxed">
                {{ $t['subheading'] }}
            </p>

            <div class="flex items-center justify-center mb-12">
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-500 blur-2xl opacity-20 animate-pulse"></div>
                    <div
                        class="relative bg-blue-600 p-6 rounded-[2rem] shadow-2xl text-white transform rotate-3 hover:rotate-0 transition-transform">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a2 2 0 0 1-2.79-2.79L14.7 6.3Z" />
                            <path d="m20 10 1.3 1.3a2 2 0 0 1 0 2.8l-4.5 4.5a2 2 0 0 1-2.8 0L12.7 17.3" />
                            <path d="M3 21l9-9" />
                            <path d="M9 15l-4.5 4.5a2 2 0 0 1-2.8 0l-1.3-1.3a2 2 0 0 1 0-2.8L5 11" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="space-y-3 mb-10">
                <p class="text-slate-800 font-bold text-xl">{{ $t['thank_you'] }}</p>
                <p class="text-blue-600 font-extrabold tracking-wide uppercase">{{ $t['back_shortly'] }}</p>
            </div>

            <div class="inline-flex gap-3 bg-white/60 border border-white rounded-full px-6 py-3 shadow-sm">
                <span class="w-3 h-3 rounded-full bg-blue-400 animate-bounce"></span>
                <span class="w-3 h-3 rounded-full bg-blue-600 animate-bounce [animation-delay:-0.2s]"></span>
                <span class="w-3 h-3 rounded-full bg-blue-400 animate-bounce [animation-delay:-0.4s]"></span>
            </div>
        </div>
    </main>

    <footer class="fixed bottom-0 left-0 w-full z-0 pointer-events-none">
        <svg viewBox="0 0 1440 200" preserveAspectRatio="none" class="w-full h-[100px] md:h-[180px]">
            <path d="M0 120 C400 250 1000 0 1440 120 L1440 200 L0 200 Z" fill="#0056b3" />
        </svg>
    </footer>

</body>

</html>