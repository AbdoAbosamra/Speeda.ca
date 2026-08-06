<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Admin-managed testimonial about Speeda, attributed to a real service provider.
 *
 * The admin picks the provider from a dropdown rather than retyping a name, so
 * the displayed name, photo and city always come from the live provider record.
 * The legacy free-text columns remain as a fallback for rows created before the
 * provider link existed.
 *
 * Rendered as cards on the home page — the site shows exactly DISPLAY_COUNT
 * active testimonials or hides the section entirely.
 */
class SiteTestimonial extends Model
{
    use HasFactory;

    /** Number of active testimonials required for the home section to show. */
    public const DISPLAY_COUNT = 3;

    protected $fillable = [
        'service_provider_id',
        'provider_name',
        'provider_title',
        'rating',
        'testimonial_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'service_provider_id' => 'integer',
        'rating' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /* =====================================================================
     |  DISPLAY ATTRIBUTES
     |  Prefer the linked provider, fall back to the stored free text.
     * ===================================================================== */

    public function getDisplayNameAttribute(): string
    {
        $provider = $this->serviceProvider;

        return $provider?->company_name
            ?: $provider?->user?->name
            ?: ($this->provider_name ?: '—');
    }

    /**
     * Secondary line: the stored title, else the provider's category.
     */
    public function getDisplayTitleAttribute(): ?string
    {
        if ($this->provider_title) {
            return $this->provider_title;
        }

        return $this->serviceProvider?->category?->localized_name ?: null;
    }

    public function getDisplayCityAttribute(): ?string
    {
        return $this->serviceProvider?->location?->city;
    }

    /**
     * Provider photo URL, or null when there is no linked provider.
     * ServiceProvider::profile_image_url already falls back to a placeholder.
     */
    public function getDisplayPhotoAttribute(): ?string
    {
        return $this->serviceProvider?->profile_image_url;
    }

    /**
     * Initial used when no photo is available.
     */
    public function getDisplayInitialAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->display_name, 0, 1)) ?: '?';
    }

    /* =====================================================================
     |  QUERIES
     * ===================================================================== */

    /**
     * Relations needed to render a testimonial card without N+1 queries.
     */
    public function scopeWithDisplayRelations($query)
    {
        return $query->with([
            'serviceProvider',
            'serviceProvider.user:id,name',
            'serviceProvider.category',
            'serviceProvider.location:id,city',
        ]);
    }

    /**
     * Scope to only active testimonials, in display order.
     */
    public function scopeActiveOrdered($query)
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * The active testimonials to display on the home page, or an empty
     * collection unless exactly DISPLAY_COUNT are active.
     */
    public static function forHomePage()
    {
        $active = static::activeOrdered()
            ->withDisplayRelations()
            ->take(self::DISPLAY_COUNT)
            ->get();

        return $active->count() === self::DISPLAY_COUNT ? $active : collect();
    }

    /**
     * Next free position, so a new testimonial lands at the end of the list.
     */
    public static function nextSortOrder(): int
    {
        return (int) static::max('sort_order') + 1;
    }
}
