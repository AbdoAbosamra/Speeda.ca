<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('home.meta_title', ['app_name' => 'Speeda'])); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/main-logo.png')); ?>">
    <!-- Preconnect to CDNs for faster loading -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --card-hover-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--dark-text);
            line-height: 1.6;
            background-color: #ffffff;
        }

        /* ===== Client Search Section Styles ===== */
        #client-search-section {
            position: relative;
            padding: 80px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #F8FAFC;
        }

        #bg-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            opacity: 0.5;
        }

        .client-search-container {
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        .section-preheading {
            text-align: center;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--primary-color);
            margin-bottom: 20px;
            animation: fadeInDown 0.8s ease-out;
        }

        .client-hero-title {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 20px;
            text-align: center;
            color: var(--dark-text);
            animation: fadeInUp 0.8s ease-out 0.1s backwards;
        }

        .highlight-text {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
        }

        .client-hero-subtitle {
            font-size: 1.1rem;
            color: var(--secondary-color);
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto 40px;
            text-align: center;
            animation: fadeInUp 0.8s ease-out 0.2s backwards;
        }

        .client-cta-container {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInUp 0.8s ease-out 0.3s backwards;
        }

        button.btn-3d {
            --button_radius: 12px;
            --button_color: var(--primary-color);
            --button_outline_color: #0056b3;

            font-size: 18px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 700;
            border: none;
            cursor: pointer;
            border-radius: var(--button_radius);
            background: var(--button_outline_color);
            padding: 0;
            outline: none;
        }

        button.btn-3d:focus {
            box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.2);
        }

        .button_top {
            display: block;
            box-sizing: border-box;
            border: 2px solid var(--button_outline_color);
            border-radius: var(--button_radius);
            padding: 16px 48px;
            background: var(--button_color);
            color: white;
            transform: translateY(-4px);
            transition: transform 0.1s ease;
            text-decoration: none;
        }

        button.btn-3d:hover .button_top {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.2);
        }

        button.btn-3d:active .button_top {
            transform: translateY(0);
        }

        .client-sub-cta {
            margin-top: 16px;
            font-size: 0.85rem;
            color: var(--secondary-color);
        }

        .client-benefits-grid {
            display: flex;
            flex-direction: row;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease-out 0.4s backwards;
        }

        .client-benefit-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: left;
            position: relative;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transform-style: preserve-3d;
            transform: translateZ(0);
            transition: transform 0.1s ease-out;
            flex: 1 1 300px;
            max-width: 350px;
            min-width: 280px;
        }

        .client-benefit-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .client-icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            transform: translateZ(20px);
        }

        .client-benefit-card h4 {
            font-size: 1.15rem;
            margin-bottom: 8px;
            color: var(--dark-text);
            transform: translateZ(20px);
        }

        .client-benefit-card p {
            color: var(--secondary-color);
            line-height: 1.5;
            font-size: 0.9rem;
            transform: translateZ(20px);
        }

        .client-b-blue .client-icon-box {
            background: rgba(0, 123, 255, 0.1);
            color: var(--primary-color);
        }

        .client-b-amber .client-icon-box {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .client-b-green .client-icon-box {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .client-hero-title { font-size: 2.2rem; }
            #client-search-section { padding: 50px 15px; }
            .client-benefits-grid { flex-direction: column; align-items: center; }
            .client-benefit-card { width: 100%; max-width: 100%; }
            .button_top { padding: 14px 32px; font-size: 16px; }
        }
        /* ===== End Client Search Section Styles ===== */

        /* Navigation */
        .navbar-brand img {
            width: 120px;
            height: auto;
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--primary-color);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        /* Hero Section */
        #hero {
            background: linear-gradient(135deg, #e6f2ff 0%, #f0f8ff 100%);
            padding: 4rem 0 6rem;
            position: relative;
            overflow: hidden;
        }

        #hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" opacity="0.03"><polygon fill="%23007bff" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-image {
            border-radius: 15px;
            transition: transform 0.5s ease;
        }

        .hero-image:hover {
            transform: scale(1.02);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            text-shadow: 0 4px 6px rgba(0, 123, 255, 0.2);
            animation: fadeInUp 1s ease-out;
        }

        .hero-tagline {
            font-size: 1.5rem;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 1.5rem;
            font-style: italic;
            text-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: var(--secondary-color);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        /* Section Headings */
        .section-heading {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 3rem;
            position: relative;
            display: inline-block;
        }

        .section-heading::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), #0056b3);
            border-radius: 2px;
        }

        .text-center .section-heading::after {
            left: 50%;
            transform: translateX(-50%);
        }

        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem 2rem;
            box-shadow: var(--card-shadow);
            transition: all 0.4s ease;
            height: 100%;
            text-align: center;
            border: 1px solid rgba(0, 123, 255, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--card-hover-shadow);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
        }

        .feature-card h4 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #333;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* Benefits Section */
        .benefits-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #dee2e6 100%);
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .benefits-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 30%, rgba(255, 193, 7, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(25, 135, 84, 0.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .benefit-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .benefit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .benefit-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 16px 50px rgba(0, 0, 0, 0.15);
        }

        .benefit-card:hover::before {
            opacity: 0.6;
        }

        .client-card {
            background: linear-gradient(135deg, #fffbf0 0%, #fff8e1 50%, #fff3cd 100%);
            border: 2px solid rgba(255, 193, 7, 0.3);
            border-left: 6px solid #ffc107;
            position: relative;
        }

        .client-card::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            bottom: -2px;
            left: -2px;
            background: linear-gradient(135deg, #ffc107, #ff9800);
            border-radius: 20px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .client-card:hover::after {
            opacity: 0.1;
        }

        .client-card:hover {
            border-color: rgba(255, 193, 7, 0.6);
            background: linear-gradient(135deg, #fffff5 0%, #fffbf0 50%, #fff8e1 100%);
        }

        .provider-card {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #d1e7dd 100%);
            border: 2px solid rgba(25, 135, 84, 0.3);
            border-left: 6px solid #198754;
            position: relative;
        }

        .provider-card::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            bottom: -2px;
            left: -2px;
            background: linear-gradient(135deg, #198754, #0d6efd);
            border-radius: 20px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .provider-card:hover::after {
            opacity: 0.1;
        }

        .provider-card:hover {
            border-color: rgba(25, 135, 84, 0.6);
            background: linear-gradient(135deg, #f7fef9 0%, #f0fdf4 50%, #dcfce7 100%);
        }

        .benefit-card .card-title {
            font-size: 1.875rem;
            font-weight: 800;
            margin-bottom: 1.75rem;
            position: relative;
            padding-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .benefit-card .card-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            border-radius: 2px;
            transition: width 0.4s ease;
        }

        .benefit-card:hover .card-title::after {
            width: 100px;
        }

        .client-card .card-title {
            color: #c77700;
            text-shadow: 0 2px 4px rgba(199, 119, 0, 0.1);
        }

        .client-card .card-title::after {
            background: linear-gradient(90deg, #ffc107, #ff9800);
        }

        .provider-card .card-title {
            color: #0a4028;
            text-shadow: 0 2px 4px rgba(10, 64, 40, 0.1);
        }

        .provider-card .card-title::after {
            background: linear-gradient(90deg, #198754, #28a745);
        }

        .benefit-list {
            list-style: none;
            padding: 0;
            margin-bottom: 2rem;
        }

        .benefit-list li {
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            padding: 0.75rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.5);
        }

        .benefit-list li:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .benefit-list .icon {
            margin-right: 1rem;
            font-size: 1.5rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .benefit-list li:hover .icon {
            transform: scale(1.15) rotate(10deg);
        }

        .client-card .icon {
            color: #ffc107;
            background: rgba(255, 193, 7, 0.15);
        }

        .client-card .benefit-list li:hover .icon {
            background: rgba(255, 193, 7, 0.25);
            box-shadow: 0 0 20px rgba(255, 193, 7, 0.4);
        }

        .provider-card .icon {
            color: #198754;
            background: rgba(25, 135, 84, 0.15);
        }

        .provider-card .benefit-list li:hover .icon {
            background: rgba(25, 135, 84, 0.25);
            box-shadow: 0 0 20px rgba(25, 135, 84, 0.4);
        }

        .benefit-list li strong {
            color: #212529;
            font-weight: 700;
            font-size: 1.0625rem;
        }

        /* Promotional Badge Styles */
        .promo-badge {
            display: block;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            font-size: 0.95rem;
            font-weight: 600;
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            overflow: hidden;
            animation: promoPulse 3s ease-in-out infinite;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(5px);
            border: 2px solid;
            line-height: 1.6;
        }

        .promo-badge i {
            font-size: 1.1rem;
            vertical-align: middle;
            animation: iconBounce 2s ease-in-out infinite;
        }

        .client-promo {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            color: #856404;
            border-color: #ffc107;
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3), inset 0 1px 3px rgba(255, 255, 255, 0.5);
        }

        .client-promo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            animation: shimmer 3s infinite;
        }

        .provider-promo {
            background: linear-gradient(135deg, #00ff88 0%, #00cc6a 50%, #00ff88 100%);
            color: #0a4028;
            border-color: #198754;
            box-shadow: 0 6px 20px rgba(25, 135, 84, 0.3), inset 0 1px 3px rgba(255, 255, 255, 0.5);
        }

        .provider-promo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            animation: shimmer 3s infinite;
        }

        @keyframes promoPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            }
            50% {
                transform: scale(1.02);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            }
        }

        @keyframes iconBounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-3px);
            }
        }

        @keyframes shimmer {
            0% {
                transform: rotate(0deg) translate(-50%, -50%);
            }
            100% {
                transform: rotate(360deg) translate(-50%, -50%);
            }
        }

        /* أزرار الكروت المحسّنة */
        .benefit-card .btn {
            width: 100%;
            padding: 1rem 2rem;
            font-size: 1.125rem;
            font-weight: 700;
            border-radius: 12px;
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .benefit-card .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .benefit-card .btn:hover::before {
            left: 100%;
        }

        .benefit-card .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        .benefit-card .btn:active {
            transform: translateY(-1px);
        }

        .client-card .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #ffffff !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .client-card .btn-warning:hover {
            background: linear-gradient(135deg, #ffb300 0%, #ff6f00 100%);
        }

        .provider-card .btn-success {
            background: linear-gradient(135deg, #198754 0%, #28a745 100%);
            color: #ffffff !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .provider-card .btn-success:hover {
            background: linear-gradient(135deg, #157347 0%, #20c997 100%);
        }

        .benefit-card .btn i {
            transition: transform 0.3s ease;
            font-size: 1.25rem;
        }

        .benefit-card .btn:hover i {
            transform: translateX(5px);
        }

        /* نص الإغلاق في الكروت */
        .benefit-card > p.fw-bold {
            font-size: 1.125rem;
            color: #495057;
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 10px;
            border-left: 4px solid currentColor;
        }

        .client-card > p.fw-bold {
            border-left-color: #ffc107;
        }

        .provider-card > p.fw-bold {
            border-left-color: #198754;
        }

        /* CTA Section */
        .cta-section {
            padding: 5rem 0;
            text-align: center;
        }

        .cta-section h2 {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            color: var(--secondary-color);
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        #cookie-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(3px);
            z-index: 9998;
            display: none;
        }

        /* ===== Cookie Banner Styling ===== */
        #cookie-banner {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: #2b2b2b;
            color: #fff;
            padding: 25px;
            width: 90%;
            max-width: 600px;
            text-align: center;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 -3px 12px rgba(0, 0, 0, 0.4);
            font-size: 14px;
            z-index: 9999;
            display: none;
        }

        #cookie-banner a {
            color: #4fc3f7;
            text-decoration: underline;
        }

        #cookie-banner button {
            background-color: #4fc3f7;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-top: 10px;
            font-weight: 600;
            cursor: pointer;
        }

        #cookie-banner button:hover {
            background-color: #03a9f4;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-brand img {
                width: 100px;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-tagline {
                font-size: 1.2rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .feature-card, .benefit-card {
                padding: 2rem 1.5rem;
                margin-bottom: 1.5rem;
            }

            .btn-primary, .btn-outline-primary {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-tagline {
                font-size: 1rem;
            }

            .section-heading {
                font-size: 1.75rem;
            }

            .feature-icon {
                width: 70px;
                height: 70px;
                font-size: 1.7rem;
            }
        }

        /* Loading Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .feature-card, .benefit-card {
            animation: fadeIn 0.6s ease-out;
        }
    </style>
</head>
<body>
<?php echo $__env->make('components.main-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<!-- Client Search Section (New Section) -->
<section id="client-search-section">
    <canvas id="bg-canvas"></canvas>
    <div class="client-search-container">
        <!-- Top Question -->
        <h3 class="section-preheading"><?php echo e(__('home.sp_section_title')); ?></h3>

        <!-- Main Heading -->
        <div class="text-center">
            <h1 class="client-hero-title">
                <?php echo e(__('home.sp_hero_intro')); ?> <br>
                <span class="highlight-text"><?php echo e(__('home.sp_hero_highlight')); ?></span>
            </h1>
        </div>

        <!-- Subheading -->
<div class="text-center">
    <p class="client-hero-subtitle" style="font-size: 1rem; font-weight: 700; background: linear-gradient(145deg, #4f46e5, #6366f1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-color: #eef2ff; display: inline-block; padding: 0.5rem 1.8rem; border-radius: 40px; box-shadow: 0 6px 15px -3px rgba(79,70,229,0.3); letter-spacing: 0.3px; transition: all 0.3s ease; cursor: default;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 12px 25px -5px rgba(79,70,229,0.5)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 6px 15px -3px rgba(79,70,229,0.3)';">
        <?php echo e(__('home.sp_hero_cta')); ?>

    </p>
</div>
        <!-- Primary CTA Button (3D CSS) -->
        <div class="client-cta-container">
            <!-- Button structure updated to match provided code -->
            <button class="btn-3d">
                <span class="button_top">
                    <i class="fas fa-user-plus me-2"></i> <?php echo e(__('home.sp_create_profile_btn')); ?>

                </span>
            </button>
            <div class="client-sub-cta"><?php echo e(__('home.sp_no_credit_card')); ?></div>
        </div>

        <!-- Benefits Grid (One Line Layout) -->
        <div class="client-benefits-grid">
            <!-- Card 1 -->
            <div class="client-benefit-card client-b-blue" data-tilt>
                <div class="client-icon-box">
                    <i class="fas fa-file-contract"></i>
                </div>
                <h4><?php echo e(__('home.sp_benefit1_title')); ?></h4>
                <p><?php echo e(__('home.sp_benefit1_desc')); ?></p>
            </div>

            <!-- Card 2 -->
            <div class="client-benefit-card client-b-amber" data-tilt>
                <div class="client-icon-box">
                    <i class="fas fa-percentage"></i>
                </div>
                <h4><?php echo e(__('home.sp_benefit2_title')); ?></h4>
                <p><?php echo e(__('home.sp_benefit2_desc')); ?></p>
            </div>

            <!-- Card 3 -->
            <div class="client-benefit-card client-b-green" data-tilt>
                <div class="client-icon-box">
                    <i class="fas fa-user-cog"></i>
                </div>
                <h4><?php echo e(__('home.sp_benefit3_title')); ?></h4>
                <p><?php echo e(__('home.sp_benefit3_desc')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Hero Section -->
<section id="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-content">
                <h1 class="hero-title" style="color: #ffc107;"><?php echo e(__('home.hero_tagline')); ?></h1>
                <p class="hero-subtitle"><?php echo e(__('home.hero_subtitle')); ?></p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo e(route('location')); ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i> <?php echo e(__('home.find_provider')); ?>

                    <a href="<?php echo e(route('register')); ?>" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-user-tie me-2"></i> <?php echo e(__('home.join_provider')); ?>

                    </a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0">
                <img src="<?php echo e(asset('images/banner.png')); ?>" alt="Speeda Services" class="img-fluid hero-image" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-5 py-md-5">
    <div class="container">
        <h2 class="text-center section-heading"><?php echo e(__('home.how_it_works_title')); ?></h2>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h4><?php echo e(__('home.step1_title')); ?></h4>
                    <p><?php echo e(__('home.step1_description')); ?></p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4><?php echo e(__('home.step2_title')); ?></h4>
                    <p><?php echo e(__('home.step2_description')); ?></p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h4><?php echo e(__('home.step3_title')); ?></h4>
                    <p><?php echo e(__('home.step3_description')); ?></p>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <p class="text-center text-muted fst-italic"><?php echo e(__('home.platform_disclaimer')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="benefits-section">
    <div class="container">
        <h2 class="text-center section-heading"><?php echo e(__('home.benefits_title')); ?></h2>
        <div class="row g-4">
            <!-- Client Card -->
            <div class="col-lg-6">
                <div class="benefit-card client-card">
                    <h3 class="card-title"><?php echo e(__('home.client_benefits_title')); ?></h3>
                    <div class="promo-badge client-promo">
                        <i class="fas fa-star me-2"></i>
                        <strong><?php echo e(__('home.client_free_forever')); ?></strong> <?php echo e(__('home.client_free_forever_desc')); ?>

                    </div>
                    <ul class="benefit-list">
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.client_benefit1_title')); ?></strong> <?php echo e(__('home.client_benefit1_desc')); ?>

                            </div>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.client_benefit2_title')); ?></strong> <?php echo e(__('home.client_benefit2_desc')); ?>

                            </div>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.client_benefit3_title')); ?></strong> <?php echo e(__('home.client_benefit3_desc')); ?>

                            </div>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.client_benefit4_title')); ?></strong> <?php echo e(__('home.client_benefit4_desc')); ?>

                            </div>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.client_benefit5_title')); ?></strong> <?php echo e(__('home.client_benefit5_desc')); ?>

                            </div>
                        </li>
                    </ul>
                    <p class="text-center fw-bold mb-3"><?php echo e(__('home.client_closing')); ?></p>
                    <a href="<?php echo e(route('location')); ?>" class="btn btn-warning text-white">
                        <i class="fas fa-rocket me-2"></i> <?php echo e(__('home.start_project')); ?>

                    </a>
                </div>
            </div>

            <!-- Provider Card -->
            <div class="col-lg-6">
                <div class="benefit-card provider-card">
                    <h3 class="card-title"><?php echo e(__('home.provider_benefits_title')); ?></h3>
                    <div class="promo-badge provider-promo">
                        <i class="fas fa-clock me-2"></i>
                        <strong><?php echo e(__('home.provider_join_free')); ?></strong> <?php echo e(__('home.provider_join_free_desc')); ?>

                    </div>
                    <ul class="benefit-list">
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.provider_benefit1_title')); ?></strong> <?php echo e(__('home.provider_benefit1_desc')); ?>

                            </div>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.provider_benefit2_title')); ?></strong> <?php echo e(__('home.provider_benefit2_desc')); ?>

                            </div>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.provider_benefit3_title')); ?></strong> <?php echo e(__('home.provider_benefit3_desc')); ?>

                            </div>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.provider_benefit4_title')); ?></strong> <?php echo e(__('home.provider_benefit4_desc')); ?>

                            </div>
                        </li>
                        <li>
                            <span class="icon"><i class="fas fa-check-circle"></i></span>
                            <div>
                                <strong><?php echo e(__('home.provider_benefit5_title')); ?></strong> <?php echo e(__('home.provider_benefit5_desc')); ?>

                            </div>
                        </li>
                    </ul>
                    <p class="text-center fw-bold mb-3"><?php echo e(__('home.provider_closing')); ?></p>
                    <a href="<?php echo e(route('register')); ?>" class="btn btn-success text-white">
                        <i class="fas fa-user-plus me-2"></i> <?php echo e(__('home.join_today')); ?>

                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <h2><?php echo e(__('home.cta_title')); ?></h2>
        <p><?php echo e(__('home.cta_description')); ?></p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?php echo e(route('location')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i> <?php echo e(__('home.find_service')); ?>

            </a>
            <a href="<?php echo e(route('register')); ?>" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-user-tie me-2"></i> <?php echo e(__('home.register_pro')); ?>

            </a>
        </div>
    </div>
</section>

<?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div id="cookie-overlay"></div>

  <!-- ===== Cookie Consent Banner ===== -->
  <div id="cookie-banner">
    🍪 <?php echo e(__('home.cookie_message')); ?>

    <?php echo e(__('home.cookie_agree')); ?>

    <a href="/terms-of-service" target="_blank"><?php echo e(__('home.terms_service')); ?></a> <?php echo e(__('home.and')); ?>

    <a href="/privacy-policy" target="_blank"><?php echo e(__('home.privacy_policy')); ?></a>.
    <br>
    <button id="accept-cookies"><?php echo e(__('home.accept')); ?></button>
  </div>




<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

<!-- Custom JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Home page loaded successfully');

        // Add loading animation to cards
        const featureCards = document.querySelectorAll('.feature-card, .benefit-card');
        featureCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const banner = document.getElementById("cookie-banner");
        const overlay = document.getElementById("cookie-overlay");
        const acceptBtn = document.getElementById("accept-cookies");

        // Function to show banner & block access
        function showBanner() {
            banner.style.display = "block";
            overlay.style.display = "block";
            document.body.style.overflow = "hidden"; // disable scrolling
        }

        // Function to hide banner & allow access
        function hideBanner() {
            banner.style.display = "none";
            overlay.style.display = "none";
            document.body.style.overflow = "auto"; // enable scrolling
        }

        // Check if user already accepted
        if (!localStorage.getItem("cookieConsentAccepted")) {
            showBanner();
        }

        // When user accepts
        acceptBtn.addEventListener("click", function () {
            localStorage.setItem("cookieConsentAccepted", "true");
            hideBanner();
        });
    });

    // --- 1. Canvas Background Animation (Subtle Mesh) ---
    const canvas = document.getElementById('bg-canvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];

        function resize() {
            width = canvas.width = document.getElementById('client-search-section').offsetWidth;
            height = canvas.height = document.getElementById('client-search-section').offsetHeight;
        }

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.3;
                this.vy = (Math.random() - 0.5) * 0.3;
                this.size = Math.random() * 200 + 100;
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                if (this.x < -100) this.x = width + 100;
                if (this.x > width + 100) this.x = -100;
                if (this.y < -100) this.y = height + 100;
                if (this.y > height + 100) this.y = -100;
            }

            draw() {
                const gradient = ctx.createRadialGradient(this.x, this.y, 0, this.x, this.y, this.size);
                const color = Math.random() > 0.5 ? '237, 242, 255' : '240, 253, 244';

                gradient.addColorStop(0, `rgba(${color}, 0.4)`);
                gradient.addColorStop(1, `rgba(${color}, 0)`);

                ctx.fillStyle = gradient;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            for (let i = 0; i < 12; i++) {
                particles.push(new Particle());
            }
        }

        function animate() {
            ctx.clearRect(0, 0, width, height);
            particles.forEach(p => {
                p.update();
                p.draw();
            });
            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', resize);
        resize();
        initParticles();
        animate();
    }

    // --- 2. 3D Tilt Effect for Benefit Cards (Vanilla JS) ---
    const cards = document.querySelectorAll('.client-benefit-card[data-tilt]');

    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const xPct = x / rect.width;
            const yPct = y / rect.height;

            const xRotation = (yPct - 0.5) * -6;
            const yRotation = (xPct - 0.5) * 6;

            card.style.transform = `perspective(1000px) rotateX(${xRotation}deg) rotateY(${yRotation}deg) scale3d(1.02, 1.02, 1.02)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
        });
    });
</script>
</body>
</html>
<?php /**PATH Y:\Speeda - Versions\Speeda\resources\views/home.blade.php ENDPATH**/ ?>