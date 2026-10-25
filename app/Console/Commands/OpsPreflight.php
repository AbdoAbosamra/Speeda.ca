<?php

namespace App\Console\Commands;

use App\Models\ProviderBroadcastRecipient;
use App\Models\Visitor;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Read-only snapshot of everything that can send email or look wrong right
 * after a deploy. Tinker is a dev dependency and is not installed on the
 * server, so this is how the release checklist reads production state.
 *
 * It never writes, deletes or sends anything.
 */
class OpsPreflight extends Command
{
    protected $signature = 'ops:preflight';

    protected $description = 'Show queued and failed emails, broadcast state and visitor IP spread (read-only)';

    public function handle(): int
    {
        $this->line('Mailer: '.config('mail.default').' | Queue: '.config('queue.default'));
        $this->newLine();

        $this->queuedJobs();
        $this->failedJobs();
        $this->broadcasts();
        $this->visitorIps();

        return self::SUCCESS;
    }

    /** Jobs waiting in the database queue: sent by the worker, deploy or not. */
    private function queuedJobs(): void
    {
        $table = config('queue.connections.database.table', 'jobs');

        if (! Schema::hasTable($table)) {
            $this->warn("Queue table '{$table}' not found.");

            return;
        }

        $rows = [];
        foreach (DB::table($table)->select(['payload', 'created_at'])->orderBy('id')->cursor() as $job) {
            $name = json_decode($job->payload)->displayName ?? 'unknown';
            $rows[$name]['count'] = ($rows[$name]['count'] ?? 0) + 1;
            $rows[$name]['oldest'] ??= (int) $job->created_at;
        }

        $this->info('Queued jobs: '.array_sum(array_column($rows, 'count')));
        if ($rows) {
            $this->table(['Job', 'Count', 'Oldest queued'], collect($rows)->map(fn ($row, $name) => [
                $name,
                $row['count'],
                Carbon::createFromTimestamp($row['oldest'])->diffForHumans(),
            ])->values()->all());
        }
    }

    /** Failed jobs are never retried automatically. */
    private function failedJobs(): void
    {
        $table = config('queue.failed.table', 'failed_jobs');

        if (! Schema::hasTable($table)) {
            return;
        }

        $count = DB::table($table)->count();
        $this->info("Failed jobs: {$count}".($count ? ' (inspect with queue:failed before any queue:retry)' : ''));
    }

    private function broadcasts(): void
    {
        if (! Schema::hasTable('provider_broadcast_recipients')) {
            return;
        }

        $counts = ProviderBroadcastRecipient::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->whereIn('status', [ProviderBroadcastRecipient::STATUS_PENDING, ProviderBroadcastRecipient::STATUS_SENDING])
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $this->info(sprintf(
            'Broadcast recipients still to send: %d pending, %d sending',
            $counts[ProviderBroadcastRecipient::STATUS_PENDING] ?? 0,
            $counts[ProviderBroadcastRecipient::STATUS_SENDING] ?? 0,
        ));
    }

    /**
     * One distinct IP hash for a whole day of traffic means every visitor
     * shares the proxy's address: see TRUSTED_PROXIES in config/trustedproxy.php.
     */
    private function visitorIps(): void
    {
        if (! Schema::hasTable('visitors')) {
            return;
        }

        $since = now()->subDay();
        $visits = Visitor::where('visited_at', '>=', $since)->count();
        $ips = Visitor::where('visited_at', '>=', $since)->distinct()->count('ip_hash');

        $this->info("Visitors in the last 24h: {$visits} page views from {$ips} distinct IPs");

        if ($visits >= 20 && $ips <= 1) {
            $this->warn('All traffic shares one IP: the real visitor IP is not reaching the app (TRUSTED_PROXIES).');
        }
    }
}
