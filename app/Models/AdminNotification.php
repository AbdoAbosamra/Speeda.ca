<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class AdminNotification extends Model
{
    use HasFactory;

    private static ?bool $providerTargetingTableExists = null;

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
     * Service providers selected to receive this notification.
     * Empty target set means broadcast to all service providers.
     */
    public function targetServiceProviders()
    {
        return $this->belongsToMany(ServiceProvider::class, 'admin_notification_service_provider')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active (not expired) notifications.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope notifications visible to a specific provider user.
     */
    public function scopeVisibleToUser(Builder $query, ?User $user): Builder
    {
        $serviceProviderId = $user?->serviceProvider?->id;

        if (!$serviceProviderId) {
            return $query->whereRaw('1 = 0');
        }

        if (!self::supportsProviderTargeting()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($serviceProviderId) {
            $q->whereDoesntHave('targetServiceProviders')
                ->orWhereHas('targetServiceProviders', function (Builder $targetQuery) use ($serviceProviderId) {
                    $targetQuery->where('service_providers.id', $serviceProviderId);
                });
        });
    }

    /**
     * A notification with no selected providers is a broadcast.
     */
    public function isBroadcast(): bool
    {
        if (!self::supportsProviderTargeting()) {
            return true;
        }

        if ($this->relationLoaded('targetServiceProviders')) {
            return $this->targetServiceProviders->isEmpty();
        }

        return !$this->targetServiceProviders()->exists();
    }

    /**
     * Whether the targeted provider pivot table has been migrated.
     */
    public static function supportsProviderTargeting(): bool
    {
        if (self::$providerTargetingTableExists !== null) {
            return self::$providerTargetingTableExists;
        }

        return self::$providerTargetingTableExists = Schema::hasTable('admin_notification_service_provider');
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
