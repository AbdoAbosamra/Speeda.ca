<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferEvent;
use App\Support\DeviceType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Records offer events: impressions, views and WhatsApp clicks.
 *
 * Admins and bots are never recorded, so the numbers the admin reads are
 * real visitors only. Tracking failures are logged and swallowed: a broken
 * analytics insert must never stop a visitor from reaching WhatsApp.
 */
class OfferTrackingService
{
    /** A repeat view from the same session within this window is not counted again. */
    private const VIEW_DEDUPE_MINUTES = 30;

    /** One impression per session, offer and placement per day. */
    private const IMPRESSION_DEDUPE_MINUTES = 1440;

    public function sessionHash(): string
    {
        $sessionId = session()->getId() ?: 'anonymous';
        $userAgent = request()->userAgent() ?? 'unknown';

        return hash('sha256', config('app.analytics_salt') . $sessionId . '|' . $userAgent);
    }

    public function deviceType(): string
    {
        return DeviceType::fromUserAgent(request()->userAgent());
    }

    /**
     * Admins browse the site constantly while managing it, and bots crawl
     * every card; neither is an audience.
     */
    public function shouldTrack(): bool
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return false;
        }

        return $this->deviceType() !== DeviceType::BOT;
    }

    public function isAdminViewer(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    /**
     * Record one event. Returns true when a row was written.
     */
    public function record(Offer $offer, string $event, ?string $source = null, ?int $dedupeMinutes = null): bool
    {
        if (! in_array($event, OfferEvent::EVENTS, true) || ! $this->shouldTrack()) {
            return false;
        }

        $hash = $this->sessionHash();
        $source = OfferEvent::normalizeSource($source);

        if ($dedupeMinutes !== null
            && ! Cache::add("offer_evt:{$event}:{$offer->id}:{$source}:{$hash}", 1, now()->addMinutes($dedupeMinutes))) {
            return false;
        }

        try {
            OfferEvent::create([
                'offer_id' => $offer->id,
                'service_provider_id' => $offer->service_provider_id,
                'event' => $event,
                'source' => $source,
                'session_hash' => $hash,
                'user_id' => Auth::id(),
                'device_type' => $this->deviceType(),
                'locale' => substr((string) app()->getLocale(), 0, 5),
                'created_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Offer event insert failed', ['offer_id' => $offer->id, 'event' => $event, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function recordView(Offer $offer, ?string $source): void
    {
        if ($this->record($offer, OfferEvent::VIEW, $source, self::VIEW_DEDUPE_MINUTES)) {
            Offer::withoutTimestamps(fn () => $offer->newQuery()->whereKey($offer->id)->increment('views_count'));
        }
    }

    /**
     * Impressions arrive in batches from the browser as cards scroll into view.
     *
     * @param  array<int, int>  $offerIds
     */
    public function recordImpressions(array $offerIds, ?string $source): int
    {
        if (! $this->shouldTrack() || $offerIds === []) {
            return 0;
        }

        $written = 0;

        Offer::live()->whereIn('id', array_slice(array_unique($offerIds), 0, 24))->get()
            ->each(function (Offer $offer) use ($source, &$written) {
                if ($this->record($offer, OfferEvent::IMPRESSION, $source, self::IMPRESSION_DEDUPE_MINUTES)) {
                    $written++;
                }
            });

        return $written;
    }

    /**
     * A visitor pressed "Get this offer on WhatsApp".
     *
     * Counted once per visitor every 30 minutes, so pressing the button again
     * after coming back from WhatsApp is not a second lead. Admins and bots
     * are never counted.
     */
    public function recordClick(Offer $offer, ?string $source): bool
    {
        if (! $this->record($offer, OfferEvent::CLAIM, $source, self::VIEW_DEDUPE_MINUTES)) {
            return false;
        }

        Offer::withoutTimestamps(fn () => $offer->newQuery()->whereKey($offer->id)->increment('claims_count'));

        return true;
    }

    /**
     * The pre-filled WhatsApp message, in the visitor's language. It names
     * the offer and links to it on SPEEDA, which is all the provider needs to
     * see that the customer came from a SPEEDA offer.
     */
    public function whatsappMessage(Offer $offer): string
    {
        return __('offers.whatsapp_message', [
            'provider' => $offer->providerName(),
            'title' => $offer->localized_title,
            'badge' => $offer->badgeLabel(),
            'link' => route('offers.show', $offer->slug),
        ]);
    }

    public function whatsappUrl(Offer $offer): ?string
    {
        return \App\Support\WhatsApp::url($offer->serviceProvider?->whatsappRawNumber(), $this->whatsappMessage($offer));
    }

    /**
     * Where the offer button sends the visitor.
     */
    public function destinationUrl(Offer $offer): string
    {
        return match ($offer->effectiveCta()) {
            Offer::CTA_WHATSAPP => $this->whatsappUrl($offer),
            Offer::CTA_URL => $offer->cta_url,
            default => route('service-providers.show', $offer->service_provider_id),
        };
    }
}
