<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\HomeVideo;
use App\Traits\LogsAdminActions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The home page video section.
 *
 * One record, one screen. The section only reaches the live home page when it
 * is switched on AND has something playable, so a half-configured state is
 * invisible to visitors rather than showing an empty frame.
 */
class AdminHomeVideoController extends Controller
{
    use LogsAdminActions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function edit(): View
    {
        return view('admin.home_video.edit', [
            'video' => HomeVideo::current(),
            'maxUploadMb' => $this->maxUploadMb(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $video = HomeVideo::current();
        $maxUploadKb = $this->maxUploadMb() * 1024;

        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'source_type' => ['required', Rule::in([HomeVideo::SOURCE_EMBED, HomeVideo::SOURCE_UPLOAD])],
            'video_url' => ['nullable', 'string', 'max:500'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', "max:{$maxUploadKb}"],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'title_fr' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_fr' => ['nullable', 'string', 'max:2000'],
        ], [
            'video_file.mimetypes' => __('admin.home_video_file_type_error'),
            'video_file.max' => __('admin.home_video_file_too_large', ['mb' => $this->maxUploadMb()]),
        ]);

        // A URL we cannot turn into an embed would render an empty frame, so
        // it is rejected here rather than silently disabling the section.
        if ($validated['source_type'] === HomeVideo::SOURCE_EMBED && filled($validated['video_url'] ?? null)) {
            $probe = new HomeVideo(['video_url' => $validated['video_url']]);

            if ($probe->embedUrl() === null) {
                throw ValidationException::withMessages([
                    'video_url' => __('admin.home_video_url_unsupported'),
                ]);
            }
        }

        try {
            $oldValues = $video->getOriginal();

            $data = [
                'is_active' => $request->boolean('is_active'),
                'source_type' => $validated['source_type'],
                'video_url' => $validated['video_url'] ?? null,
            ];

            foreach (['title_en', 'title_ar', 'title_fr', 'description_en', 'description_ar', 'description_fr'] as $field) {
                $data[$field] = $validated[$field] ?? null;
            }

            if ($request->hasFile('video_file')) {
                // Replace rather than accumulate: the old file can never be
                // reached again once the column points elsewhere.
                $video->deleteVideoFile();
                $data['video_path'] = $request->file('video_file')->store(HomeVideo::VIDEO_DIR, 'public');
            }

            if ($request->hasFile('poster')) {
                $video->deletePosterFile();
                $data['poster_path'] = $request->file('poster')->store(HomeVideo::POSTER_DIR, 'public');
            }

            $data['updated_by'] = auth()->id();

            $video->update($data);

            $this->logUpdate($video, $oldValues, 'Home video section');
            $this->forgetHomeCaches();

            ErrorHelper::flashNotification($this->savedMessage($video->fresh()), 'success');

            return redirect()->route('admin.home_video.edit');
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the uploaded video file without touching the rest of the record.
     */
    public function destroyVideo(): RedirectResponse
    {
        $video = HomeVideo::current();
        $video->deleteVideoFile();

        $this->logAction('delete', $video, ['field' => 'video_path'], 'Home video file');
        $this->forgetHomeCaches();

        ErrorHelper::flashNotification(__('admin.home_video_file_removed'), 'success');

        return redirect()->route('admin.home_video.edit');
    }

    public function destroyPoster(): RedirectResponse
    {
        $video = HomeVideo::current();
        $video->deletePosterFile();

        $this->logAction('delete', $video, ['field' => 'poster_path'], 'Home video poster');
        $this->forgetHomeCaches();

        ErrorHelper::flashNotification(__('admin.home_video_poster_removed'), 'success');

        return redirect()->route('admin.home_video.edit');
    }

    /**
     * Says plainly whether the section is now live, because "saved" alone
     * hides the most common mistake: switching it on with nothing to play.
     */
    private function savedMessage(HomeVideo $video): string
    {
        if (!$video->is_active) {
            return __('admin.home_video_saved_hidden');
        }

        return $video->isPlayable()
            ? __('admin.home_video_saved_live')
            : __('admin.home_video_saved_incomplete');
    }

    /**
     * PHP's own ceiling, so the form promises what the server can accept
     * instead of failing after a long upload.
     */
    private function maxUploadMb(): int
    {
        $toMb = function (string $value): int {
            $value = trim($value);
            $unit = strtolower(substr($value, -1));
            $number = (int) $value;

            return match ($unit) {
                'g' => $number * 1024,
                'm' => $number,
                'k' => (int) max(1, $number / 1024),
                default => (int) max(1, $number / 1048576),
            };
        };

        return max(1, min(
            $toMb((string) ini_get('upload_max_filesize')),
            $toMb((string) ini_get('post_max_size')),
        ));
    }

    /**
     * The home page reads this record directly, but its sibling caches are
     * dropped for consistency with the other admin write paths.
     */
    private function forgetHomeCaches(): void
    {
        foreach (['home_featured_providers', 'home_latest_providers', 'home_latest_blog_posts'] as $key) {
            Cache::forget($key);
        }
    }
}
