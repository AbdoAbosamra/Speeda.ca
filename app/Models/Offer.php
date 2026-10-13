<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * An admin-managed deal attributed to a real service provider.
 *
 * Status is never stored: it is derived from the active switch and the two
 * dates, so an offer appears and disappears on schedule without a cron job.
 * The provider's name, logo, city and WhatsApp number always come from the
 * live provider record rather than being copied onto the offer.
 */
class Offer extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';
    public const TYPE_FREE = 'free';
    public const TYPE_CUSTOM = 'custom';
    public const TYPES = [self::TYPE_PERCENT, self::TYPE_FIXED, self::TYPE_FREE, self::TYPE_CUSTOM];

    public const CTA_WHATSAPP = 'whatsapp';
    public const CTA_PROFILE = 'profile';
    public const CTA_URL = 'url';
    public const CTAS = [self::CTA_WHATSAPP, self::CTA_PROFILE, self::CTA_URL];

    public const STATUS_LIVE = 'live';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DRAFT = 'draft';
    public const STATUSES = [self::STATUS_LIVE, self::STATUS_SCHEDULED, self::STATUS_EXPIRED, self::STATUS_DRAFT];

    /** Where cover images live on the public disk. */
    public const IMAGE_DIR = 'offers';

    /** An offer ending within this many days gets the "ending soon" treatment. */
    public const ENDING_SOON_DAYS = 7;

    protected $fillable = [
        'service_provider_id',
        'slug',
        'deal_type',
        'deal_value',
        'badge_text_en', 'badge_text_ar', 'badge_text_fr',
        'title_en', 'title_ar', 'title_fr',
        'summary_en', 'summary_ar', 'summary_fr',
        'details_en', 'details_ar', 'details_fr',
        'terms_en', 'terms_ar', 'terms_fr',
        'image_path',
        'cta_type',
        'cta_url',
        'starts_at',
        'ends_at',
        'is_active',
        'is_featured',
        'sort_order',
        'created_by',
        'updated_by',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'deal_value' => 'float',
        'verified_at' => 'datetime',
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'views_count' => 'integer',
        'claims_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
        'favorites_count' => 'integer',
    ];

    protected static function booted(): void
    {
        $invalidate = fn () => \Illuminate\Support\Facades\Cache::forget('admin_dash_offers');
        static::saved($invalidate);
        static::deleted($invalidate);
        static::restored($invalidate);
    }

    /* =====================================================================
     |  RELATIONS
     * ===================================================================== */

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OfferEvent::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(\App\Models\OfferLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(OfferComment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(OfferComment::class)->where('status', OfferComment::STATUS_APPROVED)->latest();
    }

    public function favoritedBy(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'offer_favorites')->withPivot('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /* =====================================================================
     |  QUERIES
     * ===================================================================== */

    /**
     * Offers a visitor may see right now: switched on, inside their dates,
     * and owned by a provider whose profile is itself public.
     */
    public function scopeLive(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        return $query
            ->where('offers.is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today))
            ->whereHas('serviceProvider', fn ($q) => $q->publiclyVisible());
    }

    /**
     * Filter by derived status, mirroring status() exactly.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        $today = Carbon::today()->toDateString();

        return match ($status) {
            self::STATUS_DRAFT => $query->where('is_active', false),
            self::STATUS_SCHEDULED => $query->where('is_active', true)->whereDate('starts_at', '>', $today),
            self::STATUS_EXPIRED => $query->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
                ->whereDate('ends_at', '<', $today),
            self::STATUS_LIVE => $query->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today)),
            default => $query,
        };
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    /**
     * Relations needed to render a card without N+1 queries.
     */
    public function scopeWithCardRelations(Builder $query): Builder
    {
        return $query->with([
            'serviceProvider',
            'serviceProvider.user:id,name,is_active',
            'serviceProvider.category',
            'serviceProvider.location',
        ]);
    }

    /**
     * What the home section renders, or an empty collection to render nothing.
     */
    public static function forHomePage(?OfferSection $section = null): Collection
    {
        $section ??= OfferSection::current();

        if (! $section->is_active) {
            return new Collection();
        }

        return static::live()
            ->where('is_featured', true)
            ->withCardRelations()
            ->ordered()
            ->take(max(1, (int) $section->max_items))
            ->get();
    }

    public static function nextSortOrder(): int
    {
        return (int) static::withTrashed()->max('sort_order') + 1;
    }

    /**
     * A URL slug built from the English title and the provider name, made
     * unique against every offer including deleted ones (their links may
     * still be shared somewhere).
     */
    public static function uniqueSlug(string $title, ?string $providerName = null, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim(($providerName ? $providerName . ' ' : '') . $title)) ?: 'offer';
        $base = Str::limit($base, 140, '');
        $slug = $base;
        $i = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /* =====================================================================
     |  STATUS
     * ===================================================================== */

    public function status(): string
    {
        if (! $this->is_active) {
            return self::STATUS_DRAFT;
        }

        $today = Carbon::today();

        if ($this->starts_at && $this->starts_at->gt($today)) {
            return self::STATUS_SCHEDULED;
        }

        if ($this->ends_at && $this->ends_at->lt($today)) {
            return self::STATUS_EXPIRED;
        }

        return self::STATUS_LIVE;
    }

    public function isLive(): bool
    {
        return $this->status() === self::STATUS_LIVE
            && $this->serviceProvider?->is_active
            && ($this->serviceProvider?->user?->is_active ?? false);
    }

    /**
     * Whole days left including today; null when the offer has no end date.
     */
    public function daysLeft(): ?int
    {
        if (! $this->ends_at) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($this->ends_at, false);
    }

    public function isEndingSoon(): bool
    {
        $days = $this->daysLeft();

        return $days !== null && $days >= 0 && $days <= self::ENDING_SOON_DAYS;
    }

    /**
     * The exact moment the offer stops, for the countdown.
     */
    public function endsAtMoment(): ?Carbon
    {
        return $this->ends_at?->copy()->endOfDay();
    }

    /* =====================================================================
     |  DISPLAY
     * ===================================================================== */

    public function getLocalizedTitleAttribute(): string
    {
        return (string) $this->localizedValue('title');
    }

    public function getLocalizedSummaryAttribute(): ?string
    {
        return $this->localizedValue('summary');
    }

    public function getLocalizedDetailsAttribute(): ?string
    {
        return $this->localizedValue('details');
    }

    /**
     * Terms are written one per line in the admin form and shown as a list.
     *
     * @return array<int, string>
     */
    public function termsLines(): array
    {
        $raw = (string) $this->localizedValue('terms');

        return array_values(array_filter(array_map(
            fn ($line) => trim(ltrim(trim($line), "-•*·")),
            preg_split('/\R/u', $raw) ?: []
        ), fn ($line) => $line !== ''));
    }

    /**
     * The badge on the card: "10% OFF", "$30 OFF", "FREE" or the admin's text,
     * in the current locale.
     */
    public function badgeLabel(?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $value = $this->formattedValue();

        return match ($this->deal_type) {
            self::TYPE_PERCENT => __('offers.badge_percent', ['value' => $value], $locale),
            self::TYPE_FIXED => __('offers.badge_fixed', ['value' => $value], $locale),
            self::TYPE_FREE => __('offers.badge_free', [], $locale),
            default => $this->localizedValue('badge_text', $locale) ?: __('offers.badge_special', [], $locale),
        };
    }

    /**
     * A date in the visitor's language and word order: "Oct 13, 2026" in
     * English, "13 أكتوبر 2026" in Arabic, "13 oct. 2026" in French.
     */
    public static function localDate(?Carbon $date, bool $withYear = true): string
    {
        if (! $date) {
            return '';
        }

        $locale = app()->getLocale();
        $format = match ($locale) {
            'ar' => $withYear ? 'j F Y' : 'j F',
            'en' => $withYear ? 'M j, Y' : 'M j',
            default => $withYear ? 'j M Y' : 'j M',
        };

        return $date->copy()->locale($locale)->translatedFormat($format);
    }

    /**
     * 10 rather than 10.00, 12.5 kept as is.
     */
    public function formattedValue(): string
    {
        $v = (float) $this->deal_value;

        return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
    }

    /**
     * Used for "biggest discount" sorting: percent and dollar values are not
     * comparable, so free and custom deals sit in the middle of the range.
     */
    public function dealWeight(): float
    {
        return match ($this->deal_type) {
            self::TYPE_PERCENT => (float) $this->deal_value,
            self::TYPE_FIXED => (float) $this->deal_value / 2,
            self::TYPE_FREE => 50,
            default => 25,
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    /**
     * Stable decorative gradient for offers without a cover image.
     */
    public function fallbackGradient(): string
    {
        $palettes = [
            ['#60a5fa', '#1d4ed8'], ['#5eead4', '#0f766e'], ['#93c5fd', '#3730a3'],
            ['#f9a8d4', '#be185d'], ['#fcd34d', '#c2410c'], ['#c4b5fd', '#6d28d9'],
            ['#86efac', '#15803d'], ['#7dd3fc', '#0369a1'],
        ];
        [$a, $b] = $palettes[$this->service_provider_id % count($palettes)];

        return "linear-gradient(135deg, {$a}, {$b})";
    }

    /**
     * Font Awesome class for the provider's category, shown on covers
     * without an image. Only a plain "fas fa-name" value is trusted.
     */
    public function fallbackIcon(): string
    {
        $icon = (string) $this->serviceProvider?->category?->icon;

        return preg_match('/^fa[srb]? fa-[a-z0-9-]+$/', $icon) ? $icon : 'fas fa-tag';
    }

    public function providerName(): string
    {
        $p = $this->serviceProvider;

        return $p?->localized_company_name ?: ($p?->company_name ?: ($p?->user?->name ?: __('offers.provider')));
    }

    public function providerCategory(): ?string
    {
        $category = $this->serviceProvider?->category;

        return $category?->translated_name ?: $category?->name;
    }

    public function providerCity(): ?string
    {
        $location = $this->serviceProvider?->location;

        return $location?->localized_name ?: $location?->city;
    }

    public function providerInitials(): string
    {
        $words = preg_split('/\s+/u', trim($this->providerName())) ?: [];
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials ?: 'S';
    }

    /**
     * Digits-only WhatsApp number, or null when the provider has none.
     */
    public function providerWhatsappDigits(): ?string
    {
        return $this->serviceProvider?->whatsappDigits();
    }

    /**
     * The button opens WhatsApp when asked to AND the provider has a number;
     * otherwise it falls back to the provider profile.
     */
    public function effectiveCta(): string
    {
        if ($this->cta_type === self::CTA_URL && filled($this->cta_url)) {
            return self::CTA_URL;
        }

        if ($this->cta_type === self::CTA_WHATSAPP && $this->providerWhatsappDigits()) {
            return self::CTA_WHATSAPP;
        }

        return self::CTA_PROFILE;
    }

    /* =====================================================================
     |  LOCALISATION
     * ===================================================================== */

    /**
     * Current locale first, then English, Arabic, French — so a field the
     * admin only wrote in English still renders for every visitor.
     */
    public function localizedValue(string $base, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $order = array_unique(array_merge(
            in_array($locale, ['en', 'ar', 'fr'], true) ? [$locale] : [],
            ['en', 'ar', 'fr']
        ));

        foreach ($order as $code) {
            $value = $this->getAttribute("{$base}_{$code}");

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }

    /* =====================================================================
     |  FILES
     * ===================================================================== */

    public function deleteImageFile(): void
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            Storage::disk('public')->delete($this->image_path);
        }
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
