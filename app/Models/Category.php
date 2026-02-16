<?php

// app/Models/Category.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'name_en',
        'name_fr',
        'slug',
        'description',
        'description_ar',
        'description_en',
        'description_fr',
        'icon',
        'color',
        'is_section',
        'parent_id',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
        'metadata',
    ];

    protected $casts = [
        'is_section' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Attributes that should be appended to the model's array/JSON representation
     * These ensure accessors are always called when accessing the model
     */
    protected $appends = [
        'localized_name',
        'localized_description',
        'translated_name',
        'translated_description',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function allChildren()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function serviceProviders()
    {
        return $this->hasMany(ServiceProvider::class);
    }

    public function activeServiceProviders()
    {
        return $this->hasMany(ServiceProvider::class)->where('is_verified', true);
    }

    // Scopes
    public function scopeSections($query)
    {
        return $query->where('is_section', true)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function scopeSubcategories($query)
    {
        return $query->where('is_section', false)
            ->whereNotNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySection($query, $sectionId)
    {
        return $query->where('parent_id', $sectionId)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->whereHas('serviceProviders')
            ->withCount('serviceProviders')
            ->orderBy('service_providers_count', 'desc')
            ->limit($limit);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }

    // Methods

    /**
     * Get localized name based on current locale
     * Fallback: locale-specific → English → default name
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();

        // Try locale-specific column first (e.g., name_ar for Arabic)
        $field = 'name_'.$locale;
        if (! empty($this->$field)) {
            return $this->$field;
        }

        // Fallback: Try English
        if (! empty($this->name_en)) {
            return $this->name_en;
        }

        // Last resort: Original name column
        return $this->name ?? '';
    }

    /**
     * Get localized description based on current locale.
     *
     * Strategy:
     * 1. For non-English locales (ar, fr, etc):
     *    - ALWAYS use template generation to ensure pure language (never English fallback)
     * 2. For English locale:
     *    - Use database column if populated
     *    - Otherwise generate from template
     *
     * This ensures NO MIXED LANGUAGE rendering in any locale.
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        $localizedName = $this->localized_name;
        $cities = $this->getCitiesForLocale($locale);
        $template = __('categories.description_template', [], $locale);

        // For non-English locales, ALWAYS generate from template
        // This prevents any English text from appearing in Arabic/French mode
        if ($locale !== 'en') {
            return str_replace(
                [':category', ':cities'],
                [$localizedName, $cities],
                $template
            );
        }

        // For English: try database first, then template
        if (! empty($this->description_en)) {
            return $this->description_en;
        }

        return str_replace(
            [':category', ':cities'],
            [$localizedName, $cities],
            $template
        );
    }

    /**
     * Get cities string in the specified locale
     *
     * Returns properly localized city names for use in description templates
     */
    private function getCitiesForLocale(string $locale): string
    {
        $cities = [
            'en' => 'Laval, Montreal, Ottawa, Gatineau',
            'ar' => 'لافال، مونتريال، أوتاوا، غاتينو',
            'fr' => 'Laval, Montréal, Ottawa, Gatineau',
        ];

        return $cities[$locale] ?? $cities['en'];
    }

    /**
     * Get translated category name based on current locale (legacy method)
     */
    public function getTranslatedNameAttribute(): string
    {
        return $this->localized_name;
    }

    /**
     * Get translated category description based on current locale (legacy method)
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        return $this->localized_description;
    }

    public function isSection(): bool
    {
        return $this->is_section && is_null($this->parent_id);
    }

    public function isSubcategory(): bool
    {
        return ! $this->is_section && ! is_null($this->parent_id);
    }

    public function getServiceProvidersCount(): int
    {
        return $this->serviceProviders()->count();
    }

    public function getActiveServiceProvidersCount(): int
    {
        return $this->activeServiceProviders()->count();
    }

    public function getFullPath(): string
    {
        if ($this->isSection()) {
            return $this->name;
        }

        return $this->parent ? $this->parent->name.' → '.$this->name : $this->name;
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];

        if ($this->isSubcategory() && $this->parent) {
            $breadcrumbs[] = [
                'name' => $this->parent->name,
                'url' => route('categories.show', $this->parent),
            ];
        }

        $breadcrumbs[] = [
            'name' => $this->name,
            'url' => route('categories.show', $this),
        ];

        return $breadcrumbs;
    }

    public function getIconHtml($size = 'fa-lg'): string
    {
        return '<i class="'.$this->icon.' '.$size.'" style="color: '.$this->color.'"></i>';
    }

    // Automatic slug generation
    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }

            // Ensure unique slug
            $originalSlug = $category->slug;
            $counter = 1;
            while (static::where('slug', $category->slug)->exists()) {
                $category->slug = $originalSlug.'-'.$counter++;
            }
        });

        static::updating(function ($category) {
            // If name changed and no custom slug set, update slug
            if ($category->isDirty('name') && $category->getOriginal('slug') === Str::slug($category->getOriginal('name'))) {
                $category->slug = Str::slug($category->name);
            }

            // Ensure unique slug
            $originalSlug = $category->slug;
            $counter = 1;
            while (static::where('slug', $category->slug)->where('id', '!=', $category->id)->exists()) {
                $category->slug = $originalSlug.'-'.$counter++;
            }
        });
    }
}
