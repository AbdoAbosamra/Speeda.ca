<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The single settings row for the home page "Local Offers" section.
 *
 * current() creates it on first access, so the admin screen never deals with
 * "no record yet". Empty titles fall back to the translated defaults.
 */
class OfferSection extends Model
{
    public const MAX_ITEMS_OPTIONS = [4, 6, 8, 12];

    protected $fillable = [
        'is_active',
        'title_en', 'title_ar', 'title_fr',
        'subtitle_en', 'subtitle_ar', 'subtitle_fr',
        'max_items',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_items' => 'integer',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_active' => true,
            'max_items' => 8,
        ]);
    }

    public function displayTitle(): string
    {
        return $this->localizedValue('title') ?: __('offers.section_title');
    }

    public function displaySubtitle(): string
    {
        return $this->localizedValue('subtitle') ?: __('offers.section_subtitle');
    }

    /**
     * Unlike offers, the fallback is the translated default rather than
     * another language: the default exists in every language.
     */
    protected function localizedValue(string $base): ?string
    {
        $value = $this->getAttribute($base . '_' . app()->getLocale());

        return is_string($value) && trim($value) !== '' ? $value : null;
    }
}
