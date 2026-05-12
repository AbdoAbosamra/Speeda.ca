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
     * Get localized column value with proper fallback chain.
     * Fallback: current locale → English → Arabic → French → empty string
     *
     * @param string $column Base column name (e.g., 'title', 'message')
     * @return string Localized value or empty string
     */
    private function getLocalizedColumn(string $column): string
    {
        $locale = app()->getLocale();
        $fallbackChain = [$locale, 'en', 'ar', 'fr'];

        foreach ($fallbackChain as $lang) {
            $column_name = $column . '_' . $lang;
            $value = $this->$column_name ?? null;

            if (!empty(trim((string) $value))) {
                return $value;
            }
        }

        return '';
    }

    /**
     * Get the title based on the current locale.
     * Fallback: current locale → English → Arabic → French → empty string
     */
    public function getTitleAttribute(): string
    {
        return $this->getLocalizedColumn('title');
    }

    /**
     * Get the message based on the current locale.
     * Fallback: current locale → English → Arabic → French → empty string
     */
    public function getMessageAttribute(): string
    {
        return $this->getLocalizedColumn('message');
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
