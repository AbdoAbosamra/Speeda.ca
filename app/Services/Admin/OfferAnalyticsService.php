<?php

namespace App\Services\Admin;

use App\Models\Offer;
use App\Models\OfferEvent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Everything the admin can learn about offers, read from the offer event log.
 *
 * The funnel is impressions (card seen) → views (offer page opened) →
 * WhatsApp clicks ("Get this offer on WhatsApp"). Every rate guards against
 * division by zero, and every query honours the same filter set so the
 * numbers on one screen always agree with each other.
 */
class OfferAnalyticsService
{
    public const RANGES = ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', '365' => 'Last 12 months', 'custom' => 'Custom'];

    /**
     * @return array{from: Carbon, to: Carbon, range: string, offer_id: ?int, provider_id: ?int, source: ?string, device: ?string}
     */
    public function filtersFromRequest(Request $request): array
    {
        $range = (string) $request->query('range', '30');
        $range = array_key_exists($range, self::RANGES) ? $range : '30';

        if ($range === 'custom') {
            $from = $this->parseDate($request->query('from')) ?? Carbon::today()->subDays(29);
            $to = $this->parseDate($request->query('to')) ?? Carbon::today();
        } else {
            $to = Carbon::today();
            $from = Carbon::today()->subDays((int) $range - 1);
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        // A runaway custom range would build a chart with thousands of bars.
        if ($from->diffInDays($to) > 730) {
            $from = $to->copy()->subDays(730);
        }

        $source = $request->query('source');
        $device = $request->query('device');

        return [
            'from' => $from->startOfDay(),
            'to' => $to->endOfDay(),
            'range' => $range,
            'offer_id' => $request->integer('offer_id') ?: null,
            'provider_id' => $request->integer('provider_id') ?: null,
            'source' => in_array($source, OfferEvent::SOURCES, true) ? $source : null,
            'device' => in_array($device, ['mobile', 'tablet', 'desktop', 'unknown'], true) ? $device : null,
        ];
    }

    /* =====================================================================
     |  SUMMARY
     * ===================================================================== */

    public function summary(array $f): array
    {
        $current = $this->periodTotals($f, $f['from'], $f['to']);

        $days = $f['from']->diffInDays($f['to']) + 1;
        $prevTo = $f['from']->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays((int) ceil($days) - 1)->startOfDay();
        $previous = $this->periodTotals($f, $prevFrom, $prevTo);

        $change = [];
        foreach ($current as $key => $value) {
            $change[$key] = $this->growth((float) $value, (float) ($previous[$key] ?? 0));
        }

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $change,
            'live_offers' => Offer::live()
                ->when($f['provider_id'], fn ($q, $id) => $q->where('service_provider_id', $id))
                ->count(),
            'all_time_clicks' => (int) Offer::withTrashed()
                ->when($f['offer_id'], fn ($q, $id) => $q->whereKey($id))
                ->when($f['provider_id'], fn ($q, $id) => $q->where('service_provider_id', $id))
                ->sum('claims_count'),
        ];
    }

    private function periodTotals(array $f, Carbon $from, Carbon $to): array
    {
        $counts = $this->events($f, $from, $to)
            ->selectRaw('event, COUNT(*) as c')
            ->groupBy('event')
            ->pluck('c', 'event');

        $uniqueViewers = $this->events($f, $from, $to)
            ->where('event', OfferEvent::VIEW)
            ->whereNotNull('session_hash')
            ->distinct()
            ->count('session_hash');

        $impressions = (int) ($counts[OfferEvent::IMPRESSION] ?? 0);
        $views = (int) ($counts[OfferEvent::VIEW] ?? 0);
        $clicks = (int) ($counts[OfferEvent::CLAIM] ?? 0);

        return [
            'impressions' => $impressions,
            'views' => $views,
            'unique_viewers' => $uniqueViewers,
            'clicks' => $clicks,
            'shares' => (int) ($counts[OfferEvent::SHARE] ?? 0),
            'likes' => (int) ($counts[OfferEvent::LIKE] ?? 0),
            'favorites' => (int) ($counts[OfferEvent::FAVORITE] ?? 0),
            'comments' => (int) ($counts[OfferEvent::COMMENT] ?? 0),
            'ctr' => $this->rate($views, $impressions),
            'click_rate' => $this->rate($clicks, $views),
        ];
    }

    /* =====================================================================
     |  CHARTS
     * ===================================================================== */

    /**
     * Zero-filled daily series for the trend chart.
     */
    public function trend(array $f): array
    {
        $byDay = $this->events($f, $f['from'], $f['to'])
            ->whereIn('event', [OfferEvent::IMPRESSION, OfferEvent::VIEW, OfferEvent::CLAIM])
            ->selectRaw('DATE(created_at) as d, event, COUNT(*) as c')
            ->groupByRaw('DATE(created_at), event')
            ->get()
            ->groupBy('d');

        $series = ['labels' => [], 'dates' => [], 'impressions' => [], 'views' => [], 'clicks' => []];

        foreach (CarbonPeriod::create($f['from']->copy()->startOfDay(), $f['to']->copy()->startOfDay()) as $day) {
            $key = $day->toDateString();
            $rows = $byDay->get($key, collect())->pluck('c', 'event');

            $series['labels'][] = $day->format('M j');
            $series['dates'][] = $key;
            $series['impressions'][] = (int) ($rows[OfferEvent::IMPRESSION] ?? 0);
            $series['views'][] = (int) ($rows[OfferEvent::VIEW] ?? 0);
            $series['clicks'][] = (int) ($rows[OfferEvent::CLAIM] ?? 0);
        }

        return $series;
    }

    /**
     * Views and WhatsApp clicks split by a dimension: source, device_type or locale.
     *
     * @return array<int, array{label: string, views: int, clicks: int, rate: float}>
     */
    public function breakdown(array $f, string $column): array
    {
        // The column name is interpolated into SQL, so only known ones pass.
        if (! in_array($column, ['source', 'device_type', 'locale'], true)) {
            return [];
        }

        $rows = $this->events($f, $f['from'], $f['to'])
            ->whereIn('event', [OfferEvent::VIEW, OfferEvent::CLAIM])
            ->selectRaw("COALESCE({$column}, 'unknown') as k, event, COUNT(*) as c")
            ->groupByRaw("COALESCE({$column}, 'unknown'), event")
            ->get()
            ->groupBy('k');

        return $rows
            ->map(function ($group, $k) {
                $byEvent = $group->pluck('c', 'event');
                $views = (int) ($byEvent[OfferEvent::VIEW] ?? 0);
                $clicks = (int) ($byEvent[OfferEvent::CLAIM] ?? 0);

                return ['label' => (string) $k, 'views' => $views, 'clicks' => $clicks, 'rate' => $this->rate($clicks, $views)];
            })
            ->sortByDesc(fn ($r) => $r['clicks'] * 1000000 + $r['views'])
            ->values()
            ->all();
    }

    /**
     * WhatsApp clicks by hour of day and by weekday. Bucketed in PHP so the
     * same code runs on MySQL and on the SQLite test database.
     */
    public function clickTiming(array $f): array
    {
        $hours = array_fill(0, 24, 0);
        $weekdays = array_fill(0, 7, 0);

        $this->events($f, $f['from'], $f['to'])
            ->where('event', OfferEvent::CLAIM)
            ->select(['id', 'created_at'])
            ->orderBy('id')
            ->lazy(1000)
            ->each(function ($row) use (&$hours, &$weekdays) {
                $at = Carbon::parse($row->created_at);
                $hours[(int) $at->format('G')]++;
                $weekdays[(int) $at->format('N') - 1]++;
            });

        return [
            'hours' => $hours,
            'weekdays' => $weekdays,
            'weekday_labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        ];
    }

    /* =====================================================================
     |  TABLES
     * ===================================================================== */

    /**
     * Per-offer funnel inside the selected range.
     */
    public function offerTable(array $f): Collection
    {
        $events = $this->events($f, $f['from'], $f['to'])
            ->selectRaw('offer_id, event, COUNT(*) as c')
            ->groupBy('offer_id', 'event')
            ->get()
            ->groupBy('offer_id');

        $unique = $this->events($f, $f['from'], $f['to'])
            ->where('event', OfferEvent::VIEW)
            ->selectRaw('offer_id, COUNT(DISTINCT session_hash) as c')
            ->groupBy('offer_id')
            ->pluck('c', 'offer_id');

        $offers = Offer::withTrashed()
            ->withCardRelations()
            ->when($f['offer_id'], fn ($q, $id) => $q->whereKey($id))
            ->when($f['provider_id'], fn ($q, $id) => $q->where('service_provider_id', $id))
            ->get();

        return $offers->map(function (Offer $offer) use ($events, $unique) {
            $e = $events->get($offer->id, collect())->pluck('c', 'event');
            $impressions = (int) ($e[OfferEvent::IMPRESSION] ?? 0);
            $views = (int) ($e[OfferEvent::VIEW] ?? 0);
            $clicks = (int) ($e[OfferEvent::CLAIM] ?? 0);

            return [
                'offer' => $offer,
                'status' => $offer->trashed() ? 'deleted' : $offer->status(),
                'impressions' => $impressions,
                'views' => $views,
                'unique_viewers' => (int) ($unique[$offer->id] ?? 0),
                'clicks' => $clicks,
                'shares' => (int) ($e[OfferEvent::SHARE] ?? 0),
                'likes' => (int) ($e[OfferEvent::LIKE] ?? 0),
                'favorites' => (int) ($e[OfferEvent::FAVORITE] ?? 0),
                'comments' => (int) ($e[OfferEvent::COMMENT] ?? 0),
                'ctr' => $this->rate($views, $impressions),
                'click_rate' => $this->rate($clicks, $views),
            ];
        })
            ->filter(fn ($row) => ! $row['offer']->trashed() || $row['views'] + $row['clicks'] + $row['impressions'] > 0)
            ->sortByDesc(fn ($row) => $row['clicks'] * 1000000 + $row['views'])
            ->values();
    }

    /**
     * Roll the per-offer rows up by provider, category or city.
     */
    public function rollUp(Collection $offerRows, string $by): Collection
    {
        return $offerRows
            ->groupBy(function ($row) use ($by) {
                $offer = $row['offer'];

                return match ($by) {
                    'provider' => (string) $offer->service_provider_id,
                    'category' => $offer->providerCategory() ?: 'Uncategorised',
                    default => $offer->providerCity() ?: 'Unknown',
                };
            })
            ->map(function (Collection $rows, $key) use ($by) {
                $views = $rows->sum('views');
                $clicks = $rows->sum('clicks');
                $first = $rows->first()['offer'];

                return [
                    'key' => $key,
                    'label' => $by === 'provider' ? $first->providerName() : $key,
                    'provider_id' => $by === 'provider' ? $first->service_provider_id : null,
                    'offers' => $rows->count(),
                    'impressions' => $rows->sum('impressions'),
                    'views' => $views,
                    'clicks' => $clicks,
                    'likes' => $rows->sum('likes'),
                    'click_rate' => $this->rate($clicks, $views),
                ];
            })
            ->sortByDesc(fn ($r) => $r['clicks'] * 1000000 + $r['views'])
            ->values();
    }

    /* =====================================================================
     |  BUILDING BLOCKS
     * ===================================================================== */

    private function events(array $f, Carbon $from, Carbon $to): EloquentBuilder
    {
        return OfferEvent::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($f['offer_id'], fn ($q, $id) => $q->where('offer_id', $id))
            ->when($f['provider_id'], fn ($q, $id) => $q->where('service_provider_id', $id))
            ->when($f['source'], fn ($q, $s) => $q->where('source', $s))
            ->when($f['device'], fn ($q, $d) => $q->where('device_type', $d));
    }

    private function rate(int|float $part, int|float $whole): float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : 0.0;
    }

    private function growth(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current > 0 ? null : 0.0;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    private function parseDate($value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
