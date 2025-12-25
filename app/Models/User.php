<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profession',
        'role', // allow setting role during registration
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships (without withDefault)
    public function serviceProviderProfile()
    {
        return $this->hasOne(ServiceProviderProfile::class);
    }

    /**
     * A user may have one service provider record (if they registered as a provider).
     */
    public function serviceProvider()
    {
        return $this->hasOne(ServiceProvider::class, 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    /**
     * Providers saved/bookmarked by the user.
     * Uses a pivot table `saved_providers` (user_id, service_provider_id).
     */
    public function savedProviders()
    {
        return $this->belongsToMany(ServiceProvider::class, 'saved_providers', 'user_id', 'service_provider_id')
            ->withTimestamps();
    }

    // Role management
    public function assignRole(string $role): void
    {
        if (!in_array($role, ['client', 'service_provider'])) {
            throw new \InvalidArgumentException("Invalid role: {$role}");
        }

        $this->forceFill(['role' => $role]);
        $this->save();
    }

    public function isServiceProvider(): bool
    {
        return $this->role === 'service_provider';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function hasServiceProviderProfile(): bool
    {
        return $this->serviceProviderProfile()->exists();
    }
}
