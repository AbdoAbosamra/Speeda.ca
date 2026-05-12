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
        return $this->hasMany(ServiceProvider::class);
    }

    public function grandchildren()
    {
        return $this->hasManyThrough(
            Category::class,
            Category::class,
            'parent_id',
            'parent_id',
            'id',
            'id'
        )->where('categories.is_active', true)->orderBy('categories.sort_order');
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

    public function scopeFilterGroups($query)
    {
        return $query->where('is_active', true)
            ->where('is_section', false)
            ->whereHas('parent', function ($parentQuery) {
                $parentQuery->whereNull('parent_id')
                    ->where('is_section', true)
                    ->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    public function scopeTerminal($query)
    {
        return $query->where('is_active', true)
            ->where('is_section', false)
            ->whereDoesntHave('allChildren');
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
                ->orWhere('name_ar', 'like', "%{$searchTerm}%")
                ->orWhere('name_en', 'like', "%{$searchTerm}%")
                ->orWhere('name_fr', 'like', "%{$searchTerm}%");
        });
    }

    // Methods

    /**
     * Get localized column value with proper fallback chain.
     * Fallback: current locale → English → Arabic → French → empty string
     *
     * @param string $column Base column name (e.g., 'name', 'description')
     * @return string Localized value or empty string
     */
    private function getLocalizedColumn(string $column): string
    {
        $locale = app()->getLocale();
        
        // 1. Try the requested locale's specific column
        $localeColumn = in_array($locale, ['en', 'ar', 'fr']) ? $column . '_' . $locale : $column;
        $value = $this->$localeColumn ?? null;
        if (!empty(trim((string) $value))) {
            return $value;
        }

        // 2. Try Laravel translation files as fallback (using multiple key variations)
        if ($column === 'name' && !empty($this->slug)) {
            $slugKey = str_replace('-', '_', $this->slug);
            $possibleKeys = [
                $this->slug,
                $slugKey,
                $slugKey . '_services',
                $slugKey . '_cat',
                str_replace('_services', '', $slugKey), // reversed
            ];

            foreach ($possibleKeys as $key) {
                $translationKey = 'categories.' . $key;
                $translated = __($translationKey);
                if ($translated !== $translationKey && !empty($translated)) {
                    return $translated;
                }
            }
        }

        // 3. Try the base column (often contains the primary/English text)
        $baseValue = $this->$column ?? null;
        if (!empty(trim((string) $baseValue))) {
            return $baseValue;
        }

        // 4. Try other languages as fallbacks
        $fallbackChain = ['en', 'ar', 'fr'];
        foreach ($fallbackChain as $lang) {
            if ($lang === $locale) continue;
            
            $fallbackColumn = $column . '_' . $lang;
            $fallbackValue = $this->$fallbackColumn ?? null;
            if (!empty(trim((string) $fallbackValue))) {
                return $fallbackValue;
            }
        }

        return '';
    }

    /**
     * Get localized name based on current locale.
     * Fallback: current locale → English → Arabic → French → base name → empty string
     */
    public function getLocalizedNameAttribute(): string
    {
        return $this->getLocalizedColumn('name');
    }

    /**
     * Get localized description based on current locale.
     * Fallback: current locale → English → Arabic → French → base description → empty string
     */
    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->getLocalizedColumn('description');
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

    public function isLeaf(): bool
    {
        return ! $this->is_section && ! $this->allChildren()->exists();
    }

    public function isFilterGroup(): bool
    {
        return ! $this->is_section
            && $this->parent_id !== null
            && $this->parent?->isSection();
    }

    public function getAnchorIdAttribute(): string
    {
        return Str::slug($this->name_en ?: $this->name ?: (string) $this->id);
    }

    public function getHierarchyLabelsAttribute(): array
    {
        $labels = [];
        $current = $this;

        while ($current) {
            array_unshift($labels, $current->translated_name);
            $current = $current->parent;
        }

        return $labels;
    }

    public function descendantAndSelfIds(): array
    {
        $allIds = [$this->id];
        $frontier = [$this->id];

        while (! empty($frontier)) {
            $children = static::query()
                ->where('is_active', true)
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            if (empty($children)) {
                break;
            }

            $allIds = array_merge($allIds, $children);
            $frontier = $children;
        }

        return array_values(array_unique($allIds));
    }

    public function providerCategoryIds(): array
    {
        $allIds = $this->descendantAndSelfIds();

        $nonLeafParentIds = static::query()
            ->where('is_active', true)
            ->whereIn('parent_id', $allIds)
            ->pluck('parent_id')
            ->filter()
            ->unique()
            ->all();

        $leafIds = array_values(array_diff($allIds, $nonLeafParentIds));

        return ! empty($leafIds) ? $leafIds : [$this->id];
    }

    public static function resolveFilterValue(string|int|null $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return static::query()
            ->where('is_active', true)
            ->when(is_numeric($value), function ($query) use ($value) {
                $query->where('id', (int) $value);
            }, function ($query) use ($value) {
                $query->where('slug', (string) $value);
            })
            ->first();
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
