<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\TravelReviewRequest;
use App\Http\Resources\Travel\TravelReviewResource;
use App\Models\Place;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelReviewController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'place', 'images', 'replies.user'])
            ->where('status', 'Approved');

        if ($placeId = $request->query('place_id')) {
            $query->where('place_id', $placeId);
        }

        if ($rating = $request->query('rating')) {
            if ($rating !== 'All' && is_numeric($rating)) {
                $query->where('rating', $rating);
            }
        }

        $perPage = (int) $request->query('per_page', 10);
        $reviews = $query->orderBy('id', 'desc')->paginate($perPage);

        return $this->successResponse(
            TravelReviewResource::collection($reviews),
            'Reviews retrieved successfully.',
            200,
            [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
            ]
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $review = Review::with(['user', 'place', 'images', 'replies.user'])->find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        // Only allow viewing if approved, or if the authenticated user is the author
        $currentUser = $request->user('sanctum');
        if ($review->status !== 'Approved' && (!$currentUser || $currentUser->id !== $review->user_id)) {
            return $this->errorResponse('Review is undergoing moderation.', 403);
        }

        return $this->successResponse(
            new TravelReviewResource($review),
            'Review details retrieved successfully.'
        );
    }

    public function store(TravelReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $review = Review::create([
            'user_id' => $user->id,
            'place_id' => $validated['place_id'],
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'],
            'status' => 'Pending',
            'is_verified' => false,
        ]);

        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $imgUrl) {
                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_url' => $imgUrl,
                ]);
            }
        }

        $this->updatePlaceRating($validated['place_id']);

        $review->load(['user', 'place', 'images', 'replies.user']);

        \App\Models\Notification::createNotification([
            'type' => 'review',
            'category' => 'Reviews',
            'title' => "New {$review->rating}-Star Review on \"{$review->place?->name}\"",
            'description' => "{$user->name} wrote: \"{$review->title}\" - {$review->comment}",
            'link' => '/ratings',
            'read' => false,
            'data' => [
                'review_id' => $review->id,
                'rating' => $review->rating,
                'place_id' => $review->place_id,
                'place_name' => $review->place?->name,
            ]
        ]);

        return $this->successResponse(
            new TravelReviewResource($review),
            'Review submitted successfully and is pending moderation.',
            201
        );
    }

    public function update(TravelReviewRequest $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        if ($review->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized. You can only edit your own reviews.', 403);
        }

        $validated = $request->validated();

        $updateData = [];
        if (isset($validated['rating'])) {
            $updateData['rating'] = $validated['rating'];
        }
        if (isset($validated['title'])) {
            $updateData['title'] = $validated['title'];
        }
        if (isset($validated['comment'])) {
            $updateData['comment'] = $validated['comment'];
        }

        $review->update($updateData);

        if (isset($validated['images'])) {
            ReviewImage::where('review_id', $review->id)->delete();
            foreach ($validated['images'] as $imgUrl) {
                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_url' => $imgUrl,
                ]);
            }
        }

        $this->updatePlaceRating($review->place_id);

        $review->load(['user', 'place', 'images', 'replies.user']);

        return $this->successResponse(
            new TravelReviewResource($review),
            'Review updated successfully.'
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        if ($review->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized. You can only delete your own reviews.', 403);
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
