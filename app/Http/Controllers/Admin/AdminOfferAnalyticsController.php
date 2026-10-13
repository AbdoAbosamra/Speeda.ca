<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\ServiceProvider;
use App\Services\Admin\OfferAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Offer statistics: impressions, page views, WhatsApp clicks and engagement.
 */
class AdminOfferAnalyticsController extends Controller
{
    public function __construct(private OfferAnalyticsService $analytics)
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request): View
    {
        $f = $this->analytics->filtersFromRequest($request);
        $offerRows = $this->analytics->offerTable($f);

        return view('admin.offers.analytics', [
            'f' => $f,
            'ranges' => OfferAnalyticsService::RANGES,
            'summary' => $this->analytics->summary($f),
            'trend' => $this->analytics->trend($f),
            'bySource' => $this->analytics->breakdown($f, 'source'),
            'byDevice' => $this->analytics->breakdown($f, 'device_type'),
            'byLocale' => $this->analytics->breakdown($f, 'locale'),
            'timing' => $this->analytics->clickTiming($f),
            'offerRows' => $offerRows,
            'providerRows' => $this->analytics->rollUp($offerRows, 'provider'),
            'categoryRows' => $this->analytics->rollUp($offerRows, 'category'),
            'cityRows' => $this->analytics->rollUp($offerRows, 'city'),
            'selectedOffer' => $f['offer_id'] ? Offer::withTrashed()->with('serviceProvider')->find($f['offer_id']) : null,
            'offerOptions' => Offer::withTrashed()->with('serviceProvider:id,company_name')->orderByDesc('id')->get(['id', 'title_en', 'service_provider_id', 'deleted_at']),
            'providerOptions' => ServiceProvider::whereIn('id', Offer::withTrashed()->select('service_provider_id'))->orderBy('company_name')->get(['id', 'company_name']),
            'sources' => OfferEvent::SOURCES,
        ]);
    }
}
