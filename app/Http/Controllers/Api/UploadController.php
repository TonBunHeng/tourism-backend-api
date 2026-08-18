<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    use ApiResponse;

    public function upload(Request $request): JsonResponse
    {
        $folder = $request->input('folder', 'uploads');
        // Sanitize folder name
        $folder = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $folder) ?: 'uploads';

        // 1. Handle Multipart File Upload
        if ($request->hasFile('file') || $request->hasFile('image') || $request->hasFile('video') || $request->hasFile('media')) {
            $file = $request->file('file') 
                ?? $request->file('image') 
                ?? $request->file('video') 
                ?? $request->file('media');

            if (!$file->isValid()) {
                return $this->errorResponse('Uploaded file is invalid or corrupted.', 400);
            }

            $mime = $file->getMimeType() ?: 'application/octet-stream';
            $isVideo = str_starts_with($mime, 'video/') || in_array(strtolower($file->getClientOriginalExtension()), ['mp4', 'mov', 'webm', 'avi', 'mkv']);
            $isImage = str_starts_with($mime, 'image/');

            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension() ?: ($isVideo ? 'mp4' : 'jpg');
            $filename = date('Ymd_His') . '_' . Str::random(8) . '.' . $extension;

            // Store explicitly on the public disk
            $path = $file->storeAs($folder, $filename, 'public');
            $url = Storage::disk('public')->url($path);
            $fullUrl = url($url);

            $sizeBytes = $file->getSize();
            $formattedSize = $this->formatBytes($sizeBytes);

            $dimensions = null;
            if ($isImage && function_exists('getimagesize')) {
                $imgInfo = @getimagesize($file->getPathname());
                if ($imgInfo) {
                    $dimensions = "{$imgInfo[0]}x{$imgInfo[1]}";
                }
            }

            return $this->successResponse([
                'url' => $fullUrl,
                'relative_url' => $url,
                'file_name' => $filename,
                'original_name' => $originalName,
                'file_size' => $formattedSize,
                'file_bytes' => $sizeBytes,
                'type' => $isVideo ? 'video' : 'image',
                'mime_type' => $mime,
                'dimensions' => $dimensions,
            ], 'File uploaded successfully.', 201);
        }

        // 2. Handle Base64 Data URI Upload
        $base64Data = $request->input('data_url') ?? $request->input('base64') ?? $request->input('image_data');
        if ($base64Data && is_string($base64Data) && preg_match('/^data:(image|video)\/(\w+);base64,(.+)$/', $base64Data, $matches)) {
            $mediaType = $matches[1]; // 'image' or 'video'
            $extension = strtolower($matches[2]);
            if ($extension === 'jpeg') $extension = 'jpg';
            $decodedData = base64_decode($matches[3]);

            if ($decodedData === false) {
                return $this->errorResponse('Invalid base64 payload.', 400);
            }

            $filename = date('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
            Storage::disk('public')->put("{$folder}/{$filename}", $decodedData);

            $url = Storage::disk('public')->url("{$folder}/{$filename}");
            $fullUrl = url($url);
            $sizeBytes = strlen($decodedData);
            $formattedSize = $this->formatBytes($sizeBytes);

            return $this->successResponse([
                'url' => $fullUrl,
                'relative_url' => $url,
                'file_name' => $filename,
                'original_name' => $filename,
                'file_size' => $formattedSize,
                'file_bytes' => $sizeBytes,
                'type' => $mediaType,
                'mime_type' => "{$mediaType}/{$extension}",
                'dimensions' => null,
            ], 'Media uploaded successfully.', 201);
        }

        return $this->errorResponse('No valid file or media data provided for upload.', 422);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
