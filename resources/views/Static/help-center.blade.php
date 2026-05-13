<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/main-logo.png') }}">
    <title>{{ __('help.title') }} - SPEEDA</title>
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
            max-width: 1100px;
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

        .contact-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 50px;
            border: 3px solid #dee2e6;
        }

        .contact-section h2 {
            color: #667eea;
            font-size: 2em;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        body[dir="rtl"] .contact-section h2 {
            flex-direction: row-reverse;
        }

        .contact-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .contact-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            text-align: center;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }

        .contact-icon {
            font-size: 3em;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .contact-card h3 {
            color: #667eea;
            font-size: 1.4em;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .contact-card p {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .contact-card a {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .contact-card a:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .faq-section h2 {
            color: #667eea;
            font-size: 2em;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        body[dir="rtl"] .faq-section h2 {
            flex-direction: row-reverse;
        }

        .faq-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        .faq-question {
            font-weight: 600;
            font-size: 1.15em;
            color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        body[dir="rtl"] .faq-question {
            flex-direction: row-reverse;
        }

        .faq-question i {
            color: #667eea;
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e0e0e0;
            color: #666;
            line-height: 1.8;
            display: none;
        }

        .faq-item.active .faq-answer {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
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

            .contact-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
@include('components.main-nav')

    <div class="container">
        <div class="header">
            <h1><i class="fas fa-life-ring"></i> {{ __('help.title') }}</h1>
            <p>{{ __('help.subtitle') }}</p>
        </div>

        <div class="content">
            <!-- Contact Section -->
            <div class="contact-section">
                <h2>
                    <i class="fas fa-comments"></i>
                    {{ __('help.contact_us') }}
                </h2>
                <p style="font-size: 1.1em; color: #666; margin-bottom: 20px;">
                    {{ __('help.contact_description') }}
                </p>

                <div class="contact-methods">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fab fa-facebook-messenger"></i>
                        </div>
                        <h3>{{ __('help.messenger_title') }}</h3>
                        <p>{{ __('help.messenger_description') }}</p>
                        <a href="https://m.me/61583422931690" target="_blank">
                            <i class="fab fa-facebook-messenger"></i> {{ __('help.chat_now') }}
                        </a>
                    </div>

                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>{{ __('help.email_title') }}</h3>
                        <p>{{ __('help.email_description') }}</p>
                        <a href="mailto:support@speeda.ca">
                            <i class="fas fa-envelope"></i> support@speeda.ca
                        </a>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="faq-section">
                <h2>
                    <i class="fas fa-question-circle"></i>
                    {{ __('help.faq_title') }}
                </h2>

                <div class="faq-item" onclick="toggleFAQ(this)">
                    <div class="faq-question">
                        <span>{{ __('help.faq1_q') }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        {{ __('help.faq1_a') }}
                    </div>
                </div>

                <div class="faq-item" onclick="toggleFAQ(this)">
                    <div class="faq-question">
                        <span>{{ __('help.faq2_q') }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        {{ __('help.faq2_a') }}
                    </div>
                </div>

                <div class="faq-item" onclick="toggleFAQ(this)">
                    <div class="faq-question">
                        <span>{{ __('help.faq4_q') }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        {{ __('help.faq4_a') }}
                    </div>
                </div>

                <div class="faq-item" onclick="toggleFAQ(this)">
                    <div class="faq-question">
                        <span>{{ __('help.faq5_q') }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        {{ __('help.faq5_a') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleFAQ(element) {
            element.classList.toggle('active');
        }
    </script>

</body>
</html>
