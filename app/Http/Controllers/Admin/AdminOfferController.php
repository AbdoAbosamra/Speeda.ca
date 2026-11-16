<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\OfferSection;
use App\Services\OfferReview;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Local Offers, admin side: the home section settings and every offer in it.
 *
 * Admins create offers and review provider submissions. Changes are logged;
 * soft deletion lets Undo restore the offer and its statistics.
 */
class AdminOfferController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;


    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /* =====================================================================
     |  LIST + SECTION SETTINGS
     * ===================================================================== */

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), Offer::STATUSES, true) ? $request->query('status') : null;
        $search = trim((string) $request->query('q', ''));

        $offers = Offer::withCardRelations()
            ->when($status, fn (Builder $q, $s) => $q->withStatus($s))
            ->when($search !== '', function (Builder $q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(fn ($w) => $w->where('title_en', 'like', $like)
                    ->orWhere('title_ar', 'like', $like)
                    ->orWhere('title_fr', 'like', $like)
                    ->orWhereHas('serviceProvider', fn ($p) => $p->where('company_name', 'like', $like)));
            })
            ->ordered()
            ->get();

        $counts = [];
        foreach (Offer::STATUSES as $s) {
            $counts[$s] = Offer::query()->withStatus($s)->count();
        }

        $since = now()->subDays(30);
        $metrics = [
            'views_30' => OfferEvent::where('event', OfferEvent::VIEW)->where('created_at', '>=', $since)->count(),
            'clicks_30' => OfferEvent::where('event', OfferEvent::CLAIM)->where('created_at', '>=', $since)->count(),
            'likes_30' => OfferEvent::where('event', OfferEvent::LIKE)->where('created_at', '>=', $since)->count(),
        ];

        $section = OfferSection::current();
        $homeCount = Offer::forHomePage($section)->count();

        return view('admin.offers.index', compact('offers', 'counts', 'metrics', 'section', 'homeCount', 'status', 'search'));
    }

    public function updateSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'title_en' => ['nullable', 'string', 'max:120'],
            'title_ar' => ['nullable', 'string', 'max:120'],
            'title_fr' => ['nullable', 'string', 'max:120'],
            'subtitle_en' => ['nullable', 'string', 'max:255'],
            'subtitle_ar' => ['nullable', 'string', 'max:255'],
            'subtitle_fr' => ['nullable', 'string', 'max:255'],
            'max_items' => ['required', 'integer', Rule::in(OfferSection::MAX_ITEMS_OPTIONS)],
        ]);

        $section = OfferSection::current();
        $old = $section->getOriginal();

        $section->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
            'updated_by' => Auth::id(),
        ]));

        $this->logUpdate($section, $old, 'Offers home section');
        ErrorHelper::flashNotification('Home section settings saved.', 'success');

        return redirect()->route('admin.offers.index');
    }

    /* =====================================================================
     |  CREATE / EDIT
     * ===================================================================== */

    public function create(): View
    {
        $offer = new Offer([
            'deal_type' => Offer::TYPE_PERCENT,
            'deal_value' => 10,
            'cta_type' => Offer::CTA_WHATSAPP,
            'is_active' => true,
            'is_featured' => true,
            'starts_at' => now()->startOfDay(),
            'ends_at' => now()->addDays(30)->startOfDay(),
            'sort_order' => Offer::nextSortOrder(),
        ]);

        return view('admin.offers.form', ['offer' => $offer, 'providers' => app(\App\Services\OfferEditor::class)->providerOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = app(\App\Services\OfferEditor::class)->validated($request);

        try {
            $offer = DB::transaction(function () use ($request, $data) {
                $offer = new Offer($data);
                $offer->slug = app(\App\Services\OfferEditor::class)->slugFor($request, $data);
                $offer->created_by = Auth::id();
                $offer->updated_by = Auth::id();
                $this->applyReview($request, $offer);

                if ($request->hasFile('image')) {
                    $offer->image_path = app(\App\Services\OfferEditor::class)->storeImage($request->file('image'));
                }

                $offer->save();

                return $offer;
            });

            $this->logCreate($offer, $offer->title_en);
            ErrorHelper::flashNotification('Offer created.', 'success');

            return redirect()->route('admin.offers.edit', $offer);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Offer create failed', ['error' => $e->getMessage()]);
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back()->withInput();
        }
    }

    public function edit(Offer $offer): View
    {
        $offer->loadMissing(['serviceProvider.user', 'serviceProvider.location', 'serviceProvider.category']);

        return view('admin.offers.form', [
            'offer' => $offer,
            'providers' => app(\App\Services\OfferEditor::class)->providerOptions(),
            'stats' => [
                'views' => $offer->views_count,
                'clicks' => $offer->claims_count,
                'likes' => $offer->likes_count,
            ],
        ]);
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $data = app(\App\Services\OfferEditor::class)->validated($request, $offer);

        try {
            $old = $offer->getOriginal();
            $offer->fill($data);
            $offer->slug = app(\App\Services\OfferEditor::class)->slugFor($request, $data, $offer);
            $offer->updated_by = Auth::id();
            $this->applyReview($request, $offer);

            if ($request->boolean('remove_image') && ! $request->hasFile('image')) {
                $offer->deleteImageFile();
                $offer->image_path = null;
            }

            if ($request->hasFile('image')) {
                $previous = $offer->image_path;
                $offer->image_path = app(\App\Services\OfferEditor::class)->storeImage($request->file('image'));

                if ($previous && $previous !== $offer->image_path) {
                    Storage::disk('public')->delete($previous);
                }
            }

            $offer->save();
            $this->logUpdate($offer, $old, $offer->title_en);

            // "Save and approve" on a provider submission: the edits above are
            // saved first, so approving never throws away the admin's changes.
            if ($request->input('review_decision') === OfferReview::APPROVE && $offer->awaitsApproval()) {
                $before = $offer->getOriginal();
                if (! app(OfferReview::class)->decide($offer, OfferReview::APPROVE, $request->user(), null, $request->boolean('is_featured'))) {
                    return redirect()->route('admin.offers.edit', $offer)->with('error', __('offer_submission.already_reviewed'));
                }
                $this->logUpdate($offer, $before, 'Offer approved: '.$offer->title_en);

                return redirect()->route('admin.offers.edit', $offer)->with('success', __('offer_submission.review_approved'));
            }

            ErrorHelper::flashNotification('Offer saved.', 'success');

            return redirect()->route('admin.offers.edit', $offer);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Offer update failed', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back()->withInput();
        }
    }

    /**
     * A draft copy, for running the same deal again or for another provider.
     */
    public function duplicate(Offer $offer): RedirectResponse
    {
        $copy = $offer->replicate(['slug', 'submission_key', 'views_count', 'claims_count', 'likes_count', 'comments_count', 'favorites_count', 'image_path', 'verified_at', 'verified_by', 'created_at', 'updated_at', 'deleted_at']);
        $copy->title_en = Str::limit($offer->title_en . ' (copy)', 120, '');
        $copy->slug = Offer::uniqueSlug($copy->title_en, $offer->serviceProvider?->company_name);
        $copy->is_active = false;
        $copy->review_status = Offer::REVIEW_APPROVED;
        $copy->submitted_at = $copy->reviewed_at = $copy->reviewed_by = $copy->rejection_reason = null;
        $copy->sort_order = Offer::nextSortOrder();
        $copy->created_by = Auth::id();
        $copy->updated_by = Auth::id();

        if ($offer->image_path && Storage::disk('public')->exists($offer->image_path)) {
            $ext = pathinfo($offer->image_path, PATHINFO_EXTENSION) ?: 'webp';
            $path = Offer::IMAGE_DIR . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->copy($offer->image_path, $path);
            $copy->image_path = $path;
        }

        $copy->save();
        $this->logCreate($copy, $copy->title_en);
        ErrorHelper::flashNotification('Draft copy created. Review it, then switch it on.', 'success');

        return redirect()->route('admin.offers.edit', $copy);
    }

    /* =====================================================================
     |  QUICK ACTIONS
     * ===================================================================== */

    public function review(Request $request, Offer $offer, OfferReview $reviews): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in([OfferReview::APPROVE, OfferReview::REJECT])],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
            'is_featured' => ['nullable', 'boolean'],
        ], [
            'rejection_reason.required_if' => 'Write the reason so the provider knows what to fix.',
        ]);

        $old = $offer->getOriginal();
        if (! $reviews->decide($offer, $data['decision'], $request->user(), $data['rejection_reason'] ?? null, $request->boolean('is_featured'))) {
            return redirect()->route('admin.offers.edit', $offer)->with('error', __('offer_submission.already_reviewed'));
        }
        $this->logUpdate($offer, $old, 'Offer '.$data['decision'].': '.$offer->title_en);

        return redirect()->route('admin.offers.edit', $offer)
            ->with('success', __($data['decision'] === OfferReview::APPROVE ? 'offer_submission.review_approved' : 'offer_submission.review_rejected'));
    }

    public function toggle(Offer $offer): RedirectResponse
    {
        if ($offer->review_status !== Offer::REVIEW_APPROVED) {
            return redirect()->route('admin.offers.edit', $offer)->with('error', __('offer_submission.review_first'));
        }
        $offer->update(['is_active' => ! $offer->is_active, 'updated_by' => Auth::id()]);
        $this->logAction($offer->is_active ? 'activate' : 'deactivate', $offer, null, $offer->title_en);
        ErrorHelper::flashNotification($offer->is_active ? 'Offer switched on.' : 'Offer switched off (draft).', 'success');

        return redirect()->back();
    }

    public function feature(Offer $offer): RedirectResponse
    {
        $old = $offer->getOriginal();
        $offer->update(['is_featured' => ! $offer->is_featured, 'updated_by' => Auth::id()]);
        $this->logUpdate($offer, $old, $offer->title_en);
        ErrorHelper::flashNotification($offer->is_featured ? 'Offer added to the home page.' : 'Offer removed from the home page.', 'success');

        return redirect()->back();
    }

    public function destroy(Offer $offer): RedirectResponse
    {
        // Soft delete: the cover image stays on disk so Undo restores it intact,
        // and offer links already shared keep showing the "offer ended" page.
        $this->logAction('delete', $offer, ['deleted' => $offer->toArray()], $offer->title_en);
        $offer->delete();
        ErrorHelper::flashNotification('Offer deleted. You can undo this from the activity log.', 'success');

        return redirect()->route('admin.offers.index');
    }

    /**
     * Drag-and-drop order from the list. The order is also the home page order.
     */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        DB::transaction(function () use ($data) {
            foreach (array_values($data['ids']) as $i => $id) {
                Offer::withoutTimestamps(fn () => Offer::whereKey($id)->update(['sort_order' => $i + 1]));
            }
        });

        if ($first = Offer::find($data['ids'][0])) {
            $this->logAction('reorder', $first, ['ids' => $data['ids']], 'Offers order');
        }

        return response()->json(['ok' => true]);
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, 'offers');
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'activate' => __('admin.bulk_verb_activated'),
            'deactivate' => __('admin.bulk_verb_deactivated'),
            'feature' => 'added to the home page',
            'unfeature' => 'removed from the home page',
            'delete' => __('admin.bulk_verb_deleted'),
        ];
    }

    protected function bulkQuery(string $resource): Builder
    {
        return Offer::query();
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $offer)
    {
        switch ($action) {
            case 'activate':
                if ($offer->review_status !== Offer::REVIEW_APPROVED) {
                    return __('offer_submission.review_first');
                }
                if ($offer->is_active) {
                    return __('admin.bulk_reason_already_active');
                }
                $offer->update(['is_active' => true, 'updated_by' => Auth::id()]);
                $this->logAction('activate', $offer, null, $offer->title_en);
                break;

            case 'deactivate':
                if (! $offer->is_active) {
                    return __('admin.bulk_reason_already_inactive');
                }
                $offer->update(['is_active' => false, 'updated_by' => Auth::id()]);
                $this->logAction('deactivate', $offer, null, $offer->title_en);
                break;

            case 'feature':
            case 'unfeature':
                $want = $action === 'feature';
                if ($offer->is_featured === $want) {
                    return $want ? 'Already on the home page' : 'Already off the home page';
                }
                $old = $offer->getOriginal();
                $offer->update(['is_featured' => $want, 'updated_by' => Auth::id()]);
                $this->logUpdate($offer, $old, $offer->title_en);
                break;

            case 'delete':
                $this->logAction('delete', $offer, ['deleted' => $offer->toArray()], $offer->title_en);
                $offer->delete();
                break;

            default:
                return __('admin.bulk_reason_failed');
        }

        return true;
    }

    /* =====================================================================
     |  HELPERS
     * ===================================================================== */

    /**
     * "Reviewed by the SPEEDA team on <date>" is public, so the date must stay
     * true: it is kept while the reviewed facts are unchanged, set again when
     * the admin re-confirms changed facts, and cleared when unticked.
     */
    private function applyReview(Request $request, Offer $offer): void
    {
        if (! $request->boolean('mark_verified')) {
            $offer->verified_at = null;
            $offer->verified_by = null;

            return;
        }

        $factsChanged = $offer->isDirty(['service_provider_id', 'deal_type', 'deal_value', 'starts_at', 'ends_at', 'cta_type', 'cta_url']);

        if (! $offer->verified_at || $factsChanged) {
            $offer->verified_at = now();
            $offer->verified_by = Auth::id();
        }
    }

}
