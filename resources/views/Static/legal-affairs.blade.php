<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('legal.title') }} - SPEEDA</title>
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
            min-height: 100vh;
            padding: 40px 20px;
        }

        body[dir="rtl"] {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
        }

        .container {
            max-width: 900px;
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
        }

        .header p {
            font-size: 1.2em;
            opacity: 0.95;
        }

        .content {
            padding: 60px 50px;
        }

        .legal-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .legal-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            padding: 40px;
            border-radius: 20px;
            border: 3px solid #e0e0e0;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .legal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .legal-card:hover::before {
            transform: scaleX(1);
        }

        .legal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.25);
            border-color: #667eea;
        }

        .legal-icon {
            font-size: 4em;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .legal-card h2 {
            color: #2c3e50;
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .legal-card p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.8;
            font-size: 1.05em;
        }

        .legal-card a {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 35px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
        }

        .legal-card a:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .legal-card a i {
            margin-left: 10px;
        }

        body[dir="rtl"] .legal-card a i {
            margin-left: 0;
            margin-right: 10px;
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

            .legal-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('components.main-nav')

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-gavel"></i> {{ __('legal.title') }}</h1>
            <p>{{ __('legal.subtitle') }}</p>
        </div>

        <div class="content">
            <div class="legal-cards">
                <div class="legal-card">
                    <div class="legal-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h2>{{ __('legal.terms_title') }}</h2>
                    <p>{{ __('legal.terms_description') }}</p>
                    <a href="{{ route('terms-of-service') }}">
                        {{ __('legal.read_terms') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="legal-card">
                    <div class="legal-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h2>{{ __('legal.privacy_title') }}</h2>
                    <p>{{ __('legal.privacy_description') }}</p>
                    <a href="{{ route('privacy-policy') }}">
                        {{ __('legal.read_privacy') }}
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
