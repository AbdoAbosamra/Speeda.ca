<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('terms.title') }} - Speeda</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.8;
            color: #2c3e50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }

        body[dir="rtl"] {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 3em;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.95;
        }

        .content {
            padding: 60px 50px;
        }

        .last-updated {
            text-align: center;
            color: #888;
            font-style: italic;
            margin-bottom: 40px;
            font-size: 0.95em;
        }

        .section {
            margin-bottom: 50px;
            padding-bottom: 40px;
            border-bottom: 2px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section:hover {
            transform: translateX(5px);
        }

        body[dir="rtl"] .section:hover {
            transform: translateX(-5px);
        }

        .section-title {
            color: #667eea;
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        body[dir="rtl"] .section-title {
            flex-direction: row-reverse;
        }

        .section-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2em;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .section-content {
            color: #555;
            font-size: 1.1em;
            line-height: 1.9;
        }

        .section-content p {
            margin-bottom: 20px;
        }

        .definition-list {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 15px;
            margin: 25px 0;
            border: 2px solid #dee2e6;
        }

        .definition-item {
            margin-bottom: 18px;
            padding: 12px;
            background: white;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .definition-item:hover {
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .definition-term {
            font-weight: 700;
            color: #667eea;
            font-size: 1.1em;
            display: block;
            margin-bottom: 8px;
        }

        .highlight-box {
            background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%);
            border-left: 5px solid #ffc107;
            padding: 25px 30px;
            margin: 25px 0;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
        }

        body[dir="rtl"] .highlight-box {
            border-left: none;
            border-right: 5px solid #ffc107;
        }

        .highlight-box strong {
            color: #f57c00;
            font-size: 1.15em;
        }

        .contact-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-top: 50px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .contact-box h3 {
            margin-bottom: 20px;
            font-size: 1.8em;
            font-weight: 700;
        }

        .contact-box p {
            margin: 12px 0;
            font-size: 1.1em;
        }

        .contact-box a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 2px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s;
            padding-bottom: 2px;
        }

        .contact-box a:hover {
            border-bottom-color: white;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }

            .header {
                padding: 40px 25px;
            }

            .header h1 {
                font-size: 2em;
            }

            .content {
                padding: 40px 25px;
            }

            .section-title {
                font-size: 1.5em;
            }

            .section-number {
                width: 38px;
                height: 38px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('components.main-nav')

    <div class="container">
        <div class="header">
            <h1>{{ __('terms.title') }}</h1>
            <p>{{ __('terms.subtitle') }}</p>
        </div>

        <div class="content">
            <p class="last-updated">{{ __('terms.last_updated') }}</p>

            <div class="section">
                <h2 class="section-title">
                    <span class="section-number">1</span>
                    {{ __('terms.section1_title') }}
                </h2>
                <div class="section-content">
                    <p>{{ __('terms.section1_content') }}</p>

                    <div class="definition-list">
                        <div class="definition-item">
                            <span class="definition-term">{{ __('terms.client') }}:</span>
                            {{ __('terms.client_def') }}
                        </div>
                        <div class="definition-item">
                            <span class="definition-term">{{ __('terms.service_provider') }}:</span>
                            {{ __('terms.service_provider_def') }}
                        </div>
                        <div class="definition-item">
                            <span class="definition-term">{{ __('terms.user') }}:</span>
                            {{ __('terms.user_def') }}
                        </div>
                        <div class="definition-item">
                            <span class="definition-term">{{ __('terms.service_agreement') }}:</span>
                            {{ __('terms.service_agreement_def') }}
                        </div>
                        <div class="definition-item">
                            <span class="definition-term">{{ __('terms.psp') }}:</span>
                            {{ __('terms.psp_def') }}
                        </div>
                    </div>

                    <p>{{ __('terms.section1_accept') }}</p>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">
                    <span class="section-number">2</span>
                    {{ __('terms.section2_title') }}
                </h2>
                <div class="section-content">
                    <p>{{ __('terms.section2_content') }}</p>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">
                    <span class="section-number">3</span>
                    {{ __('terms.section3_title') }}
                </h2>
                <div class="section-content">
                    <div class="highlight-box">
                        <strong>{{ __('terms.section3_important') }}:</strong> {{ __('terms.section3_notice') }}
                    </div>
                    <p>{{ __('terms.section3_content') }}</p>
                </div>
            </div>

            <div class="contact-box">
                <h3><i class="fas fa-envelope"></i> {{ __('terms.contact_title') }}</h3>
                <p><strong>{{ __('terms.contact_company') }}</strong></p>
                <p><i class="fas fa-map-marker-alt"></i> {{ __('terms.contact_location') }}</p>
                <p>
                    <i class="fas fa-envelope"></i> {{ __('terms.contact_email') }}: 
                    <a href="mailto:support@speeda.ca">support@speeda.ca</a>
                </p>
                <p>
                    <i class="fas fa-globe"></i> {{ __('terms.contact_website') }}: 
                    <a href="https://www.Speeda.ca" target="_blank">www.Speeda.ca</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>
