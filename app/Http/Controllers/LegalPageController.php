<?php

namespace App\Http\Controllers;

use App\Domain\SEO\Services\SeoMetaService;
use App\Models\LegalPage;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function privacyPolicy(): View
    {
        return $this->showDefaultPage('privacy-policy');
    }

    public function termsOfService(): View
    {
        return $this->showDefaultPage('terms-of-service');
    }

    public function show(string $slug): View
    {
        abort_unless(LegalPage::supportsDatabasePages(), 404);

        $page = LegalPage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->render($page);
    }

    private function showDefaultPage(string $slug): View
    {
        $default = LegalPage::defaultForSlug($slug);
        abort_unless($default, 404);

        if (LegalPage::supportsDatabasePages()) {
            $page = LegalPage::query()
                ->published()
                ->where('slug', $slug)
                ->first();

            if ($page) {
                return $this->render($page);
            }
        }

        return view($default['fallback_view']);
    }

    /**
     * The admin writes an SEO title and description for each page; use them.
     */
    private function render(LegalPage $page): View
    {
        app(SeoMetaService::class)->applyPage($page->localized_seo_title, $page->localized_seo_description, url()->current());

        return view('legal-pages.show', compact('page'));
    }
}
