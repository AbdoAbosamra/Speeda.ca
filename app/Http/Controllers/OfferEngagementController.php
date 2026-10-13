<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferComment;
use App\Models\OfferEvent;
use App\Models\OfferLike;
use App\Services\OfferTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Likes, comments and favorites on offers.
 *
 *  - Likes: anyone. An account likes once; a guest is one session.
 *  - Comments: anyone. Stored as pending and shown only after an admin
 *    approves them, so nothing a visitor writes reaches the page directly.
 *  - Favorites: client accounts only, listed on the client's favorites page.
 *
 * Every action answers JSON for the page's buttons and a redirect for the
 * plain form fallback.
 */
class OfferEngagementController extends Controller
{
    public function __construct(private OfferTrackingService $tracker)
    {
    }

    public function like(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $offer = $this->liveOffer($slug);

        if (! $offer) {
            return $this->refuse($request, __('offers.ended_title'), 410);
        }

        $match = Auth::check()
            ? ['offer_id' => $offer->id, 'user_id' => Auth::id()]
            : ['offer_id' => $offer->id, 'user_id' => null, 'session_hash' => $this->tracker->sessionHash()];

        $liked = DB::transaction(function () use ($offer, $match) {
            // Serialize toggles even when the like row does not exist yet.
            Offer::whereKey($offer->id)->lockForUpdate()->firstOrFail();
            $existing = OfferLike::where($match)->lockForUpdate()->first();

            if ($existing) {
                $existing->delete();
                $liked = false;
            } else {
                OfferLike::create($match + ['created_at' => now()]);
                $liked = true;
            }

            $count = OfferLike::where('offer_id', $offer->id)->count();
            Offer::withoutTimestamps(fn () => Offer::whereKey($offer->id)->update(['likes_count' => $count]));

            return $liked;
        });

        if ($liked) {
            $this->tracker->record($offer, OfferEvent::LIKE, $request->input('source'));
        }

        $count = (int) Offer::whereKey($offer->id)->value('likes_count');

        return $request->expectsJson()
            ? response()->json(['liked' => $liked, 'count' => $count, 'label' => trans_choice('offers.likes_count', $count, ['count' => $count])])
            : redirect()->to(route('offers.show', $offer->slug) . '#engage');
    }

    public function comment(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $offer = $this->liveOffer($slug);

        if (! $offer) {
            return $this->refuse($request, __('offers.comment_closed'), 410);
        }

        $user = Auth::user();

        // Validate the text we actually store, so markup-only comments cannot
        // become empty moderation entries after stripping HTML.
        if (is_string($request->input('body'))) {
            $request->merge(['body' => trim(strip_tags($request->input('body')))]);
        }

        $data = $request->validate([
            'author_name' => [$user ? 'nullable' : 'required', 'string', 'min:2', 'max:60'],
            'body' => ['required', 'string', 'min:3', 'max:1000'],
            // Honeypot: hidden from people, filled in by spam bots.
            'website' => ['nullable', 'max:0'],
        ], [], [
            'author_name' => __('offers.comment_name'),
            'body' => __('offers.comment_body'),
        ]);

        $comment = OfferComment::create([
            'offer_id' => $offer->id,
            'user_id' => $user?->id,
            'author_name' => trim($user?->name ?: $data['author_name']),
            'body' => trim(strip_tags($data['body'])),
            'status' => OfferComment::STATUS_PENDING,
            'locale' => substr((string) app()->getLocale(), 0, 5),
            'session_hash' => $this->tracker->sessionHash(),
        ]);

        $this->tracker->record($offer, OfferEvent::COMMENT, $request->input('source'));

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'id' => $comment->id, 'message' => __('offers.comment_thanks')])
            : redirect()->to(route('offers.show', $offer->slug) . '#comments')->with('comment_sent', __('offers.comment_thanks'));
    }

    public function favorite(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            // Come back to the offer (not this POST URL) after logging in.
            session()->put('url.intended', route('offers.show', $slug));

            return $request->expectsJson()
                ? response()->json(['message' => __('offers.favorites_login'), 'login' => route('login') . '?tab=login'], 401)
                : redirect()->to(route('login') . '?tab=login');
        }

        if (! $user->isClient()) {
            return $this->refuse($request, __('offers.favorites_clients_only'), 403);
        }

        $offer = Offer::where('slug', $slug)->first();
        abort_unless($offer, 404);

        $saved = DB::transaction(function () use ($user, $offer) {
            Offer::whereKey($offer->id)->lockForUpdate()->firstOrFail();
            $exists = $user->favoriteOffers()->whereKey($offer->id)->exists();

            if ($exists) {
                $user->favoriteOffers()->detach($offer->id);
            } elseif ($offer->isLive()) {
                $user->favoriteOffers()->attach($offer->id, ['created_at' => now()]);
            }

            $count = DB::table('offer_favorites')->where('offer_id', $offer->id)->count();
            Offer::withoutTimestamps(fn () => Offer::whereKey($offer->id)->update(['favorites_count' => $count]));

            return ! $exists && $offer->isLive();
        });

        if ($saved) {
            $this->tracker->record($offer, OfferEvent::FAVORITE, $request->input('source'));
        }

        return $request->expectsJson()
            ? response()->json(['saved' => $saved, 'label' => $saved ? __('offers.saved_favorite') : __('offers.save_favorite')])
            : redirect()->back();
    }

    public function favorites(Request $request): View
    {
        \Artesaos\SEOTools\Facades\SEOTools::metatags()->setRobots('noindex, nofollow');
        $user = $request->user();
        abort_unless($user && $user->isClient(), 403, __('offers.favorites_clients_only'));

        $offers = $user->favoriteOffers()
            ->withCardRelations()
            ->where('offers.is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))
            ->whereHas('serviceProvider', fn ($q) => $q->publiclyVisible())
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);

        return view('offers.favorites', ['offers' => $offers]);
    }

    private function liveOffer(string $slug): ?Offer
    {
        $offer = Offer::where('slug', $slug)->withCardRelations()->first();
        abort_unless($offer, 404);

        return $offer->isLive() ? $offer : null;
    }

    private function refuse(Request $request, string $message, int $status): JsonResponse|RedirectResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message], $status)
            : redirect()->back()->with('error', $message);
    }
}
