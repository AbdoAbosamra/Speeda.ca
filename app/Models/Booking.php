<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'service_provider_profile_id', // legacy, will be phased out
        'service_provider_id',
        'client_id',
        'booking_reference',
        'status',
        'service_description',
        'client_requirements',
        'estimated_cost',
        'final_cost',
        'preferred_date',
        'confirmed_date',
        'completed_date',
        'service_address',
        'client_phone',
        'special_instructions',
        'payment_status',
        'payment_method',
    ];

    // Relations
    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function serviceProviderProfile()
    {
        return $this->belongsTo(ServiceProviderProfile::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
