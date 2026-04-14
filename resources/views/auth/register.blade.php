<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'he', 'ur', 'fa']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.authentication') }} | {{ config('app.name', 'Speeda') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- Meta (Facebook) Pixel --}}
    @include('partials.meta-pixel')
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: movePattern 20s linear infinite;
            pointer-events: none;
        }

        @keyframes movePattern {
            0% {
                transform: translate(0, 0);
            }

            100% {
                transform: translate(50px, 50px);
            }
        }

        .auth-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            z-index: 10;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 24px;
            padding: 2px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.05));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .auth-card:hover {
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.2);
        }

        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 200% 200%;
            animation: headerGradient 10s ease infinite;
            color: white;
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        @keyframes headerGradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            animation: shimmer 8s linear infinite;
        }

        @keyframes shimmer {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .auth-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            animation: slideLine 3s ease-in-out infinite;
        }

        @keyframes slideLine {

            0%,
            100% {
                transform: translateX(-100%);
            }

            50% {
                transform: translateX(100%);
            }
        }

        .auth-header h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.75rem;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.5px;
        }

        .auth-header p {
            opacity: 0.95;
            font-weight: 400;
            font-size: 1.05rem;
            position: relative;
            z-index: 1;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.625rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: #1f2937;
            letter-spacing: 0.01em;
        }

        .form-label i {
            font-size: 0.875rem;
            color: #667eea;
        }

        .form-input {
            width: 100%;
            padding: 0.9375rem 1.125rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
            background: #ffffff;
            color: #1f2937;
            font-weight: 400;
        }

        .form-input::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12), 0 2px 12px rgba(102, 126, 234, 0.08);
            transform: translateY(-1px);
        }

        .form-input:hover:not(:focus) {
            border-color: #cbd5e1;
            background: #fafafa;
        }

        .form-input.error {
            border-color: #ef4444;
            background: #fef2f2;
            animation: inputShake 0.4s ease;
        }

        @keyframes inputShake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-4px);
            }

            75% {
                transform: translateX(4px);
            }
        }

        .input-wrapper {
            position: relative;
        }

        /* Flex utilities for country code + phone */
        .flex {
            display: flex;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .w-1\/3 {
            width: 33.333333%;
        }

        .flex-1 {
            flex: 1 1 0%;
        }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Simple and Beautiful Eye Icon - Fixed duplication */
        .eye-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            cursor: pointer;
            color: #9ca3af;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            pointer-events: auto;
            border-radius: 50%;
            background: transparent;
        }

        .eye-icon:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-50%) scale(1.15);
        }

        .eye-icon i {
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .eye-icon:hover i {
            transform: scale(1.1);
        }

        /* Professional Dropdown Design */
        .custom-select {
            position: relative;
            width: 100%;
        }

        .select-trigger {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            box-sizing: border-box;
        }

        .select-trigger:hover {
            border-color: #9ca3af;
        }

        .select-trigger:focus,
        .select-trigger.active {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .select-value {
            color: #374151;
            font-size: 1rem;
        }

        .select-value.placeholder {
            color: #9ca3af;
        }

        .select-arrow {
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 6px solid #6b7280;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
        }

        .select-trigger.active .select-arrow {
            transform: rotate(180deg);
            border-top-color: #4f46e5;
        }

        .select-options {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
            max-height: 200px;
            overflow-y: auto;
        }

        .select-options.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .select-option {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #374151;
            font-size: 1rem;
        }

        .select-option:hover {
            background: #f3f4f6;
            color: #4f46e5;
            padding-left: 1.25rem;
        }

        .select-option.selected {
            background: #f8faff;
            color: #4f46e5;
            font-weight: 500;
        }

        .select-option:first-child {
            border-radius: 8px 8px 0 0;
        }

        .select-option:last-child {
            border-radius: 0 0 8px 8px;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
        }

        .error-message i {
            margin-right: 0.25rem;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .checkbox-input {
            margin-right: 0.875rem;
            width: 1.375rem;
            height: 1.375rem;
            border-radius: 6px;
            border: 2px solid #d1d5db;
            accent-color: #667eea;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .checkbox-input:hover {
            border-color: #667eea;
            transform: scale(1.05);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .checkbox-input:checked {
            transform: scale(1.1);
        }

        .checkbox-label {
            color: #374151;
            font-size: 0.9375rem;
            line-height: 1.6;
            cursor: pointer;
            user-select: none;
            font-weight: 500;
        }

        .auth-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 200% 200%;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1.0625rem 1.5rem;
            font-size: 1.0625rem;
            font-weight: 700;
            width: 100%;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.025em;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            box-shadow: 0 4px 14px rgba(102, 126, 234, 0.3);
        }

        .auth-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transition: left 0.5s ease;
        }

        .auth-button:hover::before {
            left: 100%;
        }

        .auth-button:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.5);
            background-position: 100% 50%;
        }

        .auth-button:active {
            transform: translateY(-1px) scale(0.99);
        }

        .auth-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .auth-button.loading {
            color: transparent;
        }

        .auth-button.loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: button-loading-spinner 1s ease infinite;
        }

        @keyframes button-loading-spinner {
            from {
                transform: rotate(0turn);
            }

            to {
                transform: rotate(1turn);
            }
        }

        .auth-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .auth-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .auth-link a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s ease;
        }

        .auth-link a:hover {
            color: #764ba2;
        }

        .auth-link a:hover::after {
            width: 100%;
        }

        .session-status {
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            background-color: #dbeafe;
            color: #1e40af;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .session-status i {
            margin-right: 0.5rem;
        }

        .error-session {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .input-error {
            display: none;
            align-items: flex-start;
            gap: 0.625rem;
            margin-top: 0.625rem;
            padding: 0.875rem 1rem;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-radius: 10px;
            font-size: 0.8125rem;
            color: #991b1b;
            font-weight: 500;
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
            animation: errorSlideIn 0.3s ease-out;
            position: relative;
            overflow: hidden;
        }

        .input-error::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
        }

        .input-error i {
            color: #ef4444;
            font-size: 1rem;
            margin-top: 2px;
            flex-shrink: 0;
        }

        @keyframes errorSlideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .role-selection {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .role-option {
            flex: 1;
            text-align: center;
            padding: 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
            background: white;
        }

        .role-option::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .role-option:hover::before {
            opacity: 1;
        }

        .role-option:hover {
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .role-option.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px) scale(1.02);
        }

        .role-option input {
            display: none;
        }

        .role-label {
            font-weight: 600;
            color: #4b5563;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.625rem;
            position: relative;
            z-index: 1;
        }

        .role-option.selected .role-label {
            color: #667eea;
        }

        .role-icon {
            font-size: 1.75rem;
            transition: transform 0.3s ease;
        }

        .role-option:hover .role-icon {
            transform: scale(1.1);
        }

        .role-option.selected .role-icon {
            transform: scale(1.15);
        }

        .tab-container {
            display: flex;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            border-radius: 14px;
            padding: 6px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .tab {
            flex: 1;
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: #6b7280;
            position: relative;
            overflow: hidden;
        }

        .tab::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .tab:hover::before {
            opacity: 1;
        }

        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
            transform: scale(1.02);
        }

        .phone-hint {
            font-size: 0.8125rem;
            color: #4b5563;
            margin-top: 0.625rem;
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.875rem 1rem;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 10px;
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08);
            position: relative;
            overflow: hidden;
        }

        .phone-hint::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
        }

        .phone-hint::after {
            content: 'ℹ️';
            font-size: 1rem;
            flex-shrink: 0;
            display: none;
        }

        /* Premium WhatsApp Country Badge Design */
        .whatsapp-country-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 46px;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
            border-radius: 12px;
            padding: 0 1rem;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3),
                0 2px 6px rgba(0, 0, 0, 0.1),
                inset 0 -2px 8px rgba(0, 0, 0, 0.15),
                inset 0 2px 4px rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: default;
        }

        .whatsapp-country-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: badgeShine 3s ease-in-out infinite;
        }

        @keyframes badgeShine {

            0%,
            100% {
                left: -100%;
            }

            50% {
                left: 100%;
            }
        }

        .whatsapp-country-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4),
                0 3px 8px rgba(0, 0, 0, 0.15),
                inset 0 -2px 8px rgba(0, 0, 0, 0.15),
                inset 0 2px 4px rgba(255, 255, 255, 0.25);
        }

        .whatsapp-country-badge .flag-emoji {
            font-size: 1.5rem;
            line-height: 1;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            animation: flagWave 2.5s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }

        @keyframes flagWave {

            0%,
            100% {
                transform: rotate(0deg) scale(1);
            }

            25% {
                transform: rotate(-5deg) scale(1.05);
            }

            75% {
                transform: rotate(5deg) scale(1.05);
            }
        }

        .whatsapp-country-badge .country-code {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3),
                0 1px 2px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
        }

        .whatsapp-country-badge .country-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.95);
            letter-spacing: 1.5px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.15);
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            position: relative;
            z-index: 1;
        }

        .whatsapp-country-badge::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .whatsapp-country-badge {
                height: 42px;
                gap: 8px;
            }

            .whatsapp-country-badge .flag-emoji {
                font-size: 1.3rem;
            }

            .whatsapp-country-badge .country-code {
                font-size: 1rem;
            }

            .whatsapp-country-badge .country-name {
                font-size: 0.75rem;
                padding: 2px 6px;
            }
        }

        .profession-field {
            display: none;
        }

        .city-field {
            display: none;
        }

        .phone-format {
            font-size: 0.8125rem;
            color: #065f46;
            margin-top: 0.625rem;
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.875rem 1rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 10px;
            font-weight: 500;
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);
            animation: successSlideIn 0.3s ease-out;
            position: relative;
            overflow: hidden;
        }

        .phone-format::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
        }

        .phone-format i {
            color: #10b981;
            font-size: 1rem;
            flex-shrink: 0;
            margin-top: 2px;
        }

        @keyframes successSlideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =============== WhatsApp-Style Input Container =============== */
        .whatsapp-input-container {
            display: flex;
            gap: 0.875rem;
            align-items: stretch;
            max-width: 100%;
        }

        .whatsapp-country-selector {
            flex: 0 0 auto;
            min-width: 140px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .whatsapp-input-field {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 0;
        }

        .whatsapp-mobile-input .form-input {
            font-size: 1rem;
            letter-spacing: 0.5px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            padding: 0.875rem 1.25rem 0.875rem 3.25rem;
            font-weight: 500;
            color: #1f2937;
        }

        .whatsapp-mobile-input .form-input::placeholder {
            color: #9ca3af;
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        .whatsapp-mobile-input .form-input:focus {
            border-color: #06b6d4;
            background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1),
                0 4px 12px rgba(6, 182, 212, 0.15);
            outline: none;
        }

        .whatsapp-mobile-input .form-input:valid:not(:placeholder-shown) {
            border-color: #10b981;
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1),
                0 2px 8px rgba(16, 185, 129, 0.08);
        }

        .whatsapp-mobile-input .form-input:invalid:not(:placeholder-shown) {
            border-color: #ef4444;
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1),
                0 2px 8px rgba(239, 68, 68, 0.08);
        }

        .whatsapp-mobile-input {
            position: relative;
        }

        .whatsapp-mobile-input .input-icon {
            font-size: 1.1rem;
            color: #06b6d4;
            transition: all 0.3s ease;
            left: 1rem;
        }

        .whatsapp-mobile-input:has(.form-input:focus) .input-icon {
            color: #0891b2;
            transform: scale(1.1);
        }

        .whatsapp-mobile-input:has(.form-input:valid:not(:placeholder-shown)) .input-icon {
            color: #10b981;
        }

        .whatsapp-icon {
            color: #25d366 !important;
        }

        .whatsapp-mobile-input:has(.form-input:focus) .whatsapp-icon {
            color: #22c55e !important;
            transform: scale(1.15);
        }

        /* WhatsApp Hint with Icon */
        .whatsapp-hint {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            color: #4b5563;
            margin-top: 0.5rem;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 10px;
            line-height: 1.4;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.08);
            border-left: 4px solid #3b82f6;
            animation: hintSlideIn 0.3s ease-out;
        }

        .whatsapp-hint i {
            flex-shrink: 0;
            font-size: 0.95rem;
            color: #3b82f6;
        }

        .whatsapp-success {
            display: flex !important;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            color: #065f46;
            margin-top: 0.5rem;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 10px;
            line-height: 1.4;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.08);
            border-left: 4px solid #10b981;
            animation: hintSlideIn 0.3s ease-out;
        }

        .whatsapp-success i {
            color: #10b981;
            font-size: 0.95rem;
        }

        @keyframes hintSlideIn {
            from {
                opacity: 0;
                transform: translateX(-8px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* RTL Support for Arabic */
        [dir="rtl"] .whatsapp-input-container {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .whatsapp-country-selector {
            flex: 0 0 auto;
        }

        [dir="rtl"] .whatsapp-mobile-input .form-input {
            padding-right: 3.25rem;
            padding-left: 1.25rem;
            text-align: right;
            direction: rtl;
        }

        [dir="rtl"] .whatsapp-hint,
        [dir="rtl"] .whatsapp-success {
            flex-direction: row-reverse;
            border-left: none;
            border-right: 4px solid #3b82f6;
            text-align: right;
            direction: rtl;
        }

        [dir="rtl"] .whatsapp-success {
            border-right: 4px solid #10b981;
        }

        [dir="rtl"] .whatsapp-mobile-input .input-icon {
            left: auto;
            right: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 640px) {
            .whatsapp-input-container {
                flex-direction: column;
                gap: 1rem;
            }

            .whatsapp-country-selector {
                min-width: 100%;
                flex: 1;
            }

            .whatsapp-input-field {
                flex: 1;
            }

            .whatsapp-mobile-input .form-input {
                font-size: 1rem;
                padding: 0.85rem 1.1rem 0.85rem 3rem;
            }

            .whatsapp-mobile-input .input-icon {
                font-size: 1rem;
                left: 0.875rem;
            }

            .whatsapp-hint,
            .whatsapp-success {
                font-size: 0.8rem;
                padding: 0.65rem 0.875rem;
            }
        }

        .password-strength {
            height: 5px;
            margin-top: 0.5rem;
            border-radius: 3px;
            background-color: #e5e7eb;
            overflow: hidden;
        }

        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }

        .password-strength-text {
            font-size: 0.8125rem;
            margin-top: 0.625rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.625rem 0.875rem;
            background: linear-gradient(135deg, #fafafa 0%, #f3f4f6 100%);
            border-radius: 8px;
            font-weight: 500;
            color: #6b7280;
        }

        .social-login {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-button {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background: white;
            color: #374151;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .social-button::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .social-button:hover::before {
            opacity: 1;
        }

        .social-button:hover {
            background: #fafafa;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .social-button i {
            margin-right: 0.625rem;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .social-button:hover i {
            transform: scale(1.15);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            padding: 0 1rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .terms-container {
            margin-bottom: 1.5rem;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            transform: translateX(120%);
            transition: transform 0.3s ease-out;
            z-index: 10001;
            max-width: 350px;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            border-left: 4px solid #10b981;
        }

        .toast.error {
            border-left: 4px solid #ef4444;
        }

        .toast.info {
            border-left: 4px solid #3b82f6;
        }

        .toast-icon {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .toast.success .toast-icon {
            color: #10b981;
        }

        .toast.error .toast-icon {
            color: #ef4444;
        }

        .toast.info .toast-icon {
            color: #3b82f6;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .toast-message {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .toast-close {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0;
            margin-left: 0.5rem;
        }

        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #4f46e5;
            color: white;
            padding: 8px;
            z-index: 100;
            transition: top 0.3s;
        }

        .skip-link:focus {
            top: 0;
        }

        /* Standalone Language Switcher for Register Page */
        .register-language-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
        }

        .language-switcher-standalone {
            position: relative;
        }

        .language-btn-standalone {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 14px;
            padding: 10px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: #667eea;
            backdrop-filter: blur(12px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        .language-btn-standalone:hover {
            background: rgba(255, 255, 255, 1);
            border-color: rgba(102, 126, 234, 0.8);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .language-dropdown-standalone {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            margin-top: 8px;
            min-width: 140px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            display: none;
            overflow: hidden;
            z-index: 1001;
        }

        .language-dropdown-standalone.show {
            display: block;
            animation: dropdownSlide 0.2s ease-out;
        }

        .language-form-standalone {
            width: 100%;
            margin: 0;
        }

        .language-option-standalone {
            width: 100%;
            padding: 0.75rem 1rem;
            background: white;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .language-option-standalone:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
            color: #667eea;
            padding-left: 1.5rem;
            transform: translateX(4px);
        }

        .language-option-standalone:last-child {
            border-bottom: none;
        }

        .language-flag-standalone {
            font-size: 16px;
            min-width: 20px;
        }

        .language-name-standalone {
            font-size: 0.85rem;
            font-weight: 500;
            flex: 1;
        }

        .current-language-standalone {
            font-weight: 600;
        }
    </style>
</head>

<body>
    <!-- Update HTML lang attribute dynamically -->
    <script>
        document.documentElement.lang = '{{ app()->getLocale() }}';
        document.documentElement.dir = '{{ app()->getLocale() === "ar" ? "rtl" : "ltr" }}';
    </script>

    <!-- Standalone Language Button for Register Page -->
    @php($authLocaleRedirect = request()->fullUrl())
    <div class="register-language-switcher">
        <div class="language-switcher-standalone" id="languageSwitcherStandalone">
            <button type="button" class="language-btn-standalone" onclick="toggleLanguageMenuStandalone()"
                aria-haspopup="true" aria-expanded="false">
                <span class="current-language-standalone">
                    @if(app()->getLocale() === 'en') 🇺🇸 EN
                    @elseif(app()->getLocale() === 'ar') 🇸🇦 AR
                    @elseif(app()->getLocale() === 'fr') 🇫🇷 FR
                    @endif
                </span>
                <i class="fas fa-chevron-down"></i>
            </button>

            <div class="language-dropdown-standalone" id="languageDropdownStandalone">
                @if(app()->getLocale() !== 'en')
                    <a href="#" onclick="event.preventDefault(); switchLanguageAuth('en');"
                        class="language-option-standalone">
                        <span class="language-flag-standalone">🇺🇸</span>
                        <span class="language-name-standalone">{{ __('language.english') }}</span>
                    </a>
                @endif

                @if(app()->getLocale() !== 'ar')
                    <a href="#" onclick="event.preventDefault(); switchLanguageAuth('ar');"
                        class="language-option-standalone">
                        <span class="language-flag-standalone">🇸🇦</span>
                        <span class="language-name-standalone">{{ __('language.arabic') }}</span>
                    </a>
                @endif

                @if(app()->getLocale() !== 'fr')
                    <a href="#" onclick="event.preventDefault(); switchLanguageAuth('fr');"
                        class="language-option-standalone">
                        <span class="language-flag-standalone">🇫🇷</span>
                        <span class="language-name-standalone">{{ __('language.french') }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="auth-card" id="main-content">
        <div class="auth-header">
            <h1 id="auth-title">{{ __('auth.welcome_back') }}</h1>
            <p id="auth-subtitle">{{ __('auth.sign_in_subtitle') }}</p>
        </div>

        {{-- Unified Error Handler --}}
        <x-error-handler />

        <div class="tab-container" role="tablist">
            <div class="tab active" id="login-tab" role="tab" aria-selected="true" aria-controls="login-panel"
                tabindex="0">{{ __('auth.login_tab') }}</div>
            <div class="tab" id="register-tab" role="tab" aria-selected="false" aria-controls="register-panel"
                tabindex="0">{{ __('auth.register_tab') }}</div>
        </div>

        <div class="form-container">
            <div class="session-status mb-4" id="session-status" style="display: none;" role="alert">
            </div>

            <form method="POST" action="{{ route('login') }}" id="login-form" role="tabpanel" id="login-panel"
                aria-labelledby="login-tab">
                @csrf

                <div class="form-group" id="mobile-field">
                    <label for="login-field" class="form-label">
                        <i class="fas fa-user-circle"></i>
                        {{ __('auth.email_or_mobile') }}
                    </label>
                    <div class="input-wrapper">
                        <input id="login-field" class="form-input" type="text" name="login" value="{{ old('login') }}"
                            required autofocus autocomplete="username" aria-describedby="login-field-error login-hint">
                        <i class="fas fa-envelope input-icon" id="login-field-icon"></i>
                    </div>
                    <div class="phone-hint" id="login-hint">
                        <span id="client-login-hint">{{ __('auth.email_address') }}</span>
                        <span id="provider-login-hint" style="display: none;">{{ __('auth.email_or_mobile') }}</span>
                    </div>
                    @if ($errors->has('login'))
                        <div class="input-error" id="login-field-error" style="display: flex;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $errors->first('login') }}</span>
                        </div>
                    @endif
                </div>

                <div class="form-group" id="whatsapp-field">
                    <label class="form-label">{{ __('auth.profession') }}</label>
                    <div class="role-selection" role="radiogroup" aria-labelledby="role-label">
                        <div class="role-option selected" id="login-client-option" role="radio" aria-checked="true"
                            tabindex="0">
                            <input type="radio" id="login_role_client" name="role" value="client" checked>
                            <label for="login_role_client" class="role-label">
                                <i class="fas fa-user role-icon"></i>
                                {{ __('auth.client') }}
                            </label>
                        </div>
                        <div class="role-option" id="login-provider-option" role="radio" aria-checked="false"
                            tabindex="0">
                            <input type="radio" id="login_role_provider" name="role" value="service_provider">
                            <label for="login_role_provider" class="role-label">
                                <i class="fas fa-tools role-icon"></i>
                                {{ __('auth.service_provider') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="login-password" class="form-label">
                        <i class="fas fa-lock"></i>
                        {{ __('auth.password') }}
                    </label>
                    <div class="input-wrapper">
                        <input id="login-password" class="form-input" type="password" name="password" required
                            autocomplete="current-password" aria-describedby="login-password-error">
                        <div class="eye-icon" id="login-password-toggle">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    @if ($errors->has('password'))
                        <div class="input-error" id="login-password-error" style="display: flex;">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $errors->first('password') }}</span>
                        </div>
                    @endif
                </div>

                <div class="checkbox-container">
                    <input id="remember_me" type="checkbox" class="checkbox-input" name="remember">
                    <label for="remember_me" class="checkbox-label">{{ __('auth.remember_me') }}</label>
                </div>

                <div class="flex items-center justify-between mt-4">
                    @if (Route::has('password.request'))
                        <div class="auth-link">
                            <a href="{{ route('password.request') }}">
                                {{ __('auth.forgot_password') }}
                            </a>
                        </div>
                    @endif

                    <button type="submit" class="auth-button" id="login-button">
                        {{ __('auth.login_tab') }}
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('register') }}" id="register-form" style="display: none;"
                role="tabpanel" id="register-panel" aria-labelledby="register-tab">
                @csrf

                <div class="social-login">
                    <button type="button" class="social-button" id="google-signup">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-button" id="facebook-signup">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </button>
                </div>

                <div class="divider">
                    <span>{{ __('auth.or') }}</span>
                </div>

                {{-- @change 2026-04-12 TASK-2 | Hid client-only registration down to essential credentials while keeping provider fields available behind role selection | Client signup should no longer collect phone details during registration | risk:LOW --}}
                <div class="form-group" id="register-name-field">
                    <label for="name" class="form-label">
                        <i class="fas fa-user"></i>
                        {{ __('auth.full_name') }}
                    </label>
                    <div class="input-wrapper">
                        <input id="name" class="form-input" type="text" name="name" value="{{ old('name') }}" required
                            autofocus autocomplete="name" aria-describedby="name-error">
                        <i class="fas fa-user input-icon" id="name-icon"></i>
                    </div>
                    <div class="input-error" id="name-error">
                        @if ($errors->has('name'))
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('name') }}
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label for="register-email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        {{ __('auth.email_address') }}
                    </label>
                    <div class="input-wrapper">
                        <input id="register-email" class="form-input" type="email" name="email"
                            value="{{ old('email') }}" required autocomplete="username"
                            aria-describedby="register-email-error">
                        <i class="fas fa-envelope input-icon" id="email-icon"></i>
                    </div>
                    <div class="input-error" id="register-email-error">
                        @if ($errors->has('email'))
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('email') }}
                        @endif
                    </div>
                </div>

                <div class="form-group" id="register-mobile-field">
                    <label for="mobile" class="form-label">
                        <i class="fas fa-mobile-alt"></i>
                        {{ __('auth.mobile_number') }}
                        <span class="phone-required-indicator" id="phone-required" style="display: none;">
                            <span class="text-red-500">*</span>
                        </span>
                        <span class="phone-optional-indicator" id="phone-optional">
                            <span class="text-gray-500">({{ __('general.optional') }})</span>
                        </span>
                    </label>
                    <div class="whatsapp-input-container">
                        <div class="whatsapp-country-selector">
                            <div class="whatsapp-country-badge">
                                <span class="flag-emoji">🍁</span>
                                <span class="country-code">+1</span>
                                <span class="country-name">CA</span>
                            </div>
                            <input type="hidden" name="mobile_country_code" value="+1">
                        </div>
                        <div class="whatsapp-input-field">
                            <div class="input-wrapper whatsapp-mobile-input">
                                <input id="mobile" class="form-input" type="tel" name="mobile"
                                    value="{{ old('mobile') }}" autocomplete="tel" placeholder="613-520-4877"
                                    pattern="[0-9\-\s]{10,20}" minlength="10" maxlength="15"
                                    aria-describedby="mobile-error phone-format">
                                <i class="fas fa-phone-alt input-icon" id="phone-icon"></i>
                            </div>
                            <div class="phone-hint whatsapp-hint" id="phone-hint">
                                <i class="fas fa-lightbulb"></i>
                                <span>{{ __('auth.phone_auto_format') ?? 'Type your phone number - we\'ll format it automatically' }}</span>
                            </div>
                            <div class="phone-format whatsapp-success" id="phone-format" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ __('validation.valid_canadian_format') ?? 'Perfect! Valid format' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="input-error" id="mobile-error">
                        @if ($errors->has('mobile'))
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('mobile') }}
                        @endif
                    </div>
                </div>

                <div class="form-group" id="register-whatsapp-field">
                    <label for="whatsapp_number" class="form-label">
                        <i class="fab fa-whatsapp"></i>
                        {{ __('service_provider.whatsapp_number') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="whatsapp-input-container">
                        <!-- Country Code Badge -->
                        <div class="whatsapp-country-selector">
                            <div class="whatsapp-country-badge">
                                <span class="flag-emoji">🍁</span>
                                <span class="country-code">+1</span>
                                <span class="country-name">CA</span>
                            </div>
                            <input type="hidden" id="whatsapp_country_code" name="whatsapp_country_code" value="+1">
                            @if ($errors->has('whatsapp_country_code'))
                                <div class="input-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('whatsapp_country_code') }}
                                </div>
                            @endif
                        </div>

                        <!-- WhatsApp Number Input -->
                        <div class="whatsapp-input-field">
                            <div class="input-wrapper whatsapp-mobile-input">
                                <input id="whatsapp_number" class="form-input" type="tel" name="whatsapp_number"
                                    value="{{ old('whatsapp_number') }}" autocomplete="tel"
                                    placeholder="514-123-4567" aria-describedby="whatsapp-error whatsapp-hint">
                                <i class="fab fa-whatsapp input-icon whatsapp-icon" id="whatsapp-icon"></i>
                            </div>
                            <div class="phone-hint whatsapp-hint" id="whatsapp-hint">
                                <i class="fas fa-lightbulb"></i>
                                <span>Enter your WhatsApp number without country code</span>
                            </div>
                            @if ($errors->has('whatsapp_number'))
                                <div class="input-error" id="whatsapp-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('whatsapp_number') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('auth.profession') }}</label>
                    <div class="role-selection" role="radiogroup" aria-labelledby="role-label">
                        <div class="role-option selected" id="client-option" role="radio" aria-checked="true"
                            tabindex="0">
                            <input type="radio" id="client" name="role" value="client" checked>
                            <label for="client" class="role-label">
                                <i class="fas fa-user role-icon"></i>
                                {{ __('auth.client') }}
                            </label>
                        </div>
                        <div class="role-option" id="provider-option" role="radio" aria-checked="false" tabindex="0">
                            <input type="radio" id="provider" name="role" value="service_provider">
                            <label for="provider" class="role-label">
                                <i class="fas fa-tools role-icon"></i>
                                {{ __('auth.service_provider') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group profession-field" id="profession-field">
                    <label for="profession" class="form-label">
                        <i class="fas fa-briefcase"></i>
                        {{ __('auth.profession') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="custom-select" id="profession-select">
                        <div class="select-trigger" id="profession-trigger">
                            <span class="select-value placeholder"
                                id="profession-value">{{ __('auth.select_profession') }}</span>
                            <div class="select-arrow"></div>
                        </div>
                        <div class="select-options" id="profession-options">
                            <div class="select-option" data-value="">{{ __('auth.select_profession') }}</div>
                            @if(isset($professionGroups) && $professionGroups->count())
                                @foreach($professionGroups as $groupName => $groupProfessions)
                                    <div class="select-group-header" style="padding: 0.5rem 1rem; font-weight: 700; color: #667eea; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8faff; border-top: 1px solid #e5e7eb; cursor: default; pointer-events: none;">
                                        {{ $groupName }}
                                    </div>
                                    @foreach($groupProfessions as $p)
                                        <div class="select-option" data-value="{{ $p->id }}" style="padding-inline-start: 1.5rem;">
                                            {{ $p->localized_name }}
                                        </div>
                                    @endforeach
                                @endforeach
                            @elseif(isset($professions) && $professions)
                                {{-- Fallback: flat list if grouping data unavailable --}}
                                @foreach($professions as $p)
                                    <div class="select-option" data-value="{{ $p->id }}">
                                        {{ $p->localized_name }}
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <input type="hidden" name="profession" id="profession-input" value="{{ old('profession') }}">
                    </div>
                    <div class="input-error" id="profession-error">
                        @if ($errors->has('profession'))
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('profession') }}
                        @endif
                    </div>
                </div>

                <div class="form-group city-field" id="city-field">
                    <label for="city" class="form-label">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ __('auth.city') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="custom-select" id="city-select">
                        <div class="select-trigger" id="city-trigger">
                            <span class="select-value placeholder" id="city-value">{{ __('auth.select_city') }}</span>
                            <div class="select-arrow"></div>
                        </div>
                        <div class="select-options" id="city-options">
                            <div class="select-option" data-value="">{{ __('auth.select_city') }}</div>
                            <div class="select-option" data-value="Laval">Laval</div>
                            <div class="select-option" data-value="Montreal">Montreal</div>
                            <div class="select-option" data-value="Ottawa">Ottawa</div>
                            <div class="select-option" data-value="Gatineau">Gatineau</div>
                        </div>
                        <input type="hidden" name="city" id="city-input" value="{{ old('city') }}">
                    </div>
                    <div class="input-error" id="city-error">
                        @if ($errors->has('city'))
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('city') }}
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label for="register-password" class="form-label">
                        <i class="fas fa-lock"></i>
                        {{ __('auth.password') }}
                    </label>
                    <div class="input-wrapper">
                        <input id="register-password" class="form-input" type="password" name="password" required
                            autocomplete="new-password"
                            aria-describedby="register-password-error password-strength-text">
                        <div class="eye-icon" id="register-password-toggle">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-meter" id="password-strength-meter"></div>
                    </div>
                    <div class="password-strength-text" id="password-strength-text">
                        <span>{{ __('auth.weak') }}</span>
                        <span>{{ __('auth.strong') }}</span>
                    </div>
                    <div class="input-error" id="register-password-error">
                        @if ($errors->has('password'))
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('password') }}
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        <i class="fas fa-lock"></i>
                        {{ __('auth.confirm_password') }}
                    </label>
                    <div class="input-wrapper">
                        <input id="password_confirmation" class="form-input" type="password"
                            name="password_confirmation" required autocomplete="new-password"
                            aria-describedby="password-confirmation-error">
                        <div class="eye-icon" id="confirm-password-toggle">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="input-error" id="password-confirmation-error">
                        @if ($errors->has('password_confirmation'))
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('password_confirmation') }}
                        @endif
                    </div>
                </div>

                <div class="terms-container" id="register-terms-field">
                    <div class="checkbox-container">
                        <input id="terms" type="checkbox" class="checkbox-input" name="terms" required>
                        <label for="terms" class="checkbox-label">
                            {{ __('auth.i_agree_to') }}
                            <a href="{{ route('terms-of-service') }}" target="_blank"
                                style="color: #667eea; text-decoration: underline; font-weight: 600;">{{ __('auth.terms_of_service') }}</a>
                            {{ __('auth.and') }}
                            <a href="{{ route('privacy-policy') }}" target="_blank"
                                style="color: #667eea; text-decoration: underline; font-weight: 600;">{{ __('auth.privacy_policy') }}</a>
                        </label>
                    </div>
                    <div class="input-error" id="terms-error">
                        @if ($errors->has('terms'))
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('terms') }}
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end mt-4">
                    <div class="auth-link">
                        <a href="{{route("register")}}" id="already-registered-link">
                            {{ __('auth.already_registered') }}
                        </a>
                    </div>

                    <button type="submit" class="auth-button" id="register-button">
                        {{ __('auth.register_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast" id="toast">
        <i class="toast-icon fas"></i>
        <div class="toast-content">
            <div class="toast-title"></div>
            <div class="toast-message"></div>
        </div>
        <button class="toast-close" id="toast-close">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <script>
        // DOM Elements
        const loginTab = document.getElementById('login-tab');
        const registerTab = document.getElementById('register-tab');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const authTitle = document.getElementById('auth-title');
        const authSubtitle = document.getElementById('auth-subtitle');
        const alreadyRegisteredLink = document.getElementById('already-registered-link');
        const mobileInput = document.getElementById('mobile');
        const nameField = document.getElementById('register-name-field');
        const mobileField = document.getElementById('register-mobile-field');
        const whatsappField = document.getElementById('register-whatsapp-field');
        const termsField = document.getElementById('register-terms-field');
        const termsInput = document.getElementById('terms');
        const phoneFormat = document.getElementById('phone-format');
        const mobileError = document.getElementById('mobile-error');
        const registerButton = document.getElementById('register-button');
        const loginButton = document.getElementById('login-button');
        const registerPasswordInput = document.getElementById('register-password');
        const passwordStrengthMeter = document.getElementById('password-strength-meter');
        const passwordStrengthText = document.getElementById('password-strength-text');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const loginPasswordToggle = document.getElementById('login-password-toggle');
        const registerPasswordToggle = document.getElementById('register-password-toggle');
        const confirmPasswordToggle = document.getElementById('confirm-password-toggle');
        const toast = document.getElementById('toast');
        const toastClose = document.getElementById('toast-close');
        const sessionStatus = document.getElementById('session-status');
        const googleSignup = document.getElementById('google-signup');
        const facebookSignup = document.getElementById('facebook-signup');
        const professionTrigger = document.getElementById('profession-trigger');
        const professionOptions = document.getElementById('profession-options');
        const professionValue = document.getElementById('profession-value');
        const professionInput = document.getElementById('profession-input');
        const cityTrigger = document.getElementById('city-trigger');
        const cityOptions = document.getElementById('city-options');
        const cityValue = document.getElementById('city-value');
        const cityInput = document.getElementById('city-input');
        const whatsappInput = document.getElementById('whatsapp_number');

        // Auto-select service provider role if URL parameter is present
        const urlParams = new URLSearchParams(window.location.search);
        const userType = urlParams.get('type');

        // Check URL parameter for initial tab selection
        const tabParam = urlParams.get('tab');
        if (tabParam === 'register') {
            switchToRegister();
        }

        // Toast notification system
        function showToast(message, type = 'info', title = '') {
            const toastIcon = toast.querySelector('.toast-icon');
            const toastTitle = toast.querySelector('.toast-title');
            const toastMessage = toast.querySelector('.toast-message');

            // Reset classes
            toast.className = 'toast';
            toast.classList.add(type);

            // Set icon based on type
            toastIcon.className = 'toast-icon fas';
            if (type === 'success') {
                toastIcon.classList.add('fa-check-circle');
            } else if (type === 'error') {
                toastIcon.classList.add('fa-exclamation-circle');
            } else {
                toastIcon.classList.add('fa-info-circle');
            }

            // Set content
            toastTitle.textContent = title;
            toastMessage.textContent = message;

            // Show toast
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);

            // Auto hide after 5 seconds
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }

        // Close toast when clicking close button
        toastClose.addEventListener('click', () => {
            toast.classList.remove('show');
        });

        // Tab switching functionality
        loginTab.addEventListener('click', switchToLogin);
        registerTab.addEventListener('click', switchToRegister);
        alreadyRegisteredLink.addEventListener('click', function (e) {
            e.preventDefault();
            switchToLogin();
        });

        // Keyboard navigation for tabs
        loginTab.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                switchToLogin();
            }
        });

        registerTab.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                switchToRegister();
            }
        });

        function switchToLogin() {
            loginForm.style.display = 'block';
            registerForm.style.display = 'none';
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginTab.setAttribute('aria-selected', 'true');
            registerTab.setAttribute('aria-selected', 'false');
            authTitle.textContent = "{{ __('auth.welcome_back') }}";
            authSubtitle.textContent = "{{ __('auth.sign_in_subtitle') }}";
            // Reset scroll
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function switchToRegister() {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerTab.setAttribute('aria-selected', 'true');
            loginTab.setAttribute('aria-selected', 'false');
            authTitle.textContent = "{{ __('auth.create_account') }}";
            authSubtitle.textContent = "{{ __('auth.sign_up_subtitle') }}";
            // Reset scroll
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // --- Role Selection for LOGIN Form ---
        document.getElementById('login-client-option').addEventListener('click', () => selectLoginRole('client'));
        document.getElementById('login-provider-option').addEventListener('click', () => selectLoginRole('service_provider'));

        // Keyboard navigation for login role selection
        document.getElementById('login-client-option').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectLoginRole('client');
            }
        });

        document.getElementById('login-provider-option').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectLoginRole('service_provider');
            }
        });

        function selectLoginRole(role) {
            document.getElementById('login-client-option').classList.remove('selected');
            document.getElementById('login-provider-option').classList.remove('selected');

            document.getElementById('login-client-option').setAttribute('aria-checked', 'false');
            document.getElementById('login-provider-option').setAttribute('aria-checked', 'false');

            const clientHint = document.getElementById('client-login-hint');
            const providerHint = document.getElementById('provider-login-hint');
            const loginIcon = document.getElementById('login-field-icon');

            if (role === 'client') {
                document.getElementById('login-client-option').classList.add('selected');
                document.getElementById('login_role_client').checked = true;
                document.getElementById('login-client-option').setAttribute('aria-checked', 'true');
                clientHint.style.display = 'inline';
                providerHint.style.display = 'none';
                loginIcon.className = 'fas fa-envelope input-icon';
            } else {
                document.getElementById('login-provider-option').classList.add('selected');
                document.getElementById('login_role_provider').checked = true;
                document.getElementById('login-provider-option').setAttribute('aria-checked', 'true');
                clientHint.style.display = 'none';
                providerHint.style.display = 'inline';
                loginIcon.className = 'fas fa-user input-icon';
            }
        }

        // --- Role Selection for REGISTER Form ---
        document.getElementById('client-option').addEventListener('click', () => selectRegisterRole('client'));
        document.getElementById('provider-option').addEventListener('click', () => selectRegisterRole('service_provider'));

        // Keyboard navigation for register role selection
        document.getElementById('client-option').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectRegisterRole('client');
            }
        });

        document.getElementById('provider-option').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectRegisterRole('service_provider');
            }
        });

        // @change 2026-04-12 TASK-2 | Updated register role toggling to hide client phone/name/terms fields and target the correct register form elements | Keep client registration limited to essential credentials without breaking provider mode | risk:LOW
        function selectRegisterRole(role) {
            document.getElementById('client-option').classList.remove('selected');
            document.getElementById('provider-option').classList.remove('selected');

            document.getElementById('client-option').setAttribute('aria-checked', 'false');
            document.getElementById('provider-option').setAttribute('aria-checked', 'false');

            const professionField = document.getElementById('profession-field');
            const cityField = document.getElementById('city-field');
            const phoneRequired = document.getElementById('phone-required');
            const phoneOptional = document.getElementById('phone-optional');
            const phoneHint = document.getElementById('phone-hint');
            const whatsappHint = document.getElementById('whatsapp-hint');

            if (role === 'service_provider') {
                document.getElementById('provider-option').classList.add('selected');
                document.getElementById('provider').checked = true;
                document.getElementById('provider-option').setAttribute('aria-checked', 'true');
                nameField.style.display = 'block';
                professionField.style.display = 'block';
                cityField.style.display = 'block';
                mobileField.style.display = 'block';
                whatsappField.style.display = 'block';
                termsField.style.display = 'block';
                // Phone is required for service providers
                phoneRequired.style.display = 'inline';
                phoneOptional.style.display = 'none';
                phoneHint.textContent = 'Please enter a valid Canadian mobile number (10 digits) - Required for service providers';
                document.getElementById('name').setAttribute('required', 'required');
                // Add required attribute to phone input
                mobileInput.setAttribute('required', 'required');
                whatsappInput?.setAttribute('required', 'required');
                termsInput.setAttribute('required', 'required');
                if (whatsappHint) {
                    whatsappHint.style.display = 'flex';
                }
            } else {
                document.getElementById('client-option').classList.add('selected');
                document.getElementById('client').checked = true;
                document.getElementById('client-option').setAttribute('aria-checked', 'true');
                nameField.style.display = 'none';
                professionField.style.display = 'none';
                cityField.style.display = 'none';
                mobileField.style.display = 'none';
                whatsappField.style.display = 'none';
                termsField.style.display = 'none';
                // Phone is optional for clients
                phoneRequired.style.display = 'none';
                phoneOptional.style.display = 'inline';
                phoneHint.textContent = 'Phone number is no longer required for client registration';
                document.getElementById('name').removeAttribute('required');
                // Remove required attribute from phone input
                mobileInput.removeAttribute('required');
                termsInput.removeAttribute('required');
                mobileInput.value = '';
                mobileInput.classList.remove('error');
                mobileError.textContent = '';
                if (whatsappInput) {
                    whatsappInput.removeAttribute('required');
                    whatsappInput.value = '';
                    whatsappInput.classList.remove('error');
                }
                const whatsappError = document.getElementById('whatsapp-error');
                if (whatsappError) {
                    whatsappError.innerHTML = '';
                }
                if (whatsappHint) {
                    whatsappHint.style.display = 'none';
                }
            }
        }

        // Auto-select service provider and switch to register tab if URL parameter is present
        if (userType === 'service-provider') {
            // Switch to register tab first
            switchToRegister();
            // Then select service provider role
            setTimeout(() => {
                selectRegisterRole('service_provider');
            }, 100);
        }

        // Simple and Beautiful Eye Icon Toggle - Fixed duplication
        function setupEyeToggle(toggleElement, inputId) {
            let isToggling = false;

            toggleElement.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                if (isToggling) return;
                isToggling = true;

                const input = document.getElementById(inputId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }

                setTimeout(() => {
                    isToggling = false;
                }, 300);
            });
        }

        // Initialize eye toggles
        setupEyeToggle(loginPasswordToggle, 'login-password');
        setupEyeToggle(registerPasswordToggle, 'register-password');
        setupEyeToggle(confirmPasswordToggle, 'password_confirmation');

        // Professional Custom Dropdown - Profession
        let isProfessionDropdownOpen = false;

        professionTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (!isProfessionDropdownOpen) {
                openProfessionDropdown();
            } else {
                closeProfessionDropdown();
            }
        });

        function openProfessionDropdown() {
            isProfessionDropdownOpen = true;
            professionTrigger.classList.add('active');
            professionOptions.classList.add('show');
        }

        function closeProfessionDropdown() {
            isProfessionDropdownOpen = false;
            professionTrigger.classList.remove('active');
            professionOptions.classList.remove('show');
        }

        // Handle profession option selection
        document.querySelectorAll('#profession-options .select-option').forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const value = this.getAttribute('data-value');
                const text = this.textContent;

                // Update display
                professionValue.textContent = text;
                professionValue.classList.remove('placeholder');

                // Update hidden input
                professionInput.value = value;

                // Update selected state
                document.querySelectorAll('#profession-options .select-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');

                // Close dropdown
                closeProfessionDropdown();
            });
        });

        // Professional Custom Dropdown - City
        let isCityDropdownOpen = false;

        cityTrigger.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (!isCityDropdownOpen) {
                openCityDropdown();
            } else {
                closeCityDropdown();
            }
        });

        function openCityDropdown() {
            isCityDropdownOpen = true;
            cityTrigger.classList.add('active');
            cityOptions.classList.add('show');
        }

        function closeCityDropdown() {
            isCityDropdownOpen = false;
            cityTrigger.classList.remove('active');
            cityOptions.classList.remove('show');
        }

        // Handle city option selection
        document.querySelectorAll('#city-options .select-option').forEach(option => {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const value = this.getAttribute('data-value');
                const text = this.textContent;

                // Update display
                cityValue.textContent = text;
                cityValue.classList.remove('placeholder');

                // Update hidden input
                cityInput.value = value;

                // Update selected state
                document.querySelectorAll('#city-options .select-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                this.classList.add('selected');

                // Close dropdown
                closeCityDropdown();
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function (e) {
            if (!professionTrigger.contains(e.target) && !professionOptions.contains(e.target)) {
                closeProfessionDropdown();
            }
            if (!cityTrigger.contains(e.target) && !cityOptions.contains(e.target)) {
                closeCityDropdown();
            }
        });

        // Keyboard navigation for dropdowns
        professionTrigger.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (!isProfessionDropdownOpen) {
                    openProfessionDropdown();
                } else {
                    closeProfessionDropdown();
                }
            } else if (e.key === 'Escape') {
                closeProfessionDropdown();
            }
        });

        cityTrigger.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (!isCityDropdownOpen) {
                    openCityDropdown();
                } else {
                    closeCityDropdown();
                }
            } else if (e.key === 'Escape') {
                closeCityDropdown();
            }
        });

        // Password strength indicator
        registerPasswordInput.addEventListener('input', function () {
            const password = this.value;
            const strength = calculatePasswordStrength(password);

            // Update meter
            passwordStrengthMeter.style.width = `${strength.score * 25}%`;

            // Update color based on strength
            if (strength.score <= 1) {
                passwordStrengthMeter.style.backgroundColor = '#ef4444'; // Red
            } else if (strength.score === 2) {
                passwordStrengthMeter.style.backgroundColor = '#f59e0b'; // Amber
            } else if (strength.score === 3) {
                passwordStrengthMeter.style.backgroundColor = '#3b82f6'; // Blue
            } else {
                passwordStrengthMeter.style.backgroundColor = '#10b981'; // Green
            }

            // Update text
            const strengthText = passwordStrengthText.querySelector('span:first-child');
            strengthText.textContent = strength.text;
        });

        function calculatePasswordStrength(password) {
            let score = 0;
            let feedback = [];

            // Length check
            if (password.length >= 8) {
                score += 1;
            } else {
                feedback.push('Use at least 8 characters');
            }

            // Complexity checks
            if (/[A-Z]/.test(password)) {
                score += 1;
            } else {
                feedback.push('Include uppercase letter');
            }

            if (/[a-z]/.test(password)) {
                score += 1;
            } else {
                feedback.push('Include lowercase letter');
            }

            if (/[0-9]/.test(password)) {
                score += 1;
            } else {
                feedback.push('Include a number');
            }

            if (/[^A-Za-z0-9]/.test(password)) {
                score += 1;
            } else {
                feedback.push('Include a special character');
            }

            // Determine text based on score
            let text = 'Weak';
            if (score >= 4) {
                text = 'Strong';
            } else if (score >= 3) {
                text = 'Good';
            } else if (score >= 2) {
                text = 'Fair';
            }

            return {
                score: Math.min(score, 4),
                text: text,
                feedback: feedback
            };
        }

        // Enhanced Canadian mobile number validation and formatting
        mobileInput.addEventListener('input', function (e) {
            formatPhoneInput(e.target, 'mobile');
        });

        // Add WhatsApp number formatting
        if (whatsappInput) {
            whatsappInput.addEventListener('input', function (e) {
                formatPhoneInput(e.target, 'whatsapp');
            });
        }

        function formatPhoneInput(input, type) {
            // Remove any non-digit characters
            let value = input.value.replace(/\D/g, '');

            // Limit to 10 digits
            value = value.substring(0, 10);

            // Format the number as XXX-XXX-XXXX
            if (value.length > 3 && value.length <= 6) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            } else if (value.length > 6) {
                value = value.substring(0, 3) + '-' + value.substring(3, 6) + '-' + value.substring(6, 10);
            }

            // Update the input value
            input.value = value;

            // Validate the number
            if (type === 'mobile') {
                validateCanadianMobile(value);
            } else if (type === 'whatsapp') {
                validateWhatsAppNumber(value);
            }
        }

        function validateCanadianMobile(phoneNumber) {
            // Remove all non-digit characters for validation
            const digitsOnly = phoneNumber.replace(/\D/g, '');

            // Empty is ok if optional
            if (digitsOnly.length === 0) {
                clearMobileError();
                return true;
            }

            // Check if it's exactly 10 digits
            if (digitsOnly.length !== 10) {
                showMobileError('Please enter 10 digits');
                return false;
            }

            // Check if the first digit is between 2-9
            const firstDigit = digitsOnly.charAt(0);
            if (firstDigit === '0' || firstDigit === '1') {
                showMobileError('First digit must be 2-9');
                return false;
            }

            // Check if the fourth digit is between 2-9
            const fourthDigit = digitsOnly.charAt(3);
            if (fourthDigit === '0' || fourthDigit === '1') {
                showMobileError('Fourth digit must be 2-9');
                return false;
            }

            // If all checks pass
            clearMobileError();
            return true;
        }

        function validateWhatsAppNumber(phoneNumber) {
            const whatsappError = document.getElementById('whatsapp-error');
            const whatsappInput = document.getElementById('whatsapp_number');

            // Remove all non-digit characters
            const digitsOnly = phoneNumber.replace(/\D/g, '');

            // Empty is ok (optional field)
            if (digitsOnly.length === 0) {
                whatsappInput.classList.remove('error');
                whatsappError.innerHTML = '';
                return true;
            }

            // Check if it's exactly 10 digits
            if (digitsOnly.length !== 10) {
                whatsappInput.classList.add('error');
                whatsappError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please enter 10 digits';
                return false;
            }

            // Check if the first digit is between 2-9
            const firstDigit = digitsOnly.charAt(0);
            if (firstDigit === '0' || firstDigit === '1') {
                whatsappInput.classList.add('error');
                whatsappError.innerHTML = '<i class="fas fa-exclamation-circle"></i> First digit must be 2-9';
                return false;
            }

            // If all checks pass
            whatsappInput.classList.remove('error');
            whatsappError.innerHTML = '';
            return true;
        }

        function showMobileError(message) {
            mobileInput.classList.add('error');
            mobileError.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            phoneFormat.style.display = 'none';
            // Don't disable button - let server-side validation handle it
        }

        function clearMobileError() {
            mobileInput.classList.remove('error');
            mobileError.textContent = '';
            phoneFormat.style.display = 'flex';
            // Don't enable button automatically - it should stay enabled by default
        }

        // Form submission validation - Simplified to avoid blocking submission
        registerForm.addEventListener('submit', function (e) {
            // Get selected role
            const selectedRole = document.querySelector('input[name="role"]:checked')?.value;

            // Only do basic validation - let server handle detailed validation
            if (!selectedRole) {
                e.preventDefault();
                showToast("{{ __('validation.role_required') }}", 'error', "{{ __('validation.error_title') }}");
                return false;
            }

            // Show loading state but don't disable for too long
            registerButton.classList.add('loading');
            registerButton.disabled = true;

            // Re-enable button after 3 seconds in case of network issues
            setTimeout(() => {
                if (registerButton.classList.contains('loading')) {
                    registerButton.classList.remove('loading');
                    registerButton.disabled = false;
                }
            }, 3000);

            // Allow the form to submit - Laravel will handle all validation
            return true;
        });

        loginForm.addEventListener('submit', function (e) {
            // Get selected role for login
            const selectedLoginRole = document.querySelector('input[name="role"]:checked').value;
            const loginField = document.getElementById('login-field').value.trim();
            const loginPassword = document.getElementById('login-password').value;

            // Clear previous errors
            document.querySelectorAll('.input-error').forEach(el => el.textContent = '');

            // Basic validation
            if (!loginField) {
                e.preventDefault();
                showToast('{{ __('auth.login_required') }}', 'error', '{{ __('validation.error_title') }}');
                document.getElementById('login-field-error').innerHTML = '<i class="fas fa-exclamation-circle"></i> {{ __('auth.login_required') }}';
                return false;
            }

            if (!loginPassword) {
                e.preventDefault();
                showToast('{{ __('auth.password_required') }}', 'error', '{{ __('validation.error_title') }}');
                document.getElementById('login-password-error').innerHTML = '<i class="fas fa-exclamation-circle"></i> {{ __('auth.password_required') }}';
                return false;
            }

            if (loginPassword.length < 8) {
                e.preventDefault();
                showToast('{{ __('auth.password_min') }}', 'error', '{{ __('validation.error_title') }}');
                document.getElementById('login-password-error').innerHTML = '<i class="fas fa-exclamation-circle"></i> {{ __('auth.password_min') }}';
                return false;
            }

            // Show loading state and allow form submission
            loginButton.classList.add('loading');
            loginButton.disabled = true;

            // Allow the form to submit normally - Laravel will handle validation
            return true;
        });

        // Re-enable form buttons on page load (especially important after validation errors)
        window.addEventListener('load', function () {
            // Always ensure buttons are enabled when page loads
            if (loginButton) {
                loginButton.classList.remove('loading');
                loginButton.disabled = false;
            }
            if (registerButton) {
                registerButton.classList.remove('loading');
                registerButton.disabled = false;
            }
        });

        // Initialize forms on page load
        document.addEventListener('DOMContentLoaded', function () {
            selectLoginRole('client'); // Default login role

            // Social Login buttons - Coming Soon
            if (googleSignup) {
                googleSignup.addEventListener('click', function (e) {
                    e.preventDefault();
                    console.log('Google button clicked');
                    showToast('{{ __("auth.coming_soon_google") ?? "Google Sign-Up Coming Soon! 🚀" }}', 'info', '{{ __("general.coming_soon") ?? "Coming Soon" }}');
                });
            } else {
                console.error('Google signup button not found');
            }

            if (facebookSignup) {
                facebookSignup.addEventListener('click', function (e) {
                    e.preventDefault();
                    console.log('Facebook button clicked');
                    showToast('{{ __("auth.coming_soon_facebook") ?? "Facebook Sign-Up Coming Soon! 🚀" }}', 'info', '{{ __("general.coming_soon") ?? "Coming Soon" }}');
                });
            } else {
                console.error('Facebook signup button not found');
            }

            // For the register form, avoid forcing a 'client' selection on load.
            // Respect old input (server-side validation) or URL params if provided
            // so clicking the provider option earlier will not be overwritten.
            const urlParams = new URLSearchParams(window.location.search);
            const oldRole = "{{ old('role') }}";
            const roleParam = urlParams.get('role');
            if (oldRole) {
                // If the form was submitted and returned with errors, restore the user's choice
                selectRegisterRole(oldRole);
            } else if (roleParam) {
                // Support links like /register?role=service_provider
                selectRegisterRole(roleParam);
            } else {
                // Keep the client flow reduced by default on first load.
                selectRegisterRole('client');
            }

            // Check URL parameters to determine which form to show
            if (urlParams.get('form') === 'register' || "{{ $errors->any() && (old('email') || old('role')) }}") {
                switchToRegister();
            }

            // Validate mobile if there's already a value
            if (mobileInput.value) {
                validateCanadianMobile(mobileInput.value);
            }

            // Set initial profession value if exists
            const initialProfession = professionInput.value;
            if (initialProfession) {
                const option = document.querySelector(`#profession-options .select-option[data-value="${initialProfession}"]`);
                if (option) {
                    professionValue.textContent = option.textContent;
                    professionValue.classList.remove('placeholder');
                    option.classList.add('selected');
                }
            }

            // Set initial city value if exists
            const initialCity = cityInput.value;
            if (initialCity) {
                const option = document.querySelector(`#city-options .select-option[data-value="${initialCity}"]`);
                if (option) {
                    cityValue.textContent = option.textContent;
                    cityValue.classList.remove('placeholder');
                    option.classList.add('selected');
                }
            }

            // Check for session status
            const statusMessage = "{{ session('status') }}";
            if (statusMessage) {
                sessionStatus.textContent = statusMessage;
                sessionStatus.style.display = 'flex';
                sessionStatus.innerHTML = `<i class="fas fa-info-circle"></i> ${statusMessage}`;

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    sessionStatus.style.display = 'none';
                }, 5000);
            }

            // Check for error messages
            const errorMessage = "{{ session('error') }}";
            if (errorMessage) {
                sessionStatus.textContent = errorMessage;
                sessionStatus.style.display = 'flex';
                sessionStatus.classList.add('error-session');
                sessionStatus.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${errorMessage}`;

                // Auto-hide after 5 seconds
                setTimeout(() => {
                    sessionStatus.style.display = 'none';
                    sessionStatus.classList.remove('error-session');
                }, 5000);
            }
        });

        // Input field icon updates based on focus/blur
        document.querySelectorAll('.form-input').forEach(input => {
            const icon = input.parentElement.querySelector('.input-icon');

            input.addEventListener('focus', function () {
                if (icon) {
                    icon.style.color = '#4f46e5';
                }
            });

            input.addEventListener('blur', function () {
                if (icon) {
                    icon.style.color = '#6b7280';
                }
            });
        });

        // CSRF Token refresh and error handling
        function refreshCsrfToken() {
            fetch('{{ route("csrf-token") }}')
                .then(response => response.json())
                .then(data => {
                    document.querySelectorAll('input[name="_token"]').forEach(token => {
                        token.value = data.token;
                    });
                })
                .catch(error => {
                    console.error('Failed to refresh CSRF token:', error);
                });
        }

        // Handle CSRF token expiration
        document.addEventListener('DOMContentLoaded', function () {
            // Refresh CSRF token every 45 minutes to prevent expiration
            setInterval(refreshCsrfToken, 45 * 60 * 1000);

            // Add CSRF token to all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        // Global error handler for AJAX requests
        $(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
            if (jqXHR.status === 419) {
                showToast('Your session has expired. Please refresh the page and try again.', 'error', 'Session Expired');
                refreshCsrfToken();
            } else if (jqXHR.status === 401) {
                showToast('You are not authorized to perform this action.', 'error', 'Unauthorized');
            } else if (jqXHR.status >= 500) {
                showToast('A server error occurred. Please try again later.', 'error', 'Server Error');
            }
        });

        // Standalone Language Switcher Functions
        function toggleLanguageMenuStandalone() {
            const dropdown = document.getElementById('languageDropdownStandalone');
            const button = document.querySelector('#languageSwitcherStandalone .language-btn-standalone');
            dropdown.classList.toggle('show');
            if (button) {
                button.setAttribute('aria-expanded', dropdown.classList.contains('show') ? 'true' : 'false');
            }
        }

        // Function to switch language on auth pages
        function switchLanguageAuth(locale) {
            const currentUrl = window.location.href;
            const btn = document.querySelector('.language-btn-standalone .current-language-standalone');

            // Show loading state
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + btn.textContent;
            }

            // Create a form to POST the language change
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("locale.update") }}';
            form.style.display = 'none';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Add locale
            const localeInput = document.createElement('input');
            localeInput.type = 'hidden';
            localeInput.name = 'locale';
            localeInput.value = locale;
            form.appendChild(localeInput);

            // Add redirect URL
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect_to';
            redirectInput.value = currentUrl;
            form.appendChild(redirectInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Close standalone language dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const switcher = document.getElementById('languageSwitcherStandalone');
            const dropdown = document.getElementById('languageDropdownStandalone');

            if (switcher && !switcher.contains(event.target)) {
                dropdown.classList.remove('show');
                document.querySelector('#languageSwitcherStandalone .language-btn-standalone')?.setAttribute('aria-expanded', 'false');
            }
        });

        // Close standalone language dropdown on ESC key
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                const dropdown = document.getElementById('languageDropdownStandalone');
                dropdown.classList.remove('show');
                document.querySelector('#languageSwitcherStandalone .language-btn-standalone')?.setAttribute('aria-expanded', 'false');
            }
        });
    </script>
</body>

</html>
