{{--
    Head meta for the pages that are full HTML documents of their own (about,
    help, legal, the fallback privacy/terms pages) and so never pass through
    layouts.app: title, description, canonical, languages and share preview.
    Expects $title (without the site name) and $description.
--}}
@php
    app(\App\Domain\SEO\Services\SeoMetaService::class)->applyPage($title, $description, url()->current());
@endphp
@include('seo.meta')
