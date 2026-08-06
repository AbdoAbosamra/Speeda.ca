<?php

namespace App\Console\Commands;

use App\Models\ServiceProvider;
use App\Services\ProviderEmailJourneyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * SendProviderJourneyEmails
 *
 * Scheduled Artisan command that processes every active service provider
 * and sends the next appropriate onboarding email based on their profile state.
 *
 * Schedule: Daily at 10:00 AM (configured in routes/console.php)
 *
 * Usage:
 *   php artisan providers:send-journey-emails            # Normal run
 *   php artisan providers:send-journey-emails --dry-run  # Preview only, no emails sent
 *   php artisan providers:send-journey-emails --provider=42  # Single provider test
 */
class SendProviderJourneyEmails extends Command
{
    protected $signature = 'providers:send-journey-emails
                            {--dry-run : Log what would be sent without actually sending}
                            {--provider= : Process only a specific provider ID}';

    protected $description = 'Send automated onboarding journey emails to service providers based on their profile completion state';

    public function __construct(
        private readonly ProviderEmailJourneyService $journeyService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun     = (bool) $this->option('dry-run');
        $providerId   = $this->option('provider');

        if ($isDryRun) {
            $this->warn('🔍 DRY-RUN MODE – No emails will actually be sent.');
        }

        $this->info('📧 Speeda Provider Email Journey – Starting...');
        $this->newLine();

        $query = ServiceProvider::query()
            ->with(['user', 'serviceAreas'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true)->whereNotNull('email'));

        // Limit to a single provider if requested
        if ($providerId) {
            $query->where('id', $providerId);
            $this->info("🎯 Processing single provider: #{$providerId}");
        }

        $totalProcessed = 0;
        $totalSent      = 0;
        $totalSkipped   = 0;

        $query->chunkById(100, function ($providers) use (
            $isDryRun,
            &$totalProcessed,
            &$totalSent,
            &$totalSkipped
        ) {
            foreach ($providers as $provider) {
                $totalProcessed++;

                $sentType = $this->journeyService->processProvider($provider, $isDryRun);

                if ($sentType) {
                    $totalSent++;
                    $verb = $isDryRun ? 'WOULD SEND' : 'SENT';
                    $this->line("  ... [{$verb}] #{$provider->id} {$provider->company_name} -> {$sentType}");
                } else {
                    $totalSkipped++;
                }
            }
        });

        $this->newLine();
        $this->info("📊 Journey Run Complete:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Providers Processed', $totalProcessed],
                ['Emails ' . ($isDryRun ? 'Would Send' : 'Sent'), $totalSent],
                ['Providers Skipped (nothing due)', $totalSkipped],
            ]
        );

        Log::info('[EmailJourney] Command completed', [
            'dry_run'   => $isDryRun,
            'processed' => $totalProcessed,
            'sent'      => $totalSent,
            'skipped'   => $totalSkipped,
        ]);

        return self::SUCCESS;
    }
}
