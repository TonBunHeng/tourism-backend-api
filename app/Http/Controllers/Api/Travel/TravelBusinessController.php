<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessDetailResource;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\ReviewResource;
use App\Models\Business;
use App\Models\Notification;
use App\Models\Review;
use App\Models\ReviewImage;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\BusinessServiceResource;
use App\Http\Resources\BusinessHourResource;
use App\Http\Resources\BusinessImageResource;
use App\Http\Resources\BusinessPromotionResource;
use App\Http\Resources\EventResource;
use App\Models\Event;

class TravelBusinessController extends Controller
{
    use ApiResponse;

    /**
     * Public list of approved & active businesses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Business::public()
            ->with(['category', 'province', 'images'])
            ->withCount(['images', 'services', 'reviews']);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('province_id')) {
            $query->where('province_id', $request->input('province_id'));
        }

        if ($request->has('price_range')) {
            $query->where('price_range', $request->input('price_range'));
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'rating' => $query->orderByDesc('rating')->orderByDesc('review_count'),
            'popular' => $query->orderByDesc('review_count'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(),
        };

        $perPage = min((int) $request->input('per_page', 12), 50);
        $businesses = $query->paginate($perPage);

        return $this->successResponse([
            'businesses' => BusinessResource::collection($businesses),
            'pagination' => [
                'current_page' => $businesses->currentPage(),
                'last_page' => $businesses->lastPage(),
                'per_page' => $businesses->perPage(),
                'total' => $businesses->total(),
            ],
        ], 'Public businesses retrieved successfully.');
    }

    /**
     * Show public business details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $business = Business::with([
            'category',
            'province',
            'images',
            'services' => fn ($q) => $q->where('is_available', true),
            'hours',
            'promotions' => fn ($q) => $q->active(),
        ])->find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        // Only approved & active businesses are visible publicly, unless viewed by owner or admin
        $user = $request->user();
        if (!$business->isApproved() || !$business->isActive()) {
            if (!$user || ($user->id !== $business->owner_id && !$user->isAdmin())) {
                return $this->errorResponse('This business is currently not available publicly.', 403);
            }
        }

        return $this->successResponse(
            new BusinessDetailResource($business),
            'Business details retrieved successfully.'
        );
    }

    /**
     * Get services for a public business.
     */
    public function services(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business || (!$business->isApproved() || !$business->isActive())) {
            return $this->errorResponse('Business not found or unavailable.', 404);
        }

        $services = $business->services()->where('is_available', true)->get();

        return $this->successResponse(
            BusinessServiceResource::collection($services),
            'Business services retrieved successfully.'
        );
    }

    /**
     * Get opening hours for a public business.
     */
    public function hours(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business || (!$business->isApproved() || !$business->isActive())) {
            return $this->errorResponse('Business not found or unavailable.', 404);
        }

        return $this->successResponse(
            BusinessHourResource::collection($business->hours),
            'Business hours retrieved successfully.'
        );
    }

    /**
     * Get gallery/images for a public business.
     */
    public function gallery(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business || (!$business->isApproved() || !$business->isActive())) {
            return $this->errorResponse('Business not found or unavailable.', 404);
        }

        return $this->successResponse(
            BusinessImageResource::collection($business->images),
            'Business gallery retrieved successfully.'
        );
    }

    /**
     * Get promotions for a public business.
     */
    public function promotions(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business || (!$business->isApproved() || !$business->isActive())) {
            return $this->errorResponse('Business not found or unavailable.', 404);
        }

        $promotions = $business->promotions()->active()->get();

        return $this->successResponse(
            BusinessPromotionResource::collection($promotions),
            'Business promotions retrieved successfully.'
        );
    }

    /**
     * Get upcoming events for a public business.
     */
    public function events(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business || (!$business->isApproved() || !$business->isActive())) {
            return $this->errorResponse('Business not found or unavailable.', 404);
        }

        $events = Event::where('business_id', $business->id)->latest()->get();

        return $this->successResponse(
            EventResource::collection($events),
            'Business events retrieved successfully.'
        );
    }

    /**
     * Get reviews for a public business.
     */
    public function reviews(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business || (!$business->isApproved() || !$business->isActive())) {
            return $this->errorResponse('Business not found or unavailable.', 404);
        }

        $reviews = Review::with(['user', 'replies.user', 'images'])
            ->where('business_id', $business->id)
            ->where('status', 'Approved')
            ->latest()
            ->paginate((int) $request->input('per_page', 15));

        return $this->successResponse([
            'reviews' => ReviewResource::collection($reviews),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ], 'Business reviews retrieved successfully.');
    }

    /**
     * Authenticated tourist reviews a business.
     */
    public function storeReview(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        if (!$business->isApproved() || !$business->isActive()) {
            return $this->errorResponse('Cannot review an inactive or unapproved business.', 400);
        }

        $user = $request->user();

        // Business owner cannot review their own business
        if ($business->owner_id === $user->id) {
            return $this->errorResponse('You cannot submit a review for your own business.', 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'comment' => ['required', 'string', 'max:2000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['nullable', 'string'],
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'],
            'status' => 'Approved', // Auto-approved for verified experience or pending
        ]);

        // Upload review images if provided
        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $imgUrl) {
                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_url' => $imgUrl,
                ]);
            }
        }

        // Recalculate business rating
        $business->recalculateRating();

        // Notify Business Owner
        if ($business->owner_id) {
            Notification::createNotification([
                'user_id' => $business->owner_id,
                'type' => 'business_review',
                'category' => 'Review',
                'title' => 'New Review on Your Business',
                'description' => "{$user->name} left a {$review->rating}-star review for '{$business->name}'.",
                'link' => "/business/businesses/{$business->id}/reviews",
                'data' => [
                    'business_id' => $business->id,
                    'review_id' => $review->id,
                    'rating' => $review->rating,
                ],
            ]);
        }

        AuditLogger::log(
            action: 'business.review_submitted',
            entityType: 'Review',
            entityId: $review->id,
            description: "User {$user->name} reviewed business '{$business->name}' with rating {$review->rating}/5.",
            newValues: $review->toArray()
        );

        $review->load(['user', 'images']);

        return $this->successResponse(
            new ReviewResource($review),
            'Your review has been submitted successfully.',
            201
        );
    }
}
