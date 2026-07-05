<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\AdminAnalyticsExclusion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ProviderAnalytics extends Model
{
    use HasFactory;

    protected $table = 'analytics';

    protected $fillable = [
        'provider_id',
        'user_id',
        'action_type',
        'session_hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('exclude_admin_activity', function ($query) {
            if (!Cache::get('analytics_has_user_id')) {
                return;
            }
            // NULL-safe: keeps guest rows (user_id IS NULL) while excluding admins.
            AdminAnalyticsExclusion::apply($query);
        });
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }
}

