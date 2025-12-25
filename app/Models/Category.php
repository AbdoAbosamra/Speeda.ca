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
        'slug',
        'description',
        'icon',
        'color',
        'is_section',
        'parent_id',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
        'metadata'
    ];

    protected $casts = [
        'is_section' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array'
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
        return $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%");
        });
    }

    // Methods

    /**
     * Get translated category name based on current locale
     */
    public function getTranslatedNameAttribute(): string
    {
        // Convert category name to translation key format
        // Example: "Car Mechanics" -> "car_mechanics"
        // Example: "Cars Inspections (Safety) for Uber" -> "cars_inspections_safety_for_uber"
        // Example: "Accounting & Bookkeeping + Tax Preparation" -> "accounting_bookkeeping_tax_preparation"
        $translationKey = strtolower($this->name);

        // Remove parentheses and their content
        $translationKey = preg_replace('/\([^)]+\)/', '', $translationKey);

        // Replace spaces, slashes, ampersands, plus signs, and hyphens with underscores
        $translationKey = str_replace([' ', '/', '&', '+', '-', '(', ')'], ['_', '_', '_', '_', '_', '', ''], $translationKey);

        // Remove multiple underscores
        $translationKey = preg_replace('/_+/', '_', $translationKey);

        // Trim underscores from start and end
        $translationKey = trim($translationKey, '_');

        // Try to get translation from categories file
        $translated = __('categories.' . $translationKey);

        // If translation not found (returns the key itself), return original name
        if ($translated === 'categories.' . $translationKey) {
            return $this->name;
        }

        return $translated;
    }

    /**
     * Get translated category description based on current locale
     */
    public function getTranslatedDescriptionAttribute(): string
    {
        // Convert category name to translation key format
        // Example: "Car Mechanics" -> "car_mechanics_desc"
        // Example: "Cars Inspections (Safety) for Uber" -> "cars_inspections_safety_for_uber_desc"
        // Example: "Accounting & Bookkeeping + Tax Preparation" -> "accounting_bookkeeping_tax_preparation_desc"
        $translationKey = strtolower($this->name);

        // Remove parentheses and their content
        $translationKey = preg_replace('/\([^)]+\)/', '', $translationKey);

        // Replace spaces, slashes, ampersands, plus signs, and hyphens with underscores
        $translationKey = str_replace([' ', '/', '&', '+', '-', '(', ')'], ['_', '_', '_', '_', '_', '', ''], $translationKey);

        // Remove multiple underscores
        $translationKey = preg_replace('/_+/', '_', $translationKey);

        // Trim underscores and add _desc suffix
        $translationKey = trim($translationKey, '_') . '_desc';

        // Try to get translation from categories file
        $translated = __('categories.' . $translationKey);

        // If translation not found (returns the key itself), return original description
        if ($translated === 'categories.' . $translationKey) {
            return $this->description ?? '';
        }

        return $translated;
    }

    public function isSection(): bool
    {
        return $this->is_section && is_null($this->parent_id);
    }

    public function isSubcategory(): bool
    {
        return !$this->is_section && !is_null($this->parent_id);
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

        return $this->parent ? $this->parent->name . ' → ' . $this->name : $this->name;
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];

        if ($this->isSubcategory() && $this->parent) {
            $breadcrumbs[] = [
                'name' => $this->parent->name,
                'url' => route('categories.show', $this->parent)
            ];
        }

        $breadcrumbs[] = [
            'name' => $this->name,
            'url' => route('categories.show', $this)
        ];

        return $breadcrumbs;
    }

    public function getIconHtml($size = 'fa-lg'): string
    {
        return '<i class="' . $this->icon . ' ' . $size . '" style="color: ' . $this->color . '"></i>';
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
                $category->slug = $originalSlug . '-' . $counter++;
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
                $category->slug = $originalSlug . '-' . $counter++;
            }
        });
    }
}
