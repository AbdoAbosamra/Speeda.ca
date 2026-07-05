<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'title_ar',
        'title_en',
        'title_fr',
        'slug',
        'content',
        'content_ar',
        'content_en',
        'content_fr',
        'excerpt',
        'excerpt_ar',
        'excerpt_en',
        'excerpt_fr',
        'category_id',
        'image',
        'featured_image',
        'featured_image_alt_ar',
        'featured_image_alt_en',
        'featured_image_alt_fr',
        'seo_title_ar',
        'seo_title_en',
        'seo_title_fr',
        'seo_description_ar',
        'seo_description_en',
        'seo_description_fr',
        'seo_keywords_ar',
        'seo_keywords_en',
        'seo_keywords_fr',
        'og_title_ar',
        'og_title_en',
        'og_title_fr',
        'og_description_ar',
        'og_description_en',
        'og_description_fr',
        'og_image',
        'twitter_title_ar',
        'twitter_title_en',
        'twitter_title_fr',
        'twitter_description_ar',
        'twitter_description_en',
        'twitter_description_fr',
        'twitter_image',
        'author_id',
        'status',
        'published_at',
        'is_featured',
        'allow_indexing',
        'canonical_url',
        'meta_robots',
        'reading_time_minutes',
        'location_id',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'allow_indexing' => 'boolean',
        'published_at' => 'datetime',
        'reading_time_minutes' => 'integer',
    ];

    protected $appends = [
        'localized_title',
        'localized_excerpt',
        'localized_content',
        'localized_seo_title',
        'localized_seo_description',
        'localized_seo_keywords',
        'localized_featured_image_alt',
        'image_url',
        'published_date',
    ];

    protected static array $columnExistsCache = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        if (self::hasDatabaseColumn('status')) {
            $query->where(function (Builder $builder) {
                $builder
                    ->where(function (Builder $publishedQuery) {
                        $publishedQuery->where('status', 'published');

                        if (self::hasDatabaseColumn('published_at')) {
                            $publishedQuery->where(function (Builder $dateQuery) {
                                $dateQuery->whereNull('published_at')
                                    ->orWhere('published_at', '<=', now());
                            });
                        }
                    })
                    ->orWhere(function (Builder $scheduledQuery) {
                        $scheduledQuery->where('status', 'scheduled');

                        if (self::hasDatabaseColumn('published_at')) {
                            $scheduledQuery->where('published_at', '<=', now());
                        }
                    });
            });
        } elseif (self::hasDatabaseColumn('is_published')) {
            $query->where('is_published', true);
        }

        return $query;
    }

    public function scopeLatestPublished(Builder $query): Builder
    {
        if (self::hasDatabaseColumn('published_at')) {
            return $query->orderByDesc('published_at')->orderByDesc('created_at');
        }

        return $query->latest();
    }

    public function scopeSearchPublic(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $columns = [
            'title',
            'title_ar',
            'title_en',
            'title_fr',
            'excerpt',
            'excerpt_ar',
            'excerpt_en',
            'excerpt_fr',
            'content',
            'content_ar',
            'content_en',
            'content_fr',
            'slug',
        ];

        $availableColumns = array_values(array_filter($columns, fn(string $column) => self::hasDatabaseColumn($column)));

        if ($availableColumns === []) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($availableColumns, $term) {
            foreach ($availableColumns as $column) {
                $builder->orWhere($column, 'like', '%' . $term . '%');
            }
        });
    }

    public function getLocalizedTitleAttribute(): string
    {
        return $this->getLocalizedValue('title') ?: 'Untitled Post';
    }

    public function getLocalizedExcerptAttribute(): ?string
    {
        return $this->getLocalizedValue('excerpt')
            ?: Str::limit(strip_tags((string) $this->getLocalizedContentAttribute()), 180);
    }

    public function getLocalizedContentAttribute(): string
    {
        return (string) ($this->getLocalizedValue('content') ?: $this->content ?: '');
    }

    public function getLocalizedSeoTitleAttribute(): string
    {
        return $this->getLocalizedValue('seo_title') ?: $this->localized_title;
    }

    public function getLocalizedSeoDescriptionAttribute(): string
    {
        return $this->getLocalizedValue('seo_description')
            ?: Str::limit(strip_tags((string) $this->localized_excerpt), 160);
    }

    public function getLocalizedSeoKeywordsAttribute(): ?string
    {
        return $this->getLocalizedValue('seo_keywords');
    }

    public function getLocalizedFeaturedImageAltAttribute(): string
    {
        return $this->getLocalizedValue('featured_image_alt') ?: $this->localized_title;
    }

    public function getPublishedDateAttribute(): ?string
    {
        $date = $this->published_at ?: $this->created_at;

        return $date?->format('M d, Y');
    }

    public function getImageUrlAttribute(): string
    {
        $path = $this->featured_image ?: $this->image;

        if (!$path) {
            return asset('images/banner.png');
        }

        if (Str::startsWith($path, ['http://', 'https://', '/storage/', '/images/', '/'])) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        return asset($path);
    }

    public function isIndexable(): bool
    {
        if (self::hasDatabaseColumn('allow_indexing')) {
            return (bool) ($this->allow_indexing ?? true);
        }

        return true;
    }

    protected function getLocalizedValue(string $baseColumn): ?string
    {
        $locale = app()->getLocale();
        
        // 1. Try the requested locale's specific column
        $localeColumn = in_array($locale, ['en', 'ar', 'fr']) ? $baseColumn . '_' . $locale : $baseColumn;
        $value = $this->getAttribute($localeColumn);
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        // 2. Try the base column (often contains the primary/English text)
        $baseValue = $this->getAttribute($baseColumn);
        if (is_string($baseValue) && trim($baseValue) !== '') {
            return $baseValue;
        }

        // 3. Try other languages as fallbacks
        $fallbackChain = ['en', 'ar', 'fr'];
        foreach ($fallbackChain as $lang) {
            if ($lang === $locale) continue;
            
            $fallbackColumn = $baseColumn . '_' . $lang;
            $fallbackValue = $this->getAttribute($fallbackColumn);
            if (is_string($fallbackValue) && trim($fallbackValue) !== '') {
                return $fallbackValue;
            }
        }

        return null;
    }

    protected static function hasDatabaseColumn(string $column): bool
    {
        $cacheKey = static::class . ':' . $column;

        if (!array_key_exists($cacheKey, self::$columnExistsCache)) {
            self::$columnExistsCache[$cacheKey] = Schema::hasColumn((new static)->getTable(), $column);
        }

        return self::$columnExistsCache[$cacheKey];
    }
}
