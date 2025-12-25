<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProviderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'location_id',
        'profession',
        'bio',
        'services_offered',
        'hourly_rate',
        'experience_years',
        'phone',
        'address',
        'facebook',
        'instagram',
        'linkedin',
        'service_area',
        'available_weekends',
        'available_evenings',
        'availability_schedule',
        'certifications',
        'languages',
        'specializations',
        'profile_image',
        'portfolio_images',
        'portfolio_videos',
        'business_type',
        'company_name',
        'business_license',
        'completed_jobs',
        'emergency_available',
        'response_time_hours',
    ];

    protected $casts = [
        'available_weekends' => 'boolean',
        'available_evenings' => 'boolean',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'emergency_available' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'availability_schedule' => 'array',
        'certifications' => 'array',
        'languages' => 'array',
        'specializations' => 'array',
        'portfolio_images' => 'array',
        'portfolio_videos' => 'array',
        'views' => 'integer',
    ];

    protected $attributes = [
        'views' => 0,
    ];

    protected $appends = [
        'has_whatsapp',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'service_provider_categories');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Return a collection of additional locations the provider serves.
     * Uses the ServiceProvider -> ServiceArea relationship if the user has a ServiceProvider record.
     */
    public function availableLocations()
    {
        $sp = ServiceProvider::where('user_id', $this->user_id)->first();
        if (! $sp) {
            return collect();
        }

        return $sp->serviceAreas()->with('location')->get()->pluck('location')->filter();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Accessors
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            return asset('storage/'.$this->profile_image);
        }

        // Fallback to the user's avatar if available, then to the default image
        if ($this->user && method_exists($this->user, 'getAvatarUrl')) {
            return $this->user->getAvatarUrl();
        }

        return asset('images/default-profile.png');
    }

    /**
     * Whether the phone looks like a Canadian number and therefore likely available on WhatsApp.
     */
    public function getHasWhatsappAttribute(): bool
    {
        if (empty($this->phone)) {
            return false;
        }

        // Normalize digits
        $digits = preg_replace('/[^0-9]/', '', $this->phone);

        // Canadian numbers are country code 1 and 10 digits in NANP
        if (strlen($digits) === 11 && substr($digits, 0, 1) === '1') {
            return true;
        }

        if (strlen($digits) === 10) {
            return true; // assume local Canadian/US format
        }

        return false;
    }

    /**
     * Increment the profile views counter.
     */
    public function incrementViews(int $by = 1): void
    {
        $this->increment('views', $by);
    }

    public function getPortfolioImagesUrlsAttribute(): array
    {
        if (! $this->portfolio_images) {
            return [];
        }

        return array_map(function ($image) {
            return asset('storage/'.$image);
        }, $this->portfolio_images);
    }

    public function getFormattedHourlyRateAttribute(): string
    {
        return $this->hourly_rate ? '$'.number_format((float) $this->hourly_rate, 2).'/hour' : 'Rate not specified';
    }

    public function getExperienceTextAttribute(): string
    {
        if (! $this->experience_years) {
            return 'Experience not specified';
        }

        return $this->experience_years.' year'.($this->experience_years > 1 ? 's' : '').' of experience';
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeAvailableWeekends($query)
    {
        return $query->where('available_weekends', true);
    }

    public function scopeAvailableEvenings($query)
    {
        return $query->where('available_evenings', true);
    }

    public function scopeEmergencyAvailable($query)
    {
        return $query->where('emergency_available', true);
    }
}
