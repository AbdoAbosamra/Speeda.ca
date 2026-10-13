{{-- JSON-LD is hardened here: the package prints titles (search terms included) unescaped inside <script>. --}}
{!! \App\Support\SafeJsonLd::harden(SEO::generate()) !!}
@stack('json-ld')
