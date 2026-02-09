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
                    ->where('is_verified', true)
                    ->where('is_active', true);
    }
}
