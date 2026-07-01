@extends('layouts.app')

@php
    $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
    $reviewDate = $page->last_reviewed_at ?: $page->published_at ?: $page->updated_at;
@endphp

@section('content')
    <div class="legal-page-shell" dir="{{ $dir }}">
        <section class="legal-page-hero">
            <div class="legal-page-hero-inner">
                <p class="legal-page-eyebrow">Speeda.ca</p>
                <h1>{{ $page->localized_title }}</h1>
                @if($page->localized_summary)
                    <p>{{ $page->localized_summary }}</p>
                @endif
                @if($reviewDate)
                    <span class="legal-page-date">
                        {{ app()->getLocale() === 'ar' ? 'آخر تحديث' : (app()->getLocale() === 'fr' ? 'Dernière mise à jour' : 'Last updated') }}:
                        {{ $reviewDate->format('M d, Y') }}
                    </span>
                @endif
            </div>
        </section>

        <section class="legal-page-body-wrap">
            <article class="legal-page-body">
                {!! $page->localized_content !!}
            </article>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .legal-page-shell {
            background: #f6f8fb;
            color: #172033;
            margin-top: -1.5rem;
        }

        .legal-page-hero {
            background: #12313f;
            color: #fff;
            padding: clamp(3rem, 8vw, 5.5rem) 1rem clamp(2.5rem, 6vw, 4rem);
        }

        .legal-page-hero-inner {
            width: min(940px, 100%);
            margin: 0 auto;
        }

        .legal-page-eyebrow {
            margin: 0 0 0.75rem;
            color: #9fe3c2;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .legal-page-hero h1 {
            max-width: 760px;
            margin: 0;
            font-size: clamp(2rem, 5vw, 3.5rem);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: 0;
        }

        .legal-page-hero p:not(.legal-page-eyebrow) {
            max-width: 740px;
            margin: 1rem 0 0;
            color: #dce8ec;
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .legal-page-date {
            display: inline-flex;
            margin-top: 1.25rem;
            padding: 0.4rem 0.7rem;
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 999px;
            color: #ecfdf5;
            font-size: 0.875rem;
            font-weight: 800;
        }

        .legal-page-body-wrap {
            width: min(940px, calc(100% - 2rem));
            margin: 0 auto;
            padding: clamp(2rem, 5vw, 4rem) 0;
        }

        .legal-page-body {
            background: #fff;
            border: 1px solid #dbe3ef;
            border-radius: 1rem;
            padding: clamp(1.25rem, 4vw, 2.5rem);
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
            font-size: 1.02rem;
            line-height: 1.82;
        }

        .legal-page-body > *:first-child {
            margin-top: 0;
        }

        .legal-page-body h2,
        .legal-page-body h3,
        .legal-page-body h4 {
            color: #12313f;
            line-height: 1.3;
            font-weight: 900;
            margin: 2rem 0 0.75rem;
            letter-spacing: 0;
        }

        .legal-page-body h2 {
            font-size: 1.55rem;
        }

        .legal-page-body h3 {
            font-size: 1.25rem;
        }

        .legal-page-body p,
        .legal-page-body ul,
        .legal-page-body ol,
        .legal-page-body blockquote,
        .legal-page-body table {
            margin-bottom: 1.1rem;
        }

        .legal-page-body ul,
        .legal-page-body ol {
            padding-inline-start: 1.5rem;
        }

        .legal-page-body li {
            margin-bottom: 0.45rem;
        }

        .legal-page-body a {
            color: #0f766e;
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: 0.2em;
        }

        .legal-page-body blockquote {
            border-inline-start: 4px solid #0f766e;
            padding: 0.85rem 1rem;
            background: #eefdf8;
            border-radius: 0.5rem;
            color: #24434a;
        }

        .legal-page-body table {
            width: 100%;
            border-collapse: collapse;
            display: block;
            overflow-x: auto;
        }

        .legal-page-body th,
        .legal-page-body td {
            border: 1px solid #dbe3ef;
            padding: 0.65rem 0.75rem;
            vertical-align: top;
        }

        .legal-page-body th {
            background: #f8fafc;
        }

        @media (max-width: 640px) {
            .legal-page-body {
                border-radius: 0.75rem;
            }
        }
    </style>
@endpush
