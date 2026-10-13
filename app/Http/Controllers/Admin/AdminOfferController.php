<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\OfferSection;
use App\Models\ServiceProvider;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/**
 * Local Offers, admin side: the home section settings and every offer in it.
 *
 * The admin is the only one who creates or changes offers. Each change is
 * written to the activity log, and deletes are soft so Undo can bring an
 * offer (and its statistics) back.
 */
class AdminOfferController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;

    private const IMAGE_MAX_KB = 5120;
    private const IMAGE_MAX_WIDTH = 1600;

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

        return view('admin.offers.form', ['offer' => $offer, 'providers' => $this->providerOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        try {
            $offer = DB::transaction(function () use ($request, $data) {
                $offer = new Offer($data);
                $offer->slug = $this->slugFor($request, $data);
                $offer->created_by = Auth::id();
                $offer->updated_by = Auth::id();
                $this->applyReview($request, $offer);

                if ($request->hasFile('image')) {
                    $offer->image_path = $this->storeImage($request->file('image'));
                }

                $offer->save();

                return $offer;
            });

            $this->logCreate($offer, $offer->title_en);
            ErrorHelper::flashNotification('Offer created.', 'success');

            return redirect()->route('admin.offers.edit', $offer);
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
            'providers' => $this->providerOptions(),
            'stats' => [
                'views' => $offer->views_count,
                'clicks' => $offer->claims_count,
                'likes' => $offer->likes_count,
            ],
        ]);
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $data = $this->validated($request, $offer);

        try {
            $old = $offer->getOriginal();
            $offer->fill($data);
            $offer->slug = $this->slugFor($request, $data, $offer);
            $offer->updated_by = Auth::id();
            $this->applyReview($request, $offer);

            if ($request->boolean('remove_image') && ! $request->hasFile('image')) {
                $offer->deleteImageFile();
                $offer->image_path = null;
            }

            if ($request->hasFile('image')) {
                $previous = $offer->image_path;
                $offer->image_path = $this->storeImage($request->file('image'));

                if ($previous && $previous !== $offer->image_path) {
                    Storage::disk('public')->delete($previous);
                }
            }

            $offer->save();
            $this->logUpdate($offer, $old, $offer->title_en);
            ErrorHelper::flashNotification('Offer saved.', 'success');

            return redirect()->route('admin.offers.edit', $offer);
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
        $copy = $offer->replicate(['slug', 'views_count', 'claims_count', 'likes_count', 'comments_count', 'favorites_count', 'image_path', 'verified_at', 'verified_by', 'created_at', 'updated_at', 'deleted_at']);
        $copy->title_en = Str::limit($offer->title_en . ' (copy)', 120, '');
        $copy->slug = Offer::uniqueSlug($copy->title_en, $offer->serviceProvider?->company_name);
        $copy->is_active = false;
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

    public function toggle(Offer $offer): RedirectResponse
    {
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

    private function validated(Request $request, ?Offer $offer = null): array
    {
        $type = $request->input('deal_type');

        $validated = $request->validate([
            'service_provider_id' => ['required', 'integer', 'exists:service_providers,id'],
            'deal_type' => ['required', Rule::in(Offer::TYPES)],
            'deal_value' => [
                Rule::requiredIf(in_array($type, [Offer::TYPE_PERCENT, Offer::TYPE_FIXED], true)),
                'nullable', 'numeric', 'min:1',
                $type === Offer::TYPE_PERCENT ? 'max:100' : 'max:100000',
            ],
            'badge_text_en' => [Rule::requiredIf($type === Offer::TYPE_CUSTOM), 'nullable', 'string', 'max:40'],
            'badge_text_ar' => ['nullable', 'string', 'max:40'],
            'badge_text_fr' => ['nullable', 'string', 'max:40'],
            'title_en' => ['required', 'string', 'max:120'],
            'title_ar' => ['nullable', 'string', 'max:120'],
            'title_fr' => ['nullable', 'string', 'max:120'],
            'summary_en' => ['required', 'string', 'max:200'],
            'summary_ar' => ['nullable', 'string', 'max:200'],
            'summary_fr' => ['nullable', 'string', 'max:200'],
            'details_en' => ['nullable', 'string', 'max:5000'],
            'details_ar' => ['nullable', 'string', 'max:5000'],
            'details_fr' => ['nullable', 'string', 'max:5000'],
            'terms_en' => ['nullable', 'string', 'max:3000'],
            'terms_ar' => ['nullable', 'string', 'max:3000'],
            'terms_fr' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::IMAGE_MAX_KB],
            'cta_type' => ['required', Rule::in(Offer::CTAS)],
            // Only http(s): this value becomes a link on the public page.
            'cta_url' => [Rule::requiredIf($request->input('cta_type') === Offer::CTA_URL), 'nullable', 'url:http,https', 'max:500'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'slug' => ['nullable', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('offers', 'slug')->ignore($offer?->id)],
        ], [
            'service_provider_id.required' => 'Choose the provider this offer belongs to.',
            'deal_value.required' => 'Enter the discount value.',
            'badge_text_en.required' => 'Write the badge text in English, e.g. "2 WEEKS FREE".',
            'ends_at.after_or_equal' => 'The end date must be on or after the start date.',
            'cta_url.required' => 'Paste the link the button should open.',
            'slug.regex' => 'Use lowercase letters, numbers and dashes only.',
        ]);

        unset($validated['image'], $validated['slug']);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? ($offer?->sort_order ?: Offer::nextSortOrder()));

        if (! in_array($validated['deal_type'], [Offer::TYPE_PERCENT, Offer::TYPE_FIXED], true)) {
            $validated['deal_value'] = null;
        }

        if ($validated['deal_type'] !== Offer::TYPE_CUSTOM) {
            $validated['badge_text_en'] = $validated['badge_text_ar'] = $validated['badge_text_fr'] = null;
        }

        if ($validated['cta_type'] !== Offer::CTA_URL) {
            $validated['cta_url'] = null;
        }

        return $validated;
    }

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

    /**
     * Keep an existing slug unless the admin typed a new one: shared links
     * must not break just because a title was polished.
     */
    private function slugFor(Request $request, array $data, ?Offer $offer = null): string
    {
        $typed = trim((string) $request->input('slug', ''));

        if ($typed !== '') {
            return $typed;
        }

        if ($offer?->slug) {
            return $offer->slug;
        }

        $provider = ServiceProvider::find($data['service_provider_id']);

        return Offer::uniqueSlug($data['title_en'], $provider?->company_name, $offer?->id);
    }

    /**
     * Resize to a sensible width and re-encode as WebP. Re-encoding also
     * strips anything hidden in the original file. Falls back to storing the
     * upload as-is if the image library cannot read it.
     */
    private function storeImage(UploadedFile $file): string
    {
        try {
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: self::IMAGE_MAX_WIDTH);
            $path = Offer::IMAGE_DIR . '/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($path, (string) $image->toWebp(82));

            return $path;
        } catch (\Throwable $e) {
            Log::warning('Offer image processing failed, storing original', ['error' => $e->getMessage()]);

            return $file->store(Offer::IMAGE_DIR, 'public');
        }
    }

    private function providerOptions()
    {
        return ServiceProvider::query()
            ->with(['user:id,name,is_active', 'location:id,city', 'category'])
            ->whereHas('user')
            ->orderBy('company_name')
            ->get()
            ->map(fn (ServiceProvider $p) => [
                'id' => $p->id,
                'name' => $p->company_name ?: ($p->user?->name ?: 'Provider #' . $p->id),
                'city' => $p->location?->city,
                'category' => $p->category?->name_en ?: $p->category?->name,
                'initials' => mb_strtoupper(mb_substr($p->company_name ?: ($p->user?->name ?: 'S'), 0, 2)),
                'image' => $p->profile_image ? $p->profile_image_url : null,
                'has_whatsapp' => filled(preg_replace('/\D+/', '', (string) ($p->whatsapp_number ?: $p->phone))),
                'visible' => (bool) ($p->is_active && $p->user?->is_active),
            ])
            ->values();
    }
}
