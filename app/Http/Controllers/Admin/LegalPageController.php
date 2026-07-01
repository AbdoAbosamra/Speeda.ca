<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $pages = LegalPage::query()
            ->with(['creator', 'updater'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('slug', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%")
                        ->orWhere('title_ar', 'like', "%{$search}%")
                        ->orWhere('title_fr', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, [LegalPage::STATUS_DRAFT, LegalPage::STATUS_PUBLISHED], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'total' => LegalPage::count(),
            'published' => LegalPage::where('status', LegalPage::STATUS_PUBLISHED)->count(),
            'draft' => LegalPage::where('status', LegalPage::STATUS_DRAFT)->count(),
        ];

        $defaultPages = collect(LegalPage::defaultPages())
            ->map(function (array $default, string $slug) {
                $override = LegalPage::where('slug', $slug)->first();

                return [
                    ...$default,
                    'override' => $override,
                    'public_url' => route($default['route']),
                ];
            });

        return view('admin.legal-pages.index', compact('pages', 'counts', 'defaultPages', 'search', 'status'));
    }

    public function create(Request $request): View
    {
        $slug = trim((string) $request->query('slug'));
        $default = $slug !== '' ? LegalPage::defaultForSlug($slug) : null;

        $page = new LegalPage([
            'slug' => $default['slug'] ?? '',
            'page_type' => $default['page_type'] ?? LegalPage::TYPE_CUSTOM,
            'status' => LegalPage::STATUS_DRAFT,
            'allow_indexing' => true,
            'title_en' => $default['title_en'] ?? '',
            'title_ar' => $default['title_ar'] ?? '',
            'title_fr' => $default['title_fr'] ?? '',
        ]);

        return view('admin.legal-pages.create', [
            'page' => $page,
            'pageTypes' => $this->pageTypes(),
            'default' => $default,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePage($request);
        $page = new LegalPage();

        $this->fillPage($page, $validated, $request);

        return redirect()
            ->route('admin.legal-pages.edit', $page)
            ->with('success', 'Legal page created successfully.');
    }

    public function edit(LegalPage $legalPage): View
    {
        return view('admin.legal-pages.edit', [
            'page' => $legalPage,
            'pageTypes' => $this->pageTypes(),
            'default' => LegalPage::defaultForSlug($legalPage->slug),
        ]);
    }

    public function update(Request $request, LegalPage $legalPage): RedirectResponse
    {
        $validated = $this->validatePage($request, $legalPage);
        $this->fillPage($legalPage, $validated, $request);

        return redirect()
            ->route('admin.legal-pages.edit', $legalPage)
            ->with('success', 'Legal page updated successfully.');
    }

    public function destroy(LegalPage $legalPage): RedirectResponse
    {
        $slug = $legalPage->slug;

        $legalPage->forceFill([
            'slug' => $slug . '-deleted-' . $legalPage->id . '-' . now()->format('YmdHis'),
            'status' => LegalPage::STATUS_DRAFT,
            'updated_by' => Auth::id(),
        ])->save();

        $legalPage->delete();

        return redirect()
            ->route('admin.legal-pages.index')
            ->with('success', 'Legal page deleted safely. Core legal page links fall back to the static page if available.');
    }

    private function validatePage(Request $request, ?LegalPage $page = null): array
    {
        return $request->validate([
            'slug' => [
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('legal_pages', 'slug')->ignore($page?->id),
            ],
            'page_type' => ['required', Rule::in(array_keys($this->pageTypes()))],
            'status' => ['required', Rule::in([LegalPage::STATUS_DRAFT, LegalPage::STATUS_PUBLISHED])],
            'allow_indexing' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'last_reviewed_at' => ['nullable', 'date'],
            'title_en' => ['required', 'string', 'max:255'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_fr' => ['required', 'string', 'max:255'],
            'content_en' => ['required', 'string'],
            'content_ar' => ['required', 'string'],
            'content_fr' => ['required', 'string'],
            'summary_en' => ['nullable', 'string', 'max:500'],
            'summary_ar' => ['nullable', 'string', 'max:500'],
            'summary_fr' => ['nullable', 'string', 'max:500'],
            'seo_title_en' => ['nullable', 'string', 'max:255'],
            'seo_title_ar' => ['nullable', 'string', 'max:255'],
            'seo_title_fr' => ['nullable', 'string', 'max:255'],
            'seo_description_en' => ['nullable', 'string', 'max:500'],
            'seo_description_ar' => ['nullable', 'string', 'max:500'],
            'seo_description_fr' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function fillPage(LegalPage $page, array $validated, Request $request): void
    {
        $status = $validated['status'];

        $page->fill([
            'slug' => $validated['slug'],
            'page_type' => $validated['page_type'],
            'status' => $status,
            'allow_indexing' => $request->boolean('allow_indexing', true),
            'published_at' => $status === LegalPage::STATUS_PUBLISHED
                ? ($validated['published_at'] ?? now())
                : ($validated['published_at'] ?? null),
            'last_reviewed_at' => $validated['last_reviewed_at'] ?? null,
            'title_en' => trim($validated['title_en']),
            'title_ar' => trim($validated['title_ar']),
            'title_fr' => trim($validated['title_fr']),
            'content_en' => $this->cleanHtml($validated['content_en']),
            'content_ar' => $this->cleanHtml($validated['content_ar']),
            'content_fr' => $this->cleanHtml($validated['content_fr']),
            'summary_en' => $validated['summary_en'] ?? Str::limit(strip_tags($validated['content_en']), 180),
            'summary_ar' => $validated['summary_ar'] ?? Str::limit(strip_tags($validated['content_ar']), 180),
            'summary_fr' => $validated['summary_fr'] ?? Str::limit(strip_tags($validated['content_fr']), 180),
            'seo_title_en' => $validated['seo_title_en'] ?? null,
            'seo_title_ar' => $validated['seo_title_ar'] ?? null,
            'seo_title_fr' => $validated['seo_title_fr'] ?? null,
            'seo_description_en' => $validated['seo_description_en'] ?? null,
            'seo_description_ar' => $validated['seo_description_ar'] ?? null,
            'seo_description_fr' => $validated['seo_description_fr'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        if (!$page->exists) {
            $page->created_by = Auth::id();
        }

        $page->save();
    }

    private function cleanHtml(string $html): string
    {
        $html = trim($html);
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|textarea|select|option|link|meta)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/(href|src)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '$1="#"', $html) ?? '';

        return strip_tags(
            $html,
            '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><blockquote><a><table><thead><tbody><tr><th><td><hr>'
        );
    }

    private function pageTypes(): array
    {
        return [
            LegalPage::TYPE_PRIVACY_POLICY => 'Privacy Policy',
            LegalPage::TYPE_TERMS_OF_SERVICE => 'Terms of Service',
            LegalPage::TYPE_POLICY => 'Policy Page',
            LegalPage::TYPE_CUSTOM => 'Custom Legal Page',
        ];
    }
}
