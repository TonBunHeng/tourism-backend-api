<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewReplyResource;
use App\Http\Resources\ReviewResource;
use App\Models\Place;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Models\ReviewReply;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'place', 'images', 'replies.user']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        if ($placeId = $request->query('place_id')) {
            $query->where('place_id', $placeId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($rating = $request->query('rating')) {
            $query->where('rating', $rating);
        }

        $perPage = (int) $request->query('per_page', 10);
        $reviews = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->successResponse(ReviewResource::collection($reviews), 'Reviews retrieved successfully.', 200, [
            'total' => $reviews->total(),
            'per_page' => $reviews->perPage(),
            'current_page' => $reviews->currentPage(),
            'last_page' => $reviews->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'string|max:255',
        ]);

        $review = Review::create([
            'user_id' => $request->user()->id,
            'place_id' => $validated['place_id'],
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'],
            'status' => 'Pending',
        ]);

        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $imgUrl) {
                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_url' => $imgUrl,
                ]);
            }
        }

        // Recalculate place rating & reviews count
        $this->updatePlaceRating($validated['place_id']);

        $review->load(['user', 'place', 'images', 'replies.user']);

        return $this->successResponse(new ReviewResource($review), 'Review submitted successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $review = Review::with(['user', 'place', 'images', 'replies.user'])->find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        return $this->successResponse(new ReviewResource($review), 'Review details retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => 'sometimes|required|string',
            'status' => ['sometimes', Rule::in(['Approved', 'Pending', 'Rejected', 'Flagged'])],
            'is_verified' => 'sometimes|boolean',
        ]);

        $review->update($validated);
        $this->updatePlaceRating($review->place_id);

        $review->load(['user', 'place', 'images', 'replies.user']);

        return $this->successResponse(new ReviewResource($review), 'Review updated successfully.');
    }

    public function addReply(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $reply = ReviewReply::create([
            'review_id' => $review->id,
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        $reply->load('user');

        return $this->successResponse(new ReviewReplyResource($reply), 'Reply added successfully.', 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        $placeId = $review->place_id;
        $review->delete();

        $this->updatePlaceRating($placeId);

        return $this->successResponse(null, 'Review deleted successfully.');
    }

    private function updatePlaceRating(int $placeId): void
    {
        $place = Place::find($placeId);
        if ($place) {
            $avgRating = Review::where('place_id', $placeId)->where('status', 'Approved')->avg('rating') ?? 0;
            $count = Review::where('place_id', $placeId)->where('status', 'Approved')->count();
            $place->update([
                'rating' => round($avgRating, 2),
                'reviews_count' => $count,
            ]);
        }
    }
}
