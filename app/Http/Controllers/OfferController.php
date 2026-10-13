<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\ServiceProvider;
use App\Services\OfferTrackingService;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Public side of Local Offers: the listing, one offer, and the link that
 * opens WhatsApp with a message saying the customer came from a SPEEDA offer.
 */
class OfferController extends Controller
{
    private const PER_PAGE = 12;

    public function __construct(private OfferTrackingService $tracker)
    {
    }

    public function index(Request $request): View
    {
        // Show every live offer in the admin's order, including visits through
        // older links that still contain filter or sort parameters.
        $page = max(1, $request->integer('page', 1));
        $offers = Offer::live()->withCardRelations()->ordered()
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->appends($request->only('lang'));

        $endingSoon = Offer::live()->withCardRelations()
            ->whereBetween('ends_at', [today()->toDateString(), today()->addDays(Offer::ENDING_SOON_DAYS)->toDateString()])
            ->orderBy('ends_at')->ordered()->limit(4)->get();

        SEOTools::setTitle(__('offers.meta_title', ['app_name' => config('app.name')]), false);
        SEOTools::setDescription(__('offers.meta_description'));
        SEOTools::setCanonical(route('offers.index'));
        SEOTools::opengraph()->setUrl(route('offers.index'));
        if ($page > 1) {
            SEOTools::metatags()->addMeta('robots', 'noindex, follow');
        }

        return view('offers.index', [
            'offers' => $offers,
            'endingSoon' => $endingSoon,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $offer = Offer::where('slug', $slug)->withCardRelations()->firstOrFail();
        $isAdmin = $this->tracker->isAdminViewer();
        $status = $offer->status();
        $providerVisible = (bool) ($offer->serviceProvider?->is_active && $offer->serviceProvider?->user?->is_active);

        // Drafts and scheduled offers do not exist for visitors yet. Expired
        // offers keep their page so shared links land on something useful.
        if (! $isAdmin && (in_array($status, [Offer::STATUS_DRAFT, Offer::STATUS_SCHEDULED], true) || ! $providerVisible)) {
            abort(404);
        }

        $isLive = $status === Offer::STATUS_LIVE && $providerVisible;

        if ($isLive) {
            $this->tracker->recordView($offer, $request->query('src'));
        }


        $moreFromProvider = Offer::live()->withCardRelations()->ordered()
            ->where('service_provider_id', $offer->service_provider_id)
            ->whereKeyNot($offer->id)
            ->take(3)->get();

        $similar = Offer::live()->withCardRelations()
            ->whereKeyNot($offer->id)
            ->where('service_provider_id', '!=', $offer->service_provider_id)
            ->orderByDesc(ServiceProvider::selectRaw('CASE WHEN category_id = ? THEN 1 ELSE 0 END', [$offer->serviceProvider?->category_id])
                ->whereColumn('service_providers.id', 'offers.service_provider_id')->limit(1))
            ->ordered()->limit($isLive ? 3 : 6)->get();

        $reviewCount = $offer->serviceProvider?->activeReviews()->count() ?? 0;

        // Engagement: what this visitor already did, and the approved comments.
        $viewer = Auth::user();
        $viewerLiked = \App\Models\OfferLike::where('offer_id', $offer->id)
            ->when($viewer,
                fn ($q) => $q->where('user_id', $viewer->id),
                fn ($q) => $q->whereNull('user_id')->where('session_hash', $this->tracker->sessionHash()))
            ->exists();
        $viewerSaved = $viewer && $viewer->isClient()
            && $viewer->favoriteOffers()->whereKey($offer->id)->exists();
        $comments = $offer->approvedComments()->take(30)->get();

        $description = $offer->localized_summary ?: Str::limit(strip_tags((string) $offer->localized_details), 155);
        SEOTools::setTitle($offer->localized_title . ' · ' . $offer->providerName() . ' | ' . config('app.name'), false);
        SEOTools::setDescription($description ?: __('offers.meta_description'));
        SEOTools::setCanonical(route('offers.show', $offer->slug));
        SEOTools::opengraph()->setUrl(route('offers.show', $offer->slug));
        SEOTools::opengraph()->setType('website');
        if ($offer->image_url) {
            SEOTools::opengraph()->addImage(url($offer->image_url));
            SEOTools::twitter()->setImage(url($offer->image_url));
        }
        if (! $isLive) {
            SEOTools::metatags()->addMeta('robots', 'noindex, follow');
        }

        return view('offers.show', [
            'offer' => $offer,
            'status' => $status,
            'isLive' => $isLive,
            'isAdmin' => $isAdmin,
            'moreFromProvider' => $moreFromProvider,
            'similar' => $similar,
            'reviewCount' => $reviewCount,
            'source' => OfferEvent::normalizeSource($request->query('src')),
            'viewerLiked' => $viewerLiked,
            'viewerSaved' => $viewerSaved,
            'comments' => $comments,
        ]);
    }

    /**
     * "Get this offer on WhatsApp": counts the click, then opens the chat.
     *
     * A plain link, so it works with or without JavaScript and is never
     * blocked as a popup. No code and no limit: the pre-filled message names
     * the offer and links to it on SPEEDA, which is the proof it came from us.
     */
    public function go(Request $request, string $slug): RedirectResponse
    {
        $offer = Offer::where('slug', $slug)->withCardRelations()->firstOrFail();

        if (! $offer->isLive() && ! $this->tracker->isAdminViewer()) {
            return redirect()->route('offers.show', $offer->slug);
        }

        $this->tracker->recordClick($offer, $request->query('src'));

        return redirect()->away($this->tracker->destinationUrl($offer));
    }

    /**
     * Small fire-and-forget events from the browser: card impressions as they
     * scroll into view, and shares.
     */
    public function track(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event' => ['required', 'in:impression,share'],
            'ids' => ['required', 'array', 'min:1', 'max:24'],
            'ids.*' => ['integer'],
            'source' => ['nullable', 'string', 'max:30'],
        ]);

        if ($data['event'] === OfferEvent::IMPRESSION) {
            $count = $this->tracker->recordImpressions($data['ids'], $data['source'] ?? null);

            return response()->json(['recorded' => $count]);
        }

        $offer = Offer::live()->whereKey($data['ids'][0])->first();
        $recorded = $offer ? $this->tracker->record($offer, $data['event'], $data['source'] ?? null, 10) : false;

        return response()->json(['recorded' => (int) $recorded]);
    }
}
