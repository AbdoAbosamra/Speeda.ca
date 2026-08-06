<?php

namespace App\Models;

use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profession',
        'role', // allow setting role during registration
        'is_active', // user account status
        'client_welcome_email_sent_at', // send-once guard: client welcome email
        'first_review_email_sent_at', // send-once guard: 1st approved review email
        'fifth_review_email_sent_at', // send-once guard: 5th approved review email
        'login_count', // number of successful logins
        'last_login_at', // timestamp of the most recent login
        'last_login_ip', // IP of the most recent login
        'last_seen_at', // presence heartbeat (updated on activity)
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
            'is_active' => 'boolean',
            'client_welcome_email_sent_at' => 'datetime',
            'first_review_email_sent_at' => 'datetime',
            'fifth_review_email_sent_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'login_count' => 'integer',
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

    /**
     * Admin notifications read by the user.
     */
    public function readAdminNotifications()
    {
        return $this->belongsToMany(AdminNotification::class, 'admin_notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    /**
     * Reviews written by this user as a client.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'client_id');
    }

    /**
     * Comments posted by this user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    /**
     * Send a branded password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
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
        // Consider user admin if role is 'admin' OR email is in admins config list
        if ($this->role === 'admin') {
            return true;
        }

        $admins = config('auth.admins', []);
        if ($this->email && in_array($this->email, $admins, true)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true; // Default to true if column doesn't exist
    }

    /** Minutes of inactivity after which a user is considered offline. */
    public const ONLINE_THRESHOLD_MINUTES = 5;

    /**
     * Whether the user is currently considered online (recent heartbeat).
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->gt(now()->subMinutes(self::ONLINE_THRESHOLD_MINUTES));
    }

    /**
     * Scope to users seen within the online threshold.
     */
    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>', now()->subMinutes(self::ONLINE_THRESHOLD_MINUTES));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // Legacy cleanup: hasServiceProviderProfile() method removed
    // Use serviceProvider()->exists() instead
}
