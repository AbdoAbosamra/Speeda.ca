<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'user_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Get the service provider being rated.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /**
     * Get the user who submitted this rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate and update the service provider's average rating.
     */
    public static function recalculateProviderRating(int $serviceProviderId): void
    {
        $average = self::where('service_provider_id', $serviceProviderId)
            ->avg('rating');

        $provider = ServiceProvider::find($serviceProviderId);
        if ($provider) {
            $provider->update([
                'rating' => $average ? round($average, 2) : null,
            ]);
        }
    }

    /**
     * Boot method to trigger rating recalculation on save/delete.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function (Rating $rating) {
            self::recalculateProviderRating($rating->service_provider_id);
        });

        static::deleted(function (Rating $rating) {
            self::recalculateProviderRating($rating->service_provider_id);
        });
    }
}
