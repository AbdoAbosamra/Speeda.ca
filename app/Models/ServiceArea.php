<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    use HasFactory;

    protected $table = 'service_areas';

    protected $fillable = [
        'service_provider_id',
        'location_id',
        'radius_km',
        'extra_charge',
        'estimated_travel_time',
        'is_active',
    ];

    protected $casts = [
        'radius_km' => 'integer',
        'extra_charge' => 'decimal:2',
        'estimated_travel_time' => 'integer',
        'is_active' => 'boolean',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
