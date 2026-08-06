<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no">
    <!--[if mso]>
    <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
    <![endif]-->
    <title>{{ $subject ?? 'Speeda' }}</title>
    <style>
        /* =====================================================
           SPEEDA EMAIL DESIGN SYSTEM
           Brand: Deep Blue #0F1F3D | Accent #3B82F6 | Gold #F59E0B
        ===================================================== */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            background-color: #F0F4FA;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        .email-wrapper {
            width: 100%;
            background-color: #F0F4FA;
            padding: 40px 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 40px rgba(15, 31, 61, 0.12);
        }

        /* === HEADER === */
        .email-header {
            background: linear-gradient(135deg, #0F1F3D 0%, #1E3A8A 50%, #1D4ED8 100%);
            padding: 40px 48px 36px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .email-header::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            background: rgba(59, 130, 246, 0.15);
            border-radius: 50%;
        }

        .email-header::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 150px;
            height: 150px;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 50%;
        }

        .logo-container {
            position: relative;
            z-index: 1;
            margin-bottom: 8px;
        }

        .logo-text {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            text-decoration: none;
        }

        .logo-dot {
            color: #F59E0B;
        }

        .header-tagline {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* === HERO BADGE === */
        .hero-badge {
            display: inline-block;
            background: linear-gradient(135deg, #0F1F3D, #1E3A8A);
            color: #fff;
            border-radius: 12px 12px 0 0;
            padding: 0;
        }

        .step-badge {
            display: inline-block;
            background: linear-gradient(90deg, #F59E0B, #EF4444);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 100px;
            margin-bottom: 20px;
        }

        /* === BODY === */
        .email-body {
            padding: 48px 48px 40px;
        }

        .greeting {
            font-size: 16px;
            color: #64748B;
            margin: 0 0 8px 0;
            font-weight: 500;
        }

        .main-headline {
            font-size: 28px;
            font-weight: 800;
            color: #0F1F3D;
            margin: 0 0 24px 0;
            line-height: 1.25;
            letter-spacing: -0.5px;
        }

        .lead-text {
            font-size: 16px;
            line-height: 1.7;
            color: #374151;
            margin: 0 0 32px 0;
        }

        /* === NEXT STEP CARD === */
        .next-step-card {
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
            border: 2px solid #BFDBFE;
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 28px;
        }

        .next-step-label {
            font-size: 11px;
            font-weight: 700;
            color: #1D4ED8;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin: 0 0 10px 0;
        }

        .next-step-title {
            font-size: 20px;
            font-weight: 700;
            color: #0F1F3D;
            margin: 0 0 12px 0;
        }

        .next-step-desc {
            font-size: 15px;
            color: #374151;
            line-height: 1.65;
            margin: 0;
        }

        /* === WHY IT MATTERS === */
        .why-card {
            background: #FFFBEB;
            border: 1.5px solid #FCD34D;
            border-radius: 14px;
            padding: 22px 28px;
            margin-bottom: 32px;
        }

        .why-label {
            font-size: 11px;
            font-weight: 700;
            color: #B45309;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin: 0 0 10px 0;
        }

        .why-text {
            font-size: 15px;
            color: #374151;
            line-height: 1.65;
            margin: 0;
        }

        /* === STATS STRIP === */
        .stats-strip {
            display: table;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 32px;
        }

        .stat-cell {
            display: table-cell;
            text-align: center;
            padding: 18px 8px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
        }

        .stat-cell:first-child { border-radius: 12px 0 0 12px; }
        .stat-cell:last-child  { border-radius: 0 12px 12px 0; }

        .stat-number {
            font-size: 24px;
            font-weight: 800;
            color: #0F1F3D;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            color: #64748B;
            font-weight: 500;
        }

        /* === CTA BUTTON === */
        .cta-container {
            text-align: center;
            margin-bottom: 36px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #1D4ED8 0%, #0F1F3D 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 16px;
            font-weight: 700;
            padding: 18px 48px;
            border-radius: 100px;
            letter-spacing: 0.3px;
            box-shadow: 0 8px 32px rgba(29, 78, 216, 0.35);
            transition: all 0.2s;
        }

        .cta-subtext {
            font-size: 13px;
            color: #94A3B8;
            margin-top: 12px;
            display: block;
        }

        /* === PROGRESS BAR === */
        .progress-section {
            background: #F8FAFC;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 32px;
        }

        .progress-label {
            font-size: 12px;
            color: #64748B;
            font-weight: 600;
            margin: 0 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .progress-bar-bg {
            background: #E2E8F0;
            border-radius: 100px;
            height: 8px;
            margin-bottom: 6px;
        }

        .progress-bar-fill {
            height: 8px;
            border-radius: 100px;
            background: linear-gradient(90deg, #1D4ED8, #3B82F6);
        }

        .progress-text {
            font-size: 13px;
            color: #475569;
            font-weight: 500;
        }

        /* === CLOSING QUOTE === */
        .closing-quote {
            font-size: 15px;
            font-style: italic;
            color: #64748B;
            line-height: 1.65;
            border-left: 4px solid #3B82F6;
            padding-left: 20px;
            margin-bottom: 32px;
        }

        /* === DIVIDER === */
        .divider {
            border: none;
            border-top: 1px solid #E2E8F0;
            margin: 32px 0;
        }

        /* === SIGNATURE === */
        .signature {
            font-size: 15px;
            color: #374151;
            line-height: 1.7;
            margin: 0;
        }

        .signature-name {
            font-weight: 700;
            color: #0F1F3D;
        }

        /* === FOOTER === */
        .email-footer {
            background: #0F1F3D;
            padding: 32px 48px;
            text-align: center;
        }

        .footer-logo {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            margin-bottom: 12px;
            display: block;
        }

        .footer-logo span {
            color: #F59E0B;
        }

        .footer-tagline {
            font-size: 13px;
            color: rgba(255,255,255,0.5);
            margin: 0 0 20px 0;
        }

        .footer-links {
            margin-bottom: 16px;
        }

        .footer-links a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 12px;
            margin: 0 10px;
        }

        .footer-copyright {
            font-size: 11px;
            color: rgba(255,255,255,0.3);
            margin: 0;
        }

        /* === MOBILE === */
        @media only screen and (max-width: 600px) {
            .email-body { padding: 32px 24px 28px; }
            .email-header { padding: 28px 24px; }
            .email-footer { padding: 24px; }
            .main-headline { font-size: 22px; }
            .cta-button { padding: 16px 32px; font-size: 15px; }
            .next-step-card { padding: 22px 20px; }
            .why-card { padding: 18px 20px; }
        }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">

        {{-- ============ HEADER ============ --}}
        <div class="email-header">
            <div class="logo-container">
                <a href="{{ config('app.url') }}" class="logo-text">Speeda<span class="logo-dot">.</span></a>
            </div>
            <p class="header-tagline">Your Local Business Growth Partner</p>
        </div>

        {{-- ============ BODY ============ --}}
        <div class="email-body">
            @yield('content')

            <hr class="divider">

            <p class="signature">
                With excitement for your success,<br>
                <span class="signature-name">The Speeda Team</span> 🚀
            </p>
        </div>

        {{-- ============ FOOTER ============ --}}
        <div class="email-footer">
            <a href="{{ config('app.url') }}" class="footer-logo">Speeda<span>.</span></a>
            <p class="footer-tagline">Connecting great businesses with local customers.</p>
            <div class="footer-links">
                <a href="{{ config('app.url') }}/privacy-policy">Privacy Policy</a>
                <a href="{{ config('app.url') }}/terms-of-service">Terms of Service</a>
                <a href="{{ config('app.url') }}/help-center">Help Center</a>
            </div>
            <p class="footer-copyright">© {{ date('Y') }} Speeda. All rights reserved.<br>
            @yield('footer_note', "You're receiving this because you registered as a service provider on Speeda.")</p>
        </div>

    </div>
</div>
</body>
</html>
