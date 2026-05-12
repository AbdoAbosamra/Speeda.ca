<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory;

    protected $fillable = [
        'city',
        'country',
        'area',
        'latitude',
        'longitude',
        'image',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    protected $appends = [
        'translated_name',
        'localized_name',
    ];

    public function getTranslatedNameAttribute(): string
    {
        return $this->getLocalizedColumn('city');
    }

    public function getLocalizedNameAttribute(): string
    {
        return $this->getLocalizedColumn('city');
    }

    public function getLocalizedColumn(string $column): string
    {
        $locale = app()->getLocale();
        
        // 1. Try the requested locale's specific column (if it exists in DB later)
        $localeColumn = $column . '_' . $locale;
        if (isset($this->attributes[$localeColumn]) && !empty(trim((string)$this->attributes[$localeColumn]))) {
            return $this->attributes[$localeColumn];
        }

        // 2. Try Laravel translation files as fallback (cities.php or location.php)
        if ($column === 'city' && !empty($this->city)) {
            $key = strtolower(trim($this->city));
            
            // Try cities.php
            $translationKey = 'cities.' . $key;
            $translated = __($translationKey);
            if ($translated !== $translationKey && !empty($translated)) {
                return $translated;
            }

            // Try location.php
            $translationKey = 'location.' . $key;
            $translated = __($translationKey);
            if ($translated !== $translationKey && !empty($translated)) {
                return $translated;
            }
        }

        // 3. Try the base column
        return $this->$column ?? '';
    }

    // Backwards-compatible accessor: many views expect a `name` property
    // on locations. Map `name` to the `city` column so templates can
    // continue using `$location->name` without changes.
    public function getNameAttribute()
    {
        return $this->city;
    }

    /**
     * Get all service providers for this location
     */
    public function serviceProviders()
    {
        return $this->hasMany(ServiceProvider::class);
    }

    /**
     * Get categories associated with this location
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'location_category');
    }

    /**
     * Scope for active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope for searching locations
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('city', 'LIKE', "%{$search}%")
                    ->orWhere('country', 'LIKE', "%{$search}%")
                    ->orWhere('area', 'LIKE', "%{$search}%");
    }

    /**
     * Get active service providers for this location
     */
    public function activeServiceProviders()
    {
        return $this->serviceProviders()
                    ->where('is_active', true);
    }
}
