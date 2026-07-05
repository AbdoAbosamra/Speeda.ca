<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/New_logo.png') }}">
    <title>{{ __('about.page_title') }} - SPEEDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-color: #667eea;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Hero Section */
        .hero-section {
            background: var(--primary-gradient);
            color: white;
            padding: 5rem 0 3rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: movePattern 20s linear infinite;
        }

        @keyframes movePattern {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .hero-content .subtitle {
            font-size: 1.3rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto;
        }

        /* Content Sections */
        .content-section {
            padding: 4rem 0;
        }

        .section-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(102, 126, 234, 0.1);
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-title i {
            font-size: 2rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-text {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        /* Values Grid */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .value-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .value-card:hover {
            border-color: var(--accent-color);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .value-icon {
            width: 70px;
            height: 70px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.8rem;
            color: white;
        }

        .value-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .value-text {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Why SPEEDA Section */
        .features-list {
            list-style: none;
            padding: 0;
            margin-top: 1.5rem;
        }

        .features-list li {
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.1rem;
        }

        .features-list li:last-child {
            border-bottom: none;
        }

        .features-list li i {
            color: #10b981;
            font-size: 1.3rem;
        }

        /* CTA Section */
        .cta-section {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-top: 3rem;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-btn {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cta-btn-primary {
            background: white;
            color: var(--accent-color);
            border: 2px solid white;
        }

        .cta-btn-primary:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .cta-btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .cta-btn-outline:hover {
            background: white;
            color: var(--accent-color);
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .section-card {
                padding: 2rem;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Include Main Navigation -->
    <x-main-nav />

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>{{ __('about.hero_title') }}</h1>
                <p class="subtitle">{{ __('about.hero_subtitle') }}</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container content-section">
        <!-- Who We Are -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fas fa-users"></i>
                {{ __('about.who_we_are_title') }}
            </h2>
            <p class="section-text">{{ __('about.who_we_are_p1') }}</p>
            <p class="section-text">{{ __('about.who_we_are_p2') }}</p>
            <p class="section-text">{{ __('about.who_we_are_p3') }}</p>
        </div>

        <!-- Our Vision -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fas fa-eye"></i>
                {{ __('about.vision_title') }}
            </h2>
            <p class="section-text">{{ __('about.vision_text') }}</p>
        </div>

        <!-- Our Mission -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fas fa-bullseye"></i>
                {{ __('about.mission_title') }}
            </h2>
            <p class="section-text">{{ __('about.mission_text') }}</p>
        </div>

        <!-- Our Values -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fas fa-heart"></i>
                {{ __('about.values_title') }}
            </h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3 class="value-title">{{ __('about.value_neutrality_title') }}</h3>
                    <p class="value-text">{{ __('about.value_neutrality_text') }}</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <h3 class="value-title">{{ __('about.value_simplicity_title') }}</h3>
                    <p class="value-text">{{ __('about.value_simplicity_text') }}</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3 class="value-title">{{ __('about.value_innovation_title') }}</h3>
                    <p class="value-text">{{ __('about.value_innovation_text') }}</p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="value-title">{{ __('about.value_credibility_title') }}</h3>
                    <p class="value-text">{{ __('about.value_credibility_text') }}</p>
                </div>
            </div>
        </div>

        <!-- Why SPEEDA -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fas fa-star"></i>
                {{ __('about.why_speeda_title') }}
            </h2>
            <ul class="features-list">
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>{{ __('about.why_feature_1') }}</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>{{ __('about.why_feature_2') }}</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>{{ __('about.why_feature_3') }}</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>{{ __('about.why_feature_4') }}</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>{{ __('about.cta_title') }}</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.95;">{{ __('about.cta_subtitle') }}</p>
            <div class="cta-buttons">
                <a href="{{ route('service-providers.index') }}" class="cta-btn cta-btn-primary">
                    <i class="fas fa-search"></i>
                    {{ __('about.cta_find_services') }}
                </a>
                <a href="{{ route('register') }}" class="cta-btn cta-btn-outline">
                    <i class="fas fa-user-plus"></i>
                    {{ __('about.cta_join_us') }}
                </a>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
