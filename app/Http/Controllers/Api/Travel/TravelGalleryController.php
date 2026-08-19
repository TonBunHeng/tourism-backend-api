<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Travel\TravelGalleryResource;
use App\Models\GalleryMedia;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelGalleryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = GalleryMedia::with(['place', 'category', 'tags'])
            ->where('status', 'Published');

        if ($placeId = $request->query('place_id')) {
            $query->where('place_id', $placeId);
        }

        if ($mediaType = $request->query('media_type') ?? $request->query('type')) {
            $query->where('type', $mediaType);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($tag = $request->query('tag')) {
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('tag_name', 'like', "%{$tag}%");
            });
        }

        $perPage = (int) $request->query('per_page', 12);
        $media = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->successResponse(
            TravelGalleryResource::collection($media),
            'Gallery media retrieved successfully.',
            200,
            [
                'total' => $media->total(),
                'per_page' => $media->perPage(),
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $media = GalleryMedia::with(['place', 'category', 'tags'])
            ->where('status', 'Published')
            ->find($id);

        if (!$media) {
            return $this->errorResponse('Gallery media not found.', 404);
        }

        $media->increment('views_count');

        return $this->successResponse(
            new TravelGalleryResource($media),
            'Gallery media retrieved successfully.'
        );
    }
}
