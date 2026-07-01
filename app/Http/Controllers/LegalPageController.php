<?php

namespace App\Http\Controllers;

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

        return view('legal-pages.show', compact('page'));
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
                return view('legal-pages.show', compact('page'));
            }
        }

        return view($default['fallback_view']);
    }
}
