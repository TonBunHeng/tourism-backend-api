<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GalleryMediaResource;
use App\Models\GalleryMedia;
use App\Models\GalleryMediaTag;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = GalleryMedia::with(['category', 'place', 'uploader', 'tags']);

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($placeId = $request->query('place_id')) {
            $query->where('place_id', $placeId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = (int) $request->query('per_page', 12);
        $media = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->successResponse(GalleryMediaResource::collection($media), 'Gallery media retrieved successfully.', 200, [
            'total' => $media->total(),
            'per_page' => $media->perPage(),
            'current_page' => $media->currentPage(),
            'last_page' => $media->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'type' => ['required', Rule::in(['image', 'video'])],
            'url' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'place_id' => 'nullable|exists:places,id',
            'file_size' => 'nullable|string|max:30',
            'dimensions' => 'nullable|string|max:30',
            'status' => ['required', Rule::in(['Published', 'Draft'])],
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $validated['uploaded_by_user_id'] = $request->user()?->id;

        $media = GalleryMedia::create($validated);

        foreach ($tags as $tagName) {
            GalleryMediaTag::create([
                'media_id' => $media->id,
                'tag_name' => $tagName,
            ]);
        }

        $media->load(['category', 'place', 'uploader', 'tags']);

        return $this->successResponse(new GalleryMediaResource($media), 'Media uploaded successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $media = GalleryMedia::with(['category', 'place', 'uploader', 'tags'])->find($id);

        if (!$media) {
            return $this->errorResponse('Gallery item not found.', 404);
        }

        $media->increment('views_count');

        return $this->successResponse(new GalleryMediaResource($media), 'Gallery item retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $media = GalleryMedia::find($id);

        if (!$media) {
            return $this->errorResponse('Gallery item not found.', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'type' => ['sometimes', Rule::in(['image', 'video'])],
            'url' => 'sometimes|required|string',
            'category_id' => 'nullable|exists:categories,id',
            'place_id' => 'nullable|exists:places,id',
            'file_size' => 'nullable|string|max:30',
            'dimensions' => 'nullable|string|max:30',
            'status' => ['sometimes', Rule::in(['Published', 'Draft'])],
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        if (array_key_exists('tags', $validated)) {
            $tags = $validated['tags'] ?? [];
            unset($validated['tags']);

            GalleryMediaTag::where('media_id', $media->id)->delete();
            foreach ($tags as $tagName) {
                GalleryMediaTag::create([
                    'media_id' => $media->id,
                    'tag_name' => $tagName,
                ]);
            }
        }

        $media->update($validated);
        $media->load(['category', 'place', 'uploader', 'tags']);

        return $this->successResponse(new GalleryMediaResource($media), 'Gallery item updated successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $media = GalleryMedia::find($id);

        if (!$media) {
            return $this->errorResponse('Gallery item not found.', 404);
        }

        $media->delete();

        return $this->successResponse(null, 'Gallery item deleted successfully.');
    }
}
