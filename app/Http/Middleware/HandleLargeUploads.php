<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleLargeUploads
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the request is too large before Laravel throws the exception
        if ($this->isRequestTooLarge($request)) {
            // Store the original data in session for repopulation
            if ($request->hasSession()) {
                $request->session()->flashInput($request->except(['profile_image', 'images']));
                $request->session()->flash('error', 'The uploaded file is too large. Maximum allowed size is '.ini_get('upload_max_filesize').'.');
            }

            return redirect()->back();
        }

        try {
            return $next($request);
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            // Handle the PostTooLargeException gracefully
            if ($request->hasSession()) {
                $request->session()->flashInput($request->except(['profile_image', 'images']));
                $request->session()->flash('error', 'The uploaded file is too large. Please select a smaller image (max '.ini_get('upload_max_filesize').').');
            }

            return redirect()->back();
        }
    }

    /**
     * Check if the request is too large
     */
    protected function isRequestTooLarge(Request $request): bool
    {
        $maxPostSize = $this->getMaxPostSize();
        $contentLength = $request->headers->get('Content-Length');

        if ($contentLength && $maxPostSize > 0) {
            return $contentLength > $maxPostSize;
        }

        return false;
    }

    /**
     * Get the maximum POST size in bytes
     */
    protected function getMaxPostSize(): int
    {
        $postMaxSize = ini_get('post_max_size');

        return $this->convertToBytes($postMaxSize);
    }

    /**
     * Convert PHP size format to bytes
     */
    protected function convertToBytes(string $size): int
    {
        if (empty($size)) {
            return 0;
        }

        $unit = strtolower(substr($size, -1));
        $value = (int) $size;

        switch ($unit) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}
