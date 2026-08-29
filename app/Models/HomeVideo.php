<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * The single, admin-managed video section on the home page.
 *
 * Effectively a singleton: current() creates the row on first access so the
 * admin screen never has to deal with "no record yet", and forHomePage()
 * returns null unless the section is switched on AND actually has something
 * to play — the home view renders nothing at all in that case.
 */
class HomeVideo extends Model
{
    use HasFactory;

    public const SOURCE_EMBED = 'embed';
    public const SOURCE_UPLOAD = 'upload';

    /** Where uploads live on the public disk. */
    public const VIDEO_DIR = 'home-video';
    public const POSTER_DIR = 'home-video/posters';

    protected $fillable = [
        'is_active',
        'source_type',
        'video_url',
        'video_path',
        'poster_path',
        'title_en',
        'title_ar',
        'title_fr',
        'description_en',
        'description_ar',
        'description_fr',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* =====================================================================
     |  LOOKUPS
     * ===================================================================== */

    /**
     * The one row, created empty on first access.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'is_active' => false,
            'source_type' => self::SOURCE_EMBED,
        ]);
    }

    /**
     * What the home page should render, or null to render nothing.
     *
     * Both conditions matter: an admin who switches the section on before
     * pasting a URL must not get an empty black box on the live home page.
     */
    public static function forHomePage(): ?self
    {
        $video = static::query()->first();

        return $video && $video->is_active && $video->isPlayable() ? $video : null;
    }

    /**
     * Whether there is actually something to play, given the chosen source.
     */
    public function isPlayable(): bool
    {
        return $this->isUpload()
            ? filled($this->video_path)
            : filled($this->embedUrl());
    }

    public function isUpload(): bool
    {
        return $this->source_type === self::SOURCE_UPLOAD;
    }

    /* =====================================================================
     |  URLS
     * ===================================================================== */

    /**
     * Public URL of an uploaded video file, or null.
     */
    public function uploadUrl(): ?string
    {
        return $this->video_path ? Storage::url($this->video_path) : null;
    }

    public function posterUrl(): ?string
    {
        return $this->poster_path ? Storage::url($this->poster_path) : null;
    }

    /**
     * The stored URL turned into something an <iframe> can load, or null when
     * it is not a URL we recognise.
     *
     * Only YouTube and Vimeo are accepted, and only after the video id has
     * been extracted and re-encoded into a canonical embed URL. Passing the
     * admin's raw string to an iframe src would let any URL — including a
     * javascript: one — reach the page.
     */
    public function embedUrl(): ?string
    {
        $id = $this->youtubeId();

        if ($id !== null) {
            // youtube-nocookie defers YouTube's tracking cookies until the
            // viewer actually presses play.
            return 'https://www.youtube-nocookie.com/embed/' . $id . '?autoplay=1&rel=0';
        }

        $id = $this->vimeoId();

        if ($id !== null) {
            return 'https://player.vimeo.com/video/' . $id . '?autoplay=1';
        }

        return null;
    }

    /**
     * Thumbnail YouTube hosts for us, used when the admin supplies a video
     * but no poster of their own. Vimeo has no equivalent URL that works
     * without an API call, so it falls back to a gradient placeholder.
     */
    public function fallbackPosterUrl(): ?string
    {
        $id = $this->youtubeId();

        return $id !== null ? "https://i.ytimg.com/vi/{$id}/maxresdefault.jpg" : null;
    }

    /**
     * The poster to show, preferring the admin's upload.
     */
    public function displayPosterUrl(): ?string
    {
        return $this->posterUrl() ?? ($this->isUpload() ? null : $this->fallbackPosterUrl());
    }

    /* =====================================================================
     |  URL PARSING
     * ===================================================================== */

    /**
     * YouTube video id from any of the shapes people actually paste:
     * watch?v=, youtu.be/, /embed/, /shorts/, /live/ — with or without
     * extra query parameters.
     */
    public function youtubeId(): ?string
    {
        return self::parseYoutubeId($this->video_url);
    }

    public function vimeoId(): ?string
    {
        return self::parseVimeoId($this->video_url);
    }

    public static function parseYoutubeId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);

        if (!in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'youtu.be'], true)) {
            return null;
        }

        // youtu.be/<id>
        if ($host === 'youtu.be') {
            $id = trim((string) parse_url($url, PHP_URL_PATH), '/');

            return self::validYoutubeId($id);
        }

        // youtube.com/watch?v=<id>
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        if (!empty($query['v'])) {
            return self::validYoutubeId($query['v']);
        }

        // youtube.com/{embed,shorts,live}/<id>
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $segments = explode('/', $path);

        if (count($segments) >= 2 && in_array($segments[0], ['embed', 'shorts', 'live', 'v'], true)) {
            return self::validYoutubeId($segments[1]);
        }

        return null;
    }

    public static function parseVimeoId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);

        if (!in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
            return null;
        }

        // vimeo.com/<id> and player.vimeo.com/video/<id>; the first all-digit
        // segment is the id in both, and in vimeo.com/<user>/<id> too.
        foreach (explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/')) as $segment) {
            if (preg_match('/^\d{6,}$/', $segment)) {
                return $segment;
            }
        }

        return null;
    }

    /**
     * YouTube ids are 11 characters of an unreserved alphabet. Checking the
     * shape is what stops a crafted path segment reaching the iframe src.
     */
    private static function validYoutubeId(?string $id): ?string
    {
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
    }

    /* =====================================================================
     |  LOCALISED COPY
     * ===================================================================== */

    public function getLocalizedTitleAttribute(): ?string
    {
        return $this->localizedValue('title');
    }

    public function getLocalizedDescriptionAttribute(): ?string
    {
        return $this->localizedValue('description');
    }

    /**
     * Current locale first, then the other two — matching LegalPage, so a
     * section translated only in English still renders for an Arabic visitor
     * instead of showing a blank heading.
     */
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

    /* =====================================================================
     |  FILE CLEANUP
     * ===================================================================== */

    /**
     * Delete the uploaded video file from disk and clear the column.
     */
    public function deleteVideoFile(): void
    {
        if ($this->video_path && Storage::disk('public')->exists($this->video_path)) {
            Storage::disk('public')->delete($this->video_path);
        }

        $this->forceFill(['video_path' => null])->save();
    }

    public function deletePosterFile(): void
    {
        if ($this->poster_path && Storage::disk('public')->exists($this->poster_path)) {
            Storage::disk('public')->delete($this->poster_path);
        }

        $this->forceFill(['poster_path' => null])->save();
    }
}
