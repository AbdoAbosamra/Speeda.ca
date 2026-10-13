@php
    // Pages without controller SEO (errors, account pages, admin) name
    // themselves with @section('title'); without this they were all "Speeda".
    if (trim($__env->yieldContent('title')) !== ''
        && SEO::metatags()->getTitle() === config('seotools.meta.defaults.title')) {
        SEO::setTitle(trim($__env->yieldContent('title')) . ' | ' . config('app.name'), false);
    }
@endphp
{{-- JSON-LD is hardened here: the package prints titles (search terms included) unescaped inside <script>. --}}
{!! \App\Support\SafeJsonLd::harden(SEO::generate()) !!}
@stack('json-ld')
