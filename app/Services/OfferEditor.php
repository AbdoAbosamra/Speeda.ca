<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/** Shared validation, images and provider preview data for both offer editors. */
class OfferEditor
{
    private const IMAGE_MAX_KB = 5120;
    private const IMAGE_MAX_WIDTH = 1600;

    public function validated(Request $request, ?Offer $offer = null): array
    {
        $type = $request->input('deal_type');

        $validated = $request->validate([
            'service_provider_id' => ['required', 'integer', 'exists:service_providers,id'],
            'deal_type' => ['required', Rule::in(Offer::TYPES)],
            'deal_value' => [
                Rule::excludeIf(! in_array($type, [Offer::TYPE_PERCENT, Offer::TYPE_FIXED], true)),
                Rule::requiredIf(in_array($type, [Offer::TYPE_PERCENT, Offer::TYPE_FIXED], true)),
                'nullable', 'numeric', 'min:1',
                $type === Offer::TYPE_PERCENT ? 'max:100' : 'max:100000',
            ],
            'badge_text_en' => [Rule::excludeIf($type !== Offer::TYPE_CUSTOM), Rule::requiredIf($type === Offer::TYPE_CUSTOM), 'nullable', 'string', 'max:40'],
            'badge_text_ar' => [Rule::excludeIf($type !== Offer::TYPE_CUSTOM), 'nullable', 'string', 'max:40'],
            'badge_text_fr' => [Rule::excludeIf($type !== Offer::TYPE_CUSTOM), 'nullable', 'string', 'max:40'],
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

        $validated['cta_url'] = null;

        return $validated;
    }


    public function slugFor(Request $request, array $data, ?Offer $offer = null): string
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


    public function storeImage(UploadedFile $file): string
    {
        $encoded = null;
        try {
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: self::IMAGE_MAX_WIDTH);
            $encoded = (string) $image->toWebp(82);
        } catch (\Throwable $e) {
            Log::warning('Offer image processing failed, storing original', ['error' => $e->getMessage()]);
        }

        try {
            if ($encoded !== null) {
                $path = Offer::IMAGE_DIR . '/' . Str::uuid() . '.webp';
                $stored = Storage::disk('public')->put($path, $encoded);
            } else {
                $path = $file->store(Offer::IMAGE_DIR, 'public');
                $stored = is_string($path) && $path !== '';
            }
            if (! $stored) {
                throw new \RuntimeException('The offer cover could not be written to storage.');
            }
            return $path;
        } catch (\Throwable $e) {
            Log::error('Offer image storage failed', ['error' => $e->getMessage()]);
            throw ValidationException::withMessages(['image' => __('offer_submission.image_upload_failed')]);
        }
    }


    public function providerOptions(?ServiceProvider $provider = null)
    {
        return ServiceProvider::query()
            ->with(['user:id,name,is_active', 'location:id,city', 'category'])
            ->whereHas('user')
            ->when($provider, fn ($q) => $q->whereKey($provider->id))
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
