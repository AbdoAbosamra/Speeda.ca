<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Services\OfferEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProviderOfferController extends Controller
{
    private function gate(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            $request->session()->put('url.intended', route('provider.offers.create'));

            return redirect()->route('register', ['role' => 'service_provider', 'form' => 'register']);
        }
        if ($user->isAdmin()) {
            return redirect()->route('admin.offers.create');
        }
        if ($user->role !== 'service_provider' || ! $user->serviceProvider) {
            return redirect()->route('provider.enroll');
        }
        abort_unless($user->is_active && $user->serviceProvider->is_active, 403);

        return null;
    }

    private function completeProfile(Request $request, ServiceProvider $provider)
    {
        if ($missing = $provider->missingOfferProfileFields()) {
            $request->session()->put('offer_profile_completion', true);

            return redirect()->to(route('service-providers.show', $provider).'#edit-basic-info')
                ->with('error', __('offer_submission.complete_profile', ['fields' => implode(app()->getLocale() === 'ar' ? '، ' : ', ', $missing)]));
        }

        return null;
    }

    public function create(Request $request, OfferEditor $editor)
    {
        if ($redirect = $this->gate($request)) {
            return $redirect;
        }
        $provider = $request->user()->serviceProvider;
        if ($redirect = $this->completeProfile($request, $provider)) {
            return $redirect;
        }
        $request->session()->forget('offer_profile_completion');
        $offer = new Offer([
            'service_provider_id' => $provider->id,
            'deal_type' => Offer::TYPE_PERCENT,
            'deal_value' => 10,
            'cta_type' => Offer::CTA_WHATSAPP,
            'starts_at' => today(),
            'ends_at' => today()->addDays(30),
            'is_active' => false,
            'is_featured' => false,
            'review_status' => Offer::STATUS_PENDING,
        ]);

        return view('admin.offers.form', [
            'offer' => $offer,
            'providers' => $editor->providerOptions($provider),
            'providerMode' => true,
            'submissionToken' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, OfferEditor $editor)
    {
        if ($redirect = $this->gate($request)) {
            return $redirect;
        }
        $provider = $request->user()->serviceProvider;
        if ($redirect = $this->completeProfile($request, $provider)) {
            return $redirect;
        }

        $request->validate(['submission_token' => ['required', 'uuid']]);
        $submissionKey = hash('sha256', $request->user()->id.':'.$request->input('submission_token'));

        // Ownership and publication controls never come from provider input.
        $request->merge([
            'service_provider_id' => $provider->id,
            'is_active' => false, 'is_featured' => false,
            'slug' => null, 'sort_order' => null,
        ]);
        $data = $editor->validated($request);
        $image = null;
        try {
            $created = DB::transaction(function () use ($request, $provider, $data, $editor, $submissionKey, &$image) {
                // Serialize submissions from this provider, including requests in
                // different tabs or retries after a connection failure.
                ServiceProvider::whereKey($provider->id)->lockForUpdate()->firstOrFail();
                if (Offer::withTrashed()->where('submission_key', $submissionKey)->exists()) {
                    return false;
                }
                if ($request->hasFile('image')) {
                    $image = $editor->storeImage($request->file('image'));
                }
                $offer = new Offer($data);
                $offer->forceFill([
                    'submission_key' => $submissionKey,
                    'slug' => $editor->slugFor($request, $data),
                    'image_path' => $image,
                    'review_status' => Offer::STATUS_PENDING,
                    'submitted_at' => now(),
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                    'verified_at' => null, 'verified_by' => null,
                    'reviewed_at' => null, 'reviewed_by' => null,
                ])->save();
                return true;
            });
        } catch (\Throwable $e) {
            if ($image) {
                Storage::disk('public')->delete($image);
            }
            throw $e;
        }

        return redirect()->route('provider.offers.index')->with('success', __($created ? 'offer_submission.submitted' : 'offer_submission.already_submitted'));
    }

    /** Pending or rejected offers of this provider can be changed; approved ones cannot. */
    private function ownEditable(Request $request, Offer $offer)
    {
        abort_unless((int) $offer->service_provider_id === (int) $request->user()->serviceProvider->id, 404);

        if (! $offer->awaitsApproval()) {
            return redirect()->route('provider.offers.index')->with('error', __('offer_submission.not_editable'));
        }

        return null;
    }

    public function edit(Request $request, Offer $offer, OfferEditor $editor)
    {
        if ($redirect = $this->gate($request)) {
            return $redirect;
        }
        if ($redirect = $this->ownEditable($request, $offer)) {
            return $redirect;
        }

        return view('admin.offers.form', [
            'offer' => $offer,
            'providers' => $editor->providerOptions($request->user()->serviceProvider),
            'providerMode' => true,
        ]);
    }

    /**
     * Save the provider's changes and send the offer (back) to review. A
     * rejected offer becomes pending again; the old reason is cleared.
     */
    public function update(Request $request, Offer $offer, OfferEditor $editor)
    {
        if ($redirect = $this->gate($request)) {
            return $redirect;
        }
        if ($redirect = $this->ownEditable($request, $offer)) {
            return $redirect;
        }
        $provider = $request->user()->serviceProvider;
        if ($redirect = $this->completeProfile($request, $provider)) {
            return $redirect;
        }

        $request->merge([
            'service_provider_id' => $provider->id,
            'is_active' => false, 'is_featured' => false,
            'slug' => null, 'sort_order' => $offer->sort_order,
        ]);
        $data = $editor->validated($request, $offer);

        $newImage = $request->hasFile('image') ? $editor->storeImage($request->file('image')) : null;
        $previousImage = $offer->image_path;
        try {
            $saved = DB::transaction(function () use ($request, $offer, $data, $newImage) {
                $locked = Offer::whereKey($offer->id)->lockForUpdate()->firstOrFail();
                // The admin may have approved it in the meantime.
                if (! $locked->awaitsApproval()) {
                    return false;
                }
                $locked->fill($data);
                if ($newImage) {
                    $locked->image_path = $newImage;
                } elseif ($request->boolean('remove_image')) {
                    $locked->image_path = null;
                }
                $locked->forceFill([
                    'review_status' => Offer::STATUS_PENDING,
                    'submitted_at' => now(),
                    'updated_by' => $request->user()->id,
                    'reviewed_at' => null, 'reviewed_by' => null, 'rejection_reason' => null,
                    'verified_at' => null, 'verified_by' => null,
                ])->save();

                return true;
            });
        } catch (\Throwable $e) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }
            throw $e;
        }

        if (! $saved) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            return redirect()->route('provider.offers.index')->with('error', __('offer_submission.not_editable'));
        }

        if ($previousImage && $previousImage !== $offer->fresh()->image_path) {
            Storage::disk('public')->delete($previousImage);
        }

        return redirect()->route('provider.offers.index')->with('success', __('offer_submission.resubmitted'));
    }

    public function index(Request $request)
    {
        if ($redirect = $this->gate($request)) {
            return $redirect;
        }

        return view('offers.mine', [
            'offers' => Offer::withCardRelations()->where('service_provider_id', $request->user()->serviceProvider->id)->latest()->paginate(12),
        ]);
    }
}
