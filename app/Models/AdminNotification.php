<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_ar',
        'title_en',
        'title_fr',
        'message_ar',
        'message_en',
        'message_fr',
        'target_type',
        'created_by',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the admin who created the notification.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active (not expired) notifications.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Get the title based on the current locale.
     */
    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        return $this->{"title_{$locale}"} ?? $this->title_en;
    }

    /**
     * Get the message based on the current locale.
     */
    public function getMessageAttribute()
    {
        $locale = app()->getLocale();
        return $this->{"message_{$locale}"} ?? $this->message_en;
    }

    /**
     * Users who have read this notification.
     */
    public function readByUsers()
    {
        return $this->belongsToMany(User::class, 'admin_notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }
}
