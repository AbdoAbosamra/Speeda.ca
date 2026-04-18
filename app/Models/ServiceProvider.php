<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\File;

class ServiceProvider extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'location_id',
        'company_name',
        'bio',
        'address',
        'experience_years',
        'hourly_rate',
        'emergency_available',
        'available_weekends',
        'available_evenings',
        'response_time_hours',
        'languages',
        'specializations',
        'services_offered',
        'availability_schedule',
        'profile_image',
        'profile_completion_percent',
        'profile_completion_popup_shown_at',
        'business_license',
        'business_type',
        'is_verified',
        'is_certified',
        'certification',
        'phone',
        'whatsapp_number',
        'views',
        'rating',
        'endorsement_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'emergency_available' => 'boolean',
        'available_weekends' => 'boolean',
        'available_evenings' => 'boolean',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'is_certified' => 'boolean',
        'experience_years' => 'integer',
        'hourly_rate' => 'decimal:2',
        'response_time_hours' => 'integer',
        'views' => 'integer',
        'profile_completion_percent' => 'integer',
        'profile_completion_popup_shown_at' => 'datetime',
        'languages' => 'array',
        'specializations' => 'array',
        'services_offered' => 'array',
        'availability_schedule' => 'array',
        'portfolio_images' => 'array',
        'portfolio_videos' => 'array',
    ];

    /**
     * Gallery media collection for provider images.
     * - Dynamic image count
     * - Only JPEG/PNG/WebP
     * - Stored in the `public` disk
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('gallery')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/webp'])
            ->acceptsFile(function (File $file) {
                // Backend defense in depth: max 10MB
                return $file->size <= 10 * 1024 * 1024;
            });
    }

    /**
     * Queued conversions:
     * - Resize + optimize
     * - Convert to WebP
     * - Generate a thumbnail for the responsive gallery grid
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('gallery_thumb')
            ->performOnCollections('gallery')
            ->nonQueued()
            ->width(600)
            ->height(600)
            ->fit('crop', 600, 600)
            ->quality(80)
            ->format('webp');

        $this
            ->addMediaConversion('gallery_large')
            ->performOnCollections('gallery')
            ->nonQueued()
            ->width(1200)
            ->height(1200)
            ->fit('crop', 1200, 1200)
            ->quality(85)
            ->format('webp');
    }

    /**
     * Provide a virtual "business_name" attribute that maps to company_name.
     */
    protected function businessName(): Attribute
    {
        return Attribute::make(
            get: fn($value, array $attributes) => $value ?? $attributes['company_name'] ?? null,
            set: fn($value) => ['company_name' => $value]
        );
    }

    /**
     * Get the user that owns the service provider profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the service areas for the service provider.
     */
    public function serviceAreas(): HasMany
    {
        return $this->hasMany(ServiceArea::class);
    }

    /**
     * Get all locations where this provider offers services.
     */
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'service_areas')
            ->withPivot(['radius_km', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get the primary location of the service provider.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the category that the service provider belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Legacy cleanup: profile() method removed
    // ServiceProviderProfile model no longer exists

    /**
     * Get all reviews for this service provider.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get active reviews for this service provider.
     */
    public function activeReviews()
    {
        return $this->reviews()->where('is_active', true);
    }

    /**
     * Get all endorsements for this service provider.
     */
    public function endorsements(): HasMany
    {
        return $this->hasMany(Endorsement::class);
    }

    /**
     * Check if a user has endorsed this provider.
     */
    public function isEndorsedBy(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }
        return $this->endorsements()->where('user_id', $userId)->exists();
    }

    /**
     * Check if the current user is the owner of this profile.
     */
    public function isOwner()
    {
        return auth()->check() && auth()->user()->id === $this->user_id;
    }

    /**
     * Get the formatted hourly rate.
     */
    // public function getFormattedHourlyRateAttribute(): string
    // {
    //     return $this->hourly_rate !== null
    //         ? '$'.number_format((float) $this->hourly_rate, 2).'/hr'
    //         : 'Not specified';
    // }

    /**
     * Get the URL for the profile image.
     */
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image && Storage::disk('public')->exists($this->profile_image)) {
            return $this->normalizePublicUrl(Storage::url($this->profile_image));
        }

        return $this->default_image_url;
    }

    /**
     * Get a safe URL for the first gallery image when present.
     */
    public function getGalleryImageUrlAttribute(): ?string
    {
        $media = $this->getMedia('gallery')->first();

        if (!$media) {
            return null;
        }

        return $this->getMediaPublicUrl(
            $media,
            $media->hasGeneratedConversion('gallery_thumb') ? 'gallery_thumb' : null
        );
    }

    /**
     * Get the best image to display for cards/profile thumbnails.
     */
    public function getDisplayImageUrlAttribute(): string
    {
        return $this->profile_image_url;
    }

    /**
     * Get a safe local fallback image path.
     */
    public function getDefaultImageUrlAttribute(): string
    {
        return '/images/default-provider.jpg';
    }

    /**
     * Normalize app-generated URLs to same-origin relative paths for local disks.
     */
    private function normalizePublicUrl(string $url): string
    {
        if (!Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['path'])) {
            return $url;
        }

        $normalized = $parts['path'];

        if (!empty($parts['query'])) {
            $normalized .= '?' . $parts['query'];
        }

        return $normalized;
    }

    /**
     * Resolve a media-library file to a safe same-origin public URL when it exists.
     */
    public function getMediaPublicUrl(?Media $media, ?string $conversionName = null): ?string
    {
        if (!$media) {
            return null;
        }

        $relativePath = $media->getPathRelativeToRoot($conversionName ?? '');
        if (!Storage::disk($media->disk)->exists($relativePath)) {
            return null;
        }

        return $this->normalizePublicUrl($media->getUrl($conversionName ?? ''));
    }

    /**
     * Get the formatted experience.
     */
    public function getFormattedExperienceAttribute(): string
    {
        if (!$this->experience_years) {
            return 'Not specified';
        }

        return $this->experience_years . ' year' . ($this->experience_years > 1 ? 's' : '') . ' experience';
    }

    /**
     * Get the formatted views count with K and M suffixes for large numbers.
     *
     * @return string Formatted views count (e.g., '1.5K' for 1500, '2.3M' for 2,300,000)
     */
    public function getFormattedViewsAttribute(): string
    {
        if ($this->views < 1000) {
            return (string) $this->views;
        } elseif ($this->views < 1000000) {
            return round($this->views / 1000, 1) . 'K';
        } else {
            return round($this->views / 1000000, 1) . 'M';
        }
    }

    /**
     * Increment the view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Get the calculated rating from active reviews.
     * This ensures rating is always accurate even if stored value is stale.
     *
     * @return Attribute
     */
    protected function calculatedRating(): Attribute
    {
        return Attribute::make(
            get: function () {
                // If we have active reviews loaded, calculate from them
                if ($this->relationLoaded('activeReviews')) {
                    $activeReviews = $this->activeReviews;
                } else {
                    // Otherwise query directly (one query, not N+1)
                    $activeReviews = $this->activeReviews()->get();
                }

                if ($activeReviews->isEmpty()) {
                    return null;
                }

                $average = $activeReviews->avg('rating');
                return round($average, 1);
            }
        );
    }

    /**
     * Get the formatted rating display (e.g., "4.6" or "0.0" if no reviews).
     *
     * @return Attribute
     */
    protected function displayRating(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->calculated_rating ?? $this->rating ?? 0
        );
    }

    /**
     * Get the total count of approved reviews.
     *
     * @return Attribute
     */
    protected function totalReviewsCount(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->relationLoaded('activeReviews')) {
                    return $this->activeReviews->count();
                }
                return $this->activeReviews()->count();
            }
        );
    }

    /**
     * Get the certification status badge.
     */
    public function getVerificationBadgeAttribute(): string
    {
        if ($this->certification) {
            return '<span class="badge bg-success"><i class="fas fa-certificate"></i> Certified</span>';
        }

        return '';
    }

    /**
     * Get the service locations as a comma-separated string.
     */
    public function getServiceLocationsAttribute(): string
    {
        return $this->locations->pluck('city')->join(', ');
    }

    /**
     * Scope a query to only include verified service providers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Get all bookings for this service provider.
     */
    // public function bookings(): HasMany
    // {
    //     return $this->hasMany(Booking::class);
    // }
}
