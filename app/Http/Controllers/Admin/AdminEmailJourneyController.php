<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Services\ProviderEmailJourneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * AdminEmailJourneyController
 *
 * Provides admin visibility and control over the automated provider email journey.
 * Routes mounted under /admin/email-journey
 */
class AdminEmailJourneyController extends Controller
{
    public function __construct(
        private readonly ProviderEmailJourneyService $journeyService
    ) {}

    /**
     * GET /admin/email-journey
     * Main stats dashboard for the email journey system.
     */
    public function index()
    {
        $stats = $this->journeyService->getAdminStats();

        // Per-provider summary with latest log
        $providers = ServiceProvider::query()
            ->with(['user', 'providerEmailLogs' => fn ($q) => $q->orderByDesc('sent_at')])
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->withCount('providerEmailLogs')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        // Email type labels
        $emailTypeLabels = [
            'welcome'       => '🎉 Welcome',
            'photo'         => '📸 Profile Photo',
            'services'      => '🛠️ Services',
            'bio'           => '📝 Description',
            'experience'    => '📅 Experience',
            'gallery'       => '🖼️ Gallery',
            'service_areas' => '🌍 Service Areas',
            'complete'      => '🏆 Complete',
            'reviews'       => '⭐ Reviews',
        ];

        return view('admin.email_journey.index', compact('stats', 'providers', 'emailTypeLabels'));
    }

    /**
     * GET /admin/email-journey/{serviceProvider}
     * Full email timeline for a specific provider.
     */
    public function show(ServiceProvider $serviceProvider)
    {
        $serviceProvider->load(['user', 'serviceAreas']);

        $timeline = $this->journeyService->getProviderEmailTimeline($serviceProvider);

        $emailTypeLabels = [
            'welcome'       => '🎉 Welcome',
            'photo'         => '📸 Profile Photo',
            'services'      => '🛠️ Services',
            'bio'           => '📝 Description',
            'experience'    => '📅 Experience',
            'gallery'       => '🖼️ Gallery',
            'service_areas' => '🌍 Service Areas',
            'complete'      => '🏆 Complete',
            'reviews'       => '⭐ Reviews',
        ];

        return view('admin.email_journey.show', compact('serviceProvider', 'timeline', 'emailTypeLabels'));
    }

    /**
     * GET /admin/email-journey/preview/{type}
     * Preview an email template in the browser (no real sending).
     */
    public function preview(string $type)
    {
        // Use the first active provider as a dummy for preview
        $provider = ServiceProvider::whereHas('user', fn ($q) => $q->where('is_active', true))->first();

        if (!$provider) {
            abort(404, 'No active provider found for preview.');
        }

        $dashboardUrl = route('service-providers.show', $provider->id);

        $view = match ($type) {
            'welcome'       => 'emails.provider.welcome',
            'photo'         => 'emails.provider.add-photo',
            'services'      => 'emails.provider.add-services',
            'bio'           => 'emails.provider.write-description',
            'experience'    => 'emails.provider.add-experience',
            'gallery'       => 'emails.provider.showcase-work',
            'service_areas' => 'emails.provider.add-service-areas',
            'complete'      => 'emails.provider.profile-complete',
            'reviews'       => 'emails.provider.build-reputation',
            default         => abort(404, "Unknown email type: {$type}"),
        };

        return view($view, [
            'providerName' => $provider->company_name ?? $provider->user?->name ?? 'Demo Provider',
            'dashboardUrl' => $dashboardUrl,
            'subject'      => "Preview: {$type}",
        ]);
    }

    /**
     * POST /admin/email-journey/{serviceProvider}/send-test
     *
     * Sends the provider's next onboarding email immediately instead of waiting
     * for the scheduler. This is a REAL email to the REAL provider — not a test
     * send to the admin — so the UI confirms before posting here.
     *
     * `expected_type` is what the admin saw on screen; if the journey service
     * has since decided a different email is due (e.g. a resend became
     * eligible), we abort rather than send something the admin did not intend.
     */
    public function sendTest(Request $request, ServiceProvider $serviceProvider)
    {
        $validated = $request->validate([
            'expected_type' => 'required|in:welcome,photo,services,bio,experience,gallery,service_areas,complete,reviews',
        ]);

        $serviceProvider->load('user');

        $timeline = $this->journeyService->getProviderEmailTimeline($serviceProvider);
        $nextType = $timeline['next_email'] ?? null;

        if ($nextType !== $validated['expected_type']) {
            return redirect()
                ->route('admin.email_journey.show', $serviceProvider)
                ->with('warning', $nextType
                    ? "Nothing sent — the next due email changed to \"{$nextType}\". Review the timeline and try again."
                    : 'Nothing sent — this provider has no onboarding email due right now.');
        }

        $sent = $this->journeyService->processProvider($serviceProvider, dryRun: false);

        Log::info('[EmailJourney] Admin manually triggered the next onboarding email', [
            'admin_id'    => auth()->id(),
            'admin_email' => auth()->user()->email,
            'provider_id' => $serviceProvider->id,
            'email_type'  => $sent,
        ]);

        if (!$sent) {
            return redirect()
                ->route('admin.email_journey.show', $serviceProvider)
                ->with('warning', 'Nothing was sent — the provider is inactive, has no email address, or is already up to date.');
        }

        return redirect()
            ->route('admin.email_journey.show', $serviceProvider)
            ->with('success', "✅ \"{$sent}\" email sent to {$serviceProvider->user?->email}.");
    }
}
