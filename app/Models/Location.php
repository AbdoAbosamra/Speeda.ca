<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory;
    protected $fillable = [
        'city',
    ];

    // Backwards-compatible accessor: many views expect a `name` property
    // on locations. Map `name` to the `city` enum column so templates can
    // continue using `$location->name` without changes.
    public function getNameAttribute()
    {
        return $this->city;
    }
    public function serviceProviders(){
        return $this->hasMany(ServiceProvider::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class, 'location_category');
    }
    public function scopeActive($query)
{
    return $query->where('is_active', 1);
}

}
