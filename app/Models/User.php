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

    // Legacy cleanup: ServiceProviderProfile relationship removed
    // Profile is now integrated into ServiceProvider model

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

    /**
     * Endorsements made by this user.
     */
    public function endorsements()
    {
        return $this->hasMany(Endorsement::class);
    }

    // Role management
    public function assignRole(string $role): void
    {
        if (!in_array($role, ['client', 'service_provider', 'admin'])) {
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

    public function isAdmin(): bool
    {
        // Consider user admin if role is 'admin' OR email is in ADMINS env list (comma separated)
        if ($this->role === 'admin') {
            return true;
        }

        $admins = array_filter(array_map('trim', explode(',', env('ADMINS', env('ADMIN_EMAIL', '')))));
        if ($this->email && in_array($this->email, $admins, true)) {
            return true;
        }

        return false;
    }

    // Legacy cleanup: hasServiceProviderProfile() method removed
    // Use serviceProvider()->exists() instead
}
