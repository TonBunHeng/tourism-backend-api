<?php

namespace App\Traits;

trait NormalizesMediaUrl
{
    /**
     * Normalize storage and media URLs to always use the incoming request host/protocol.
     */
    protected function normalizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        // Handle relative storage paths e.g. "storage/..." or "/storage/..."
        if (str_starts_with($url, 'storage/') || str_starts_with($url, '/storage/')) {
            return url('/' . ltrim($url, '/'));
        }

        // Handle URLs with /storage/ on localhost, 127.0.0.1, or old IPs
        if (str_contains($url, '/storage/')) {
            $path = parse_url($url, PHP_URL_PATH);
            if ($path) {
                return url($path);
            }
        }

        return $url;
    }
}
