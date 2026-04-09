<?php

namespace App\Http\Controllers;

use App\Actions\CalculateProfileCompletionAction;
use App\Helpers\ErrorHelper;
use App\Models\ServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * AJAX gallery controller for per-image management.
 *
 * All three methods return JSON and are consumed by the Alpine.js
 * gallery grid component in provider/gallery/_grid.blade.php.
 *
 * Maximum 4 images per provider is enforced on store().
 */
class GalleryController extends Controller
{
    private const MAX_GALLERY_IMAGES = 4;
    private const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_MIMES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

    /**
     * Store a single gallery image (AJAX).
     */
    public function store(Request $request, ServiceProvider $serviceProvider): JsonResponse
    {
        $this->authorizeOwner($serviceProvider);

        // Enforce 4-image limit
        $currentCount = $serviceProvider->getMedia('gallery')->count();
        if ($currentCount >= self::MAX_GALLERY_IMAGES) {
            return response()->json([
                'success' => false,
                'message' => __('service_provider.gallery_full', ['max' => self::MAX_GALLERY_IMAGES]),
            ], 422);
        }

        $request->validate([
            'gallery_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $request->file('gallery_image');

        // MIME spoofing check — verify file magic bytes
        if (!$this->isGenuineImage($file)) {
            return response()->json([
                'success' => false,
                'message' => __('service_provider.invalid_image_file'),
            ], 422);
        }

        try {
            $media = null;
            try {
                $media = $serviceProvider
                    ->addMedia($file)
                    ->toMediaCollection('gallery');
            } catch (\Throwable $conversionError) {
                // Ignore Spatie conversion exceptions (e.g., missing GD functions).
                // The media record and original file are already saved before conversions fire.
                $media = $serviceProvider->getMedia('gallery')->sortByDesc('id')->first();
                Log::warning('Gallery upload conversion failed: ' . $conversionError->getMessage());
            }

            if (!$media) {
                throw new \Exception('Media upload failed entirely.');
            }

            app(CalculateProfileCompletionAction::class)->execute($serviceProvider->fresh());

            return response()->json([
                'success' => true,
                'message' => __('service_provider.gallery_image_added'),
                'media'   => $this->mediaPayload($serviceProvider, $media),
                'count'   => $serviceProvider->getMedia('gallery')->count(),
                'max'     => self::MAX_GALLERY_IMAGES,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gallery store failed', [
                'user_id' => auth()->id(),
                'sp_id'   => $serviceProvider->id,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => __('service_provider.gallery_upload_failed'),
            ], 500);
        }
    }

    /**
     * Replace a single gallery image (AJAX).
     */
    public function update(Request $request, ServiceProvider $serviceProvider, int $mediaId): JsonResponse
    {
        $this->authorizeOwner($serviceProvider);

        $request->validate([
            'gallery_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $request->file('gallery_image');

        if (!$this->isGenuineImage($file)) {
            return response()->json([
                'success' => false,
                'message' => __('service_provider.invalid_image_file'),
            ], 422);
        }

        try {
            $oldMedia = $this->resolveMedia($serviceProvider, $mediaId);

            $newMedia = null;
            try {
                // Add new
                $newMedia = $serviceProvider
                    ->addMedia($file)
                    ->toMediaCollection('gallery');
            } catch (\Throwable $conversionError) {
                // Ignore Spatie conversion exceptions (e.g., missing GD functions).
                // The media record and original file are already saved before conversions fire.
                $newMedia = $serviceProvider->getMedia('gallery')->sortByDesc('id')->first();
                Log::warning('Gallery replace conversion failed: ' . $conversionError->getMessage());
            }

            if (!$newMedia) {
                throw new \Exception('Media replacement failed entirely.');
            }

            // Then delete old (guaranteed atomic-ish swap)
            $oldMedia->delete();

            app(CalculateProfileCompletionAction::class)->execute($serviceProvider->fresh());

            return response()->json([
                'success' => true,
                'message' => __('service_provider.gallery_image_replaced'),
                'media'   => $this->mediaPayload($serviceProvider, $newMedia),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gallery replace failed', [
                'user_id'  => auth()->id(),
                'sp_id'    => $serviceProvider->id,
                'media_id' => $mediaId,
                'error'    => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => __('service_provider.gallery_upload_failed'),
            ], 500);
        }
    }

    /**
     * Delete a single gallery image (AJAX).
     */
    public function destroy(ServiceProvider $serviceProvider, int $mediaId): JsonResponse
    {
        $this->authorizeOwner($serviceProvider);

        try {
            $media = $this->resolveMedia($serviceProvider, $mediaId);
            $media->delete();

            app(CalculateProfileCompletionAction::class)->execute($serviceProvider->fresh());

            return response()->json([
                'success' => true,
                'message' => __('service_provider.gallery_image_deleted'),
                'count'   => $serviceProvider->getMedia('gallery')->count(),
                'max'     => self::MAX_GALLERY_IMAGES,
            ]);
        } catch (\Throwable $e) {
            Log::error('Gallery delete failed', [
                'user_id'  => auth()->id(),
                'sp_id'    => $serviceProvider->id,
                'media_id' => $mediaId,
                'error'    => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => __('service_provider.gallery_upload_failed'),
            ], 500);
        }
    }

    // ─── Private helpers ─────────────────────────────────────────

    private function authorizeOwner(ServiceProvider $serviceProvider): void
    {
        if (!auth()->check() || auth()->id() !== $serviceProvider->user_id) {
            abort(403, __('service_provider.unauthorized_access'));
        }
    }

    private function resolveMedia(ServiceProvider $serviceProvider, int $mediaId): Media
    {
        return Media::query()
            ->whereKey($mediaId)
            ->where('model_type', ServiceProvider::class)
            ->where('model_id', $serviceProvider->id)
            ->where('collection_name', 'gallery')
            ->firstOrFail();
    }

    /**
     * Build a JSON-safe payload for one media item.
     */
    private function mediaPayload(ServiceProvider $serviceProvider, Media $media): array
    {
        return [
            'id'        => $media->id,
            'thumb_url' => $serviceProvider->getMediaPublicUrl(
                $media,
                $media->hasGeneratedConversion('gallery_thumb') ? 'gallery_thumb' : null
            ) ?? $media->getUrl(),
            'full_url'  => $serviceProvider->getMediaPublicUrl(
                $media,
                $media->hasGeneratedConversion('gallery_large') ? 'gallery_large' : null
            ) ?? $media->getUrl(),
        ];
    }

    /**
     * Verify the uploaded file is a genuine image by reading magic bytes.
     * Rejects files whose first bytes match a PHP open tag (MIME spoofing).
     */
    private function isGenuineImage(\Illuminate\Http\UploadedFile $file): bool
    {
        $handle = @fopen($file->getRealPath(), 'rb');
        if (!$handle) {
            return false;
        }

        $header = fread($handle, 512);
        fclose($handle);

        if ($header === false || strlen($header) < 4) {
            return false;
        }

        // Reject PHP scripts disguised as images
        if (str_contains($header, '<?php') || str_contains($header, '<?=')) {
            return false;
        }

        // Check for known image magic bytes
        $hex = bin2hex(substr($header, 0, 4));

        // JPEG: FF D8 FF
        if (str_starts_with($hex, 'ffd8ff')) {
            return true;
        }
        // PNG: 89 50 4E 47
        if (str_starts_with($hex, '89504e47')) {
            return true;
        }
        // WebP: RIFF....WEBP
        if (substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP') {
            return true;
        }

        return false;
    }
}
