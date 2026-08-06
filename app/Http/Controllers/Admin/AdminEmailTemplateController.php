<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\ServiceProvider;
use App\Traits\LogsAdminActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Lets an admin edit the wording of the automated emails.
 *
 * Editing is limited to a fixed set of named text fields — never raw HTML —
 * because the email layout is hand-inlined for mail clients and arbitrary
 * markup would both break rendering and be an injection vector. A template with
 * no saved row (or with is_active off) falls back to the shipped default, so
 * "Reset" is simply deleting the row.
 */
class AdminEmailTemplateController extends Controller
{
    use LogsAdminActions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(): View
    {
        $overrides = EmailTemplate::query()->get()->keyBy('key');

        $templates = collect(EmailTemplate::defaults())
            ->map(function (array $default, string $key) use ($overrides) {
                $row = $overrides->get($key);

                return [
                    'key' => $key,
                    'label' => $default['label'],
                    'group' => $default['group'],
                    'subject' => $row && $row->is_active ? $row->subject : $default['subject'],
                    'customised' => (bool) $row,
                    'is_active' => $row?->is_active ?? false,
                    'updated_at' => $row?->updated_at,
                    'updated_by' => $row?->updatedBy?->name,
                ];
            })
            ->values();

        return view('admin.email_templates.index', [
            'templates' => $templates,
            'groups' => ['provider' => 'Provider onboarding journey', 'client' => 'Client engagement'],
        ]);
    }

    public function edit(string $key): View|RedirectResponse
    {
        if (!EmailTemplate::isKnownKey($key)) {
            abort(404);
        }

        $default = EmailTemplate::defaults()[$key];
        $row = EmailTemplate::where('key', $key)->first();

        // Pre-fill with the saved copy when present, otherwise the default.
        $values = collect(EmailTemplate::CONTENT_FIELDS)
            ->mapWithKeys(fn ($f) => [$f => $row?->{$f} ?? ($default[$f] ?? '')])
            ->all();

        return view('admin.email_templates.edit', [
            'key' => $key,
            'label' => $default['label'],
            'default' => $default,
            'row' => $row,
            'values' => $values,
            'fields' => EmailTemplate::CONTENT_FIELDS,
            'longFields' => EmailTemplate::LONG_FIELDS,
            'placeholders' => EmailTemplate::PLACEHOLDERS,
        ]);
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        if (!EmailTemplate::isKnownKey($key)) {
            abort(404);
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:120'],
            'lead' => ['nullable', 'string', 'max:2000'],
            'next_step_label' => ['nullable', 'string', 'max:120'],
            'next_step_title' => ['nullable', 'string', 'max:255'],
            'next_step_desc' => ['nullable', 'string', 'max:2000'],
            'why_label' => ['nullable', 'string', 'max:120'],
            'why_text' => ['nullable', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:120'],
            'cta_subtext' => ['nullable', 'string', 'max:255'],
            'quote' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $unknown = $this->unknownPlaceholders($validated);
        if ($unknown) {
            return redirect()->back()->withInput()->withErrors([
                'subject' => __('admin.email_unknown_placeholder', ['list' => implode(', ', $unknown)]),
            ]);
        }

        $row = EmailTemplate::firstOrNew(['key' => $key]);
        $old = $row->exists ? $row->getOriginal() : [];

        $row->fill($validated);
        $row->is_active = $request->boolean('is_active', true);
        $row->updated_by = Auth::id();
        $row->save();

        $row->exists && $old
            ? $this->logUpdate($row, $old, $key)
            : $this->logCreate($row, $key);

        ErrorHelper::flashNotification(__('admin.email_template_saved'), 'success');

        return redirect()->route('admin.email_templates.edit', $key);
    }

    /**
     * Drop the override so the shipped default takes over again.
     */
    public function reset(string $key): RedirectResponse
    {
        if (!EmailTemplate::isKnownKey($key)) {
            abort(404);
        }

        $row = EmailTemplate::where('key', $key)->first();

        if (!$row) {
            ErrorHelper::flashNotification(__('admin.email_template_already_default'), 'warning');

            return redirect()->back();
        }

        $this->logAction('delete', $row, ['deleted' => $row->only(EmailTemplate::CONTENT_FIELDS)], $key);
        $row->delete();

        ErrorHelper::flashNotification(__('admin.email_template_reset'), 'success');

        return redirect()->route('admin.email_templates.edit', $key);
    }

    /**
     * Render the email exactly as a recipient would receive it, using the
     * currently SAVED copy (not unsaved form input).
     */
    public function preview(string $key)
    {
        if (!EmailTemplate::isKnownKey($key)) {
            abort(404);
        }

        $provider = ServiceProvider::with('user')->first();

        $content = EmailTemplate::resolve($key, [
            'provider_name' => $provider?->company_name ?: ($provider?->user?->name ?: 'Sample Provider'),
            'dashboard_url' => $provider ? route('service-providers.show', $provider->id) : url('/'),
        ]);

        return view('emails.provider.journey', [
            'c' => $content,
            'subject' => $content['subject'],
            'dashboardUrl' => $provider ? route('service-providers.show', $provider->id) : url('/'),
            'stats' => $this->previewStats($key),
            'progress' => $this->previewProgress($key),
        ]);
    }

    /**
     * Reject {{ tokens }} the renderer does not know, so a typo like
     * {{ providername }} is caught at save time instead of shipping literally.
     *
     * @return array<int,string>
     */
    private function unknownPlaceholders(array $values): array
    {
        $known = array_keys(EmailTemplate::PLACEHOLDERS);
        $found = [];

        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $value, $m);
            $found = array_merge($found, $m[1] ?? []);
        }

        return array_values(array_unique(array_diff($found, $known)));
    }

    private function previewStats(string $key): array
    {
        return $key === 'photo'
            ? [
                ['value' => '3x', 'label' => 'More Views'],
                ['value' => '2x', 'label' => 'More Leads'],
                ['value' => '30s', 'label' => 'To Complete'],
            ]
            : [];
    }

    private function previewProgress(string $key): array
    {
        $steps = [
            'welcome' => [0, 'Step 0 of 6 complete (0%)'],
            'photo' => [0, 'Step 0 of 6 complete (0%)'],
            'services' => [17, 'Step 1 of 6 complete (17%)'],
            'bio' => [33, 'Step 2 of 6 complete (33%)'],
            'experience' => [50, 'Step 3 of 6 complete (50%)'],
            'gallery' => [67, 'Step 4 of 6 complete (67%)'],
            'service_areas' => [83, 'Step 5 of 6 complete (83%)'],
        ];

        if (!isset($steps[$key])) {
            return [];
        }

        [$percent, $text] = $steps[$key];

        return ['label' => 'Onboarding Progress', 'text' => $text, 'percent' => $percent];
    }
}
