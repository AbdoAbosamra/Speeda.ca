<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LegalPage extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public const TYPE_PRIVACY_POLICY = 'privacy_policy';
    public const TYPE_TERMS_OF_SERVICE = 'terms_of_service';
    public const TYPE_POLICY = 'policy';
    public const TYPE_CUSTOM = 'custom';

    protected $fillable = [
        'slug',
        'page_type',
        'status',
        'allow_indexing',
        'published_at',
        'last_reviewed_at',
        'title_en',
        'title_ar',
        'title_fr',
        'content_en',
        'content_ar',
        'content_fr',
        'summary_en',
        'summary_ar',
        'summary_fr',
        'seo_title_en',
        'seo_title_ar',
        'seo_title_fr',
        'seo_description_en',
        'seo_description_ar',
        'seo_description_fr',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allow_indexing' => 'boolean',
        'published_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'localized_title',
        'localized_content',
        'localized_summary',
        'localized_seo_title',
        'localized_seo_description',
        'public_url',
    ];

    private static ?bool $tableExists = null;

    public static function defaultPages(): array
    {
        return [
            'privacy-policy' => [
                'slug' => 'privacy-policy',
                'page_type' => self::TYPE_PRIVACY_POLICY,
                'title_en' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'title_fr' => 'Politique de confidentialité',
                'route' => 'privacy-policy',
                'fallback_view' => 'Static.PrivacyPolicy',
            ],
            'terms-of-service' => [
                'slug' => 'terms-of-service',
                'page_type' => self::TYPE_TERMS_OF_SERVICE,
                'title_en' => 'Terms of Service',
                'title_ar' => 'شروط الخدمة',
                'title_fr' => 'Conditions d’utilisation',
                'route' => 'terms-of-service',
                'fallback_view' => 'Static.terms-of-service',
            ],
        ];
    }

    public static function defaultForSlug(string $slug): ?array
    {
        return self::defaultPages()[$slug] ?? null;
    }

    public static function supportsDatabasePages(): bool
    {
        if (self::$tableExists !== null) {
            return self::$tableExists;
        }

        return self::$tableExists = Schema::hasTable('legal_pages');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $builder) {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && (!$this->published_at || $this->published_at->lte(now()));
    }

    public function isDefaultPage(): bool
    {
        return array_key_exists($this->slug, self::defaultPages());
    }

    public function getLocalizedTitleAttribute(): string
    {
        return $this->localizedValue('title') ?: 'Legal Page';
    }

    public function getLocalizedContentAttribute(): string
    {
        return $this->localizedValue('content') ?: '';
    }

    public function getLocalizedSummaryAttribute(): string
    {
        return $this->localizedValue('summary')
            ?: Str::limit(strip_tags($this->localized_content), 180);
    }

    public function getLocalizedSeoTitleAttribute(): string
    {
        return $this->localizedValue('seo_title') ?: $this->localized_title;
    }

    public function getLocalizedSeoDescriptionAttribute(): string
    {
        return $this->localizedValue('seo_description') ?: $this->localized_summary;
    }

    public function getPublicUrlAttribute(): string
    {
        $default = self::defaultForSlug($this->slug);

        if ($default && isset($default['route']) && Route::has($default['route'])) {
            return route($default['route']);
        }

        return route('legal-pages.show', $this->slug);
    }

    protected function localizedValue(string $baseColumn): ?string
    {
        $locale = app()->getLocale();
        $columns = [];

        if (in_array($locale, ['en', 'ar', 'fr'], true)) {
            $columns[] = "{$baseColumn}_{$locale}";
        }

        $columns = array_merge($columns, [
            "{$baseColumn}_en",
            "{$baseColumn}_ar",
            "{$baseColumn}_fr",
        ]);

        foreach (array_unique($columns) as $column) {
            $value = $this->getAttribute($column);

            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }
}
