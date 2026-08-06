<?php

namespace App\Support;

/**
 * Outcome of a bulk admin action.
 *
 * Bulk operations are partial by nature: a batch of 20 reviews may contain some
 * that cannot be actioned (already in that state, protected by a guard, or
 * removed by someone else mid-request). Reporting a flat "20 updated" would be
 * a lie, so every skip is recorded with a reason and surfaced to the admin.
 */
class BulkActionResult
{
    /** @var array<int, string> id => reason */
    private array $skipped = [];

    private int $succeeded = 0;

    public function succeeded(): void
    {
        $this->succeeded++;
    }

    public function skipped(int|string $id, string $reason): void
    {
        $this->skipped[(string) $id] = $reason;
    }

    public function successCount(): int
    {
        return $this->succeeded;
    }

    public function skippedCount(): int
    {
        return count($this->skipped);
    }

    /**
     * Distinct skip reasons, most common first, for a compact message.
     *
     * @return array<string, int>
     */
    public function skipReasons(): array
    {
        $counts = array_count_values($this->skipped);
        arsort($counts);

        return $counts;
    }

    public function nothingHappened(): bool
    {
        return $this->succeeded === 0;
    }

    /**
     * Human-readable summary, e.g.
     *   "12 items updated. 3 skipped: cannot delete an active category (2), not found (1)."
     */
    public function message(string $actionLabel): string
    {
        $parts = [];

        if ($this->succeeded > 0) {
            $parts[] = trans_choice('admin.bulk_succeeded', $this->succeeded, [
                'count' => $this->succeeded,
                'action' => $actionLabel,
            ]);
        }

        if ($this->skipped) {
            $reasons = [];
            foreach ($this->skipReasons() as $reason => $count) {
                $reasons[] = $count > 1 ? "{$reason} ({$count})" : $reason;
            }

            $parts[] = __('admin.bulk_skipped', [
                'count' => $this->skippedCount(),
                'reasons' => implode(', ', $reasons),
            ]);
        }

        return $parts ? implode(' ', $parts) : __('admin.bulk_nothing_selected');
    }

    /**
     * success when anything worked, warning when everything was skipped.
     */
    public function flashType(): string
    {
        if ($this->nothingHappened()) {
            return 'warning';
        }

        return $this->skipped ? 'warning' : 'success';
    }
}
