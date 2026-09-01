<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\BusinessReviewReplyRequest;
use App\Http\Requests\Business\StoreBusinessImageRequest;
use App\Http\Requests\Business\StoreBusinessPromotionRequest;
use App\Http\Requests\Business\StoreBusinessRequest;
use App\Http\Requests\Business\StoreBusinessServiceRequest;
use App\Http\Requests\Business\UpdateBusinessHoursRequest;
use App\Http\Requests\Business\UpdateBusinessPromotionRequest;
use App\Http\Requests\Business\UpdateBusinessRequest;
use App\Http\Requests\Business\UpdateBusinessServiceRequest;
use App\Http\Resources\BusinessDetailResource;
use App\Http\Resources\BusinessHourResource;
use App\Http\Resources\BusinessImageResource;
use App\Http\Resources\BusinessPromotionResource;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\BusinessServiceResource;
use App\Http\Resources\ReviewResource;
use App\Models\Business;
use App\Models\BusinessHour;
use App\Models\BusinessImage;
use App\Models\BusinessPromotion;
use App\Models\BusinessService;
use App\Models\Event;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BusinessOwnerController extends Controller
{
    use ApiResponse;

    /**
     * Get profile and owned businesses overview.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $businesses = Business::with(['category', 'province', 'images'])
            ->where('owner_id', $user->id)
            ->get();

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => $user->status,
                'avatar' => $user->avatar,
            ],
            'businesses_count' => $businesses->count(),
            'businesses' => BusinessResource::collection($businesses),
        ], 'Business owner profile retrieved successfully.');
    }

    /**
     * Get list of businesses owned by authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Business::with(['category', 'province', 'images'])
            ->withCount(['images', 'services', 'promotions', 'reviews']);

        if (!$user->isAdmin()) {
            $query->where('owner_id', $user->id);
        }

        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->input('verification_status'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 15), 50);
        $businesses = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'businesses' => BusinessResource::collection($businesses),
            'pagination' => [
                'current_page' => $businesses->currentPage(),
                'last_page' => $businesses->lastPage(),
                'per_page' => $businesses->perPage(),
                'total' => $businesses->total(),
            ],
        ], 'Owned businesses retrieved successfully.');
    }

    /**
     * Create a new business.
     */
    public function store(StoreBusinessRequest $request): JsonResponse
    {
        $user = $request->user();

        if (Gate::has('create', Business::class) && !Gate::allows('create', Business::class)) {
            return $this->errorResponse('Access denied. You do not have permission to create a business.', 403);
        }

        $validated = $request->validated();
        $validated['owner_id'] = $user->id;
        $validated['status'] = 'active';
        $validated['verification_status'] = 'pending';

        $business = Business::create($validated);
        $business->load(['category', 'province', 'owner']);

        AuditLogger::log(
            action: 'business.created',
            entityType: 'Business',
            entityId: $business->id,
            description: "Business '{$business->name}' was created by {$user->name} and is pending verification.",
            newValues: $business->toArray()
        );

        return $this->successResponse(
            new BusinessDetailResource($business),
            'Business created successfully. It is currently pending verification by administrators.',
            201
        );
    }

    /**
     * Show single business details.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $business = Business::with(['category', 'province', 'owner', 'images', 'services', 'hours', 'promotions'])->find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied. You do not own this business.', 403);
        }

        return $this->successResponse(
            new BusinessDetailResource($business),
            'Business details retrieved successfully.'
        );
    }

    /**
     * Update own business.
     */
    public function update(UpdateBusinessRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied. You cannot modify another owner\'s business.', 403);
        }

        $oldValues = $business->toArray();
        $validated = $request->validated();

        $business->update($validated);
        $business->load(['category', 'province', 'owner', 'images', 'services', 'hours', 'promotions']);

        AuditLogger::log(
            action: 'business.updated',
            entityType: 'Business',
            entityId: $business->id,
            description: "Business '{$business->name}' was updated by {$user->name}.",
            oldValues: $oldValues,
            newValues: $business->toArray()
        );

        return $this->successResponse(
            new BusinessDetailResource($business),
            'Business updated successfully.'
        );
    }

    /**
     * Delete own business.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied. You cannot delete another owner\'s business.', 403);
        }

        $businessName = $business->name;
        $businessId = $business->id;
        $oldValues = $business->toArray();

        $business->delete();

        AuditLogger::log(
            action: 'business.deleted',
            entityType: 'Business',
            entityId: $businessId,
            description: "Business '{$businessName}' was deleted by {$user->name}.",
            oldValues: $oldValues
        );

        return $this->successResponse(null, "Business '{$businessName}' deleted successfully.");
    }

    /**
     * Get images for a business.
     */
    public function images(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        return $this->successResponse(
            BusinessImageResource::collection($business->images),
            'Business images retrieved successfully.'
        );
    }

    /**
     * Store image for a business.
     */
    public function storeImage(StoreBusinessImageRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied. You cannot upload images to another owner\'s business.', 403);
        }

        $imageUrl = $request->input('image_url');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('businesses/images', 'public');
            $imageUrl = Storage::url($path);
        }

        if (!$imageUrl) {
            return $this->errorResponse('An image file or valid image_url is required.', 422);
        }

        $isCover = (bool) $request->input('is_cover', false);
        if ($isCover) {
            $business->images()->update(['is_cover' => false]);
        }

        $image = BusinessImage::create([
            'business_id' => $business->id,
            'image_url' => $imageUrl,
            'caption' => $request->input('caption'),
            'is_cover' => $isCover,
            'display_order' => (int) $request->input('display_order', 0),
        ]);

        AuditLogger::log(
            action: 'business.image_uploaded',
            entityType: 'BusinessImage',
            entityId: $image->id,
            description: "Image uploaded for business '{$business->name}'.",
            newValues: $image->toArray()
        );

        return $this->successResponse(
            new BusinessImageResource($image),
            'Business image uploaded successfully.',
            201
        );
    }

    /**
     * Delete an image from a business.
     */
    public function destroyImage(Request $request, $id, $imageId): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $image = BusinessImage::where('business_id', $business->id)->find($imageId);

        if (!$image) {
            return $this->errorResponse('Business image not found.', 404);
        }

        $oldValues = $image->toArray();
        $image->delete();

        AuditLogger::log(
            action: 'business.image_deleted',
            entityType: 'BusinessImage',
            entityId: (int) $imageId,
            description: "Image deleted from business '{$business->name}'.",
            oldValues: $oldValues
        );

        return $this->successResponse(null, 'Business image deleted successfully.');
    }

    /**
     * Get services for a business.
     */
    public function services(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        return $this->successResponse(
            BusinessServiceResource::collection($business->services),
            'Business services retrieved successfully.'
        );
    }

    /**
     * Add service to business.
     */
    public function storeService(StoreBusinessServiceRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $validated = $request->validated();
        $validated['business_id'] = $business->id;

        $service = BusinessService::create($validated);

        AuditLogger::log(
            action: 'business.service_created',
            entityType: 'BusinessService',
            entityId: $service->id,
            description: "Service '{$service->name}' added to business '{$business->name}'.",
            newValues: $service->toArray()
        );

        return $this->successResponse(
            new BusinessServiceResource($service),
            'Business service created successfully.',
            201
        );
    }

    /**
     * Update service.
     */
    public function updateService(UpdateBusinessServiceRequest $request, $id, $serviceId): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $service = BusinessService::where('business_id', $business->id)->find($serviceId);

        if (!$service) {
            return $this->errorResponse('Business service not found.', 404);
        }

        $oldValues = $service->toArray();
        $service->update($request->validated());

        AuditLogger::log(
            action: 'business.service_updated',
            entityType: 'BusinessService',
            entityId: $service->id,
            description: "Service '{$service->name}' updated for business '{$business->name}'.",
            oldValues: $oldValues,
            newValues: $service->toArray()
        );

        return $this->successResponse(
            new BusinessServiceResource($service),
            'Business service updated successfully.'
        );
    }

    /**
     * Delete service.
     */
    public function destroyService(Request $request, $id, $serviceId): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $service = BusinessService::where('business_id', $business->id)->find($serviceId);

        if (!$service) {
            return $this->errorResponse('Business service not found.', 404);
        }

        $oldValues = $service->toArray();
        $service->delete();

        AuditLogger::log(
            action: 'business.service_deleted',
            entityType: 'BusinessService',
            entityId: (int) $serviceId,
            description: "Service deleted from business '{$business->name}'.",
            oldValues: $oldValues
        );

        return $this->successResponse(null, 'Business service deleted successfully.');
    }

    /**
     * Get opening hours.
     */
    public function hours(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        return $this->successResponse(
            BusinessHourResource::collection($business->hours),
            'Business opening hours retrieved successfully.'
        );
    }

    /**
     * Update opening hours (batch).
     */
    public function updateHours(UpdateBusinessHoursRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $hoursData = $request->input('hours', []);

        foreach ($hoursData as $item) {
            BusinessHour::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'day_of_week' => $item['day_of_week'],
                ],
                [
                    'open_time' => $item['open_time'] ?? null,
                    'close_time' => $item['close_time'] ?? null,
                    'is_closed' => (bool) ($item['is_closed'] ?? false),
                ]
            );
        }

        $business->load('hours');

        AuditLogger::log(
            action: 'business.hours_updated',
            entityType: 'Business',
            entityId: $business->id,
            description: "Opening hours updated for business '{$business->name}'."
        );

        return $this->successResponse(
            BusinessHourResource::collection($business->hours),
            'Business opening hours updated successfully.'
        );
    }

    /**
     * Get promotions.
     */
    public function promotions(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        return $this->successResponse(
            BusinessPromotionResource::collection($business->promotions),
            'Business promotions retrieved successfully.'
        );
    }

    /**
     * Store promotion.
     */
    public function storePromotion(StoreBusinessPromotionRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $validated = $request->validated();
        $validated['business_id'] = $business->id;

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('businesses/promotions', 'public');
            $validated['banner_url'] = Storage::url($path);
        }

        $promotion = BusinessPromotion::create($validated);

        AuditLogger::log(
            action: 'business.promotion_created',
            entityType: 'BusinessPromotion',
            entityId: $promotion->id,
            description: "Promotion '{$promotion->title}' created for business '{$business->name}'.",
            newValues: $promotion->toArray()
        );

        return $this->successResponse(
            new BusinessPromotionResource($promotion),
            'Business promotion created successfully.',
            201
        );
    }

    /**
     * Update promotion.
     */
    public function updatePromotion(UpdateBusinessPromotionRequest $request, $id, $promoId): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $promotion = BusinessPromotion::where('business_id', $business->id)->find($promoId);

        if (!$promotion) {
            return $this->errorResponse('Business promotion not found.', 404);
        }

        $validated = $request->validated();

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('businesses/promotions', 'public');
            $validated['banner_url'] = Storage::url($path);
        }

        $oldValues = $promotion->toArray();
        $promotion->update($validated);

        AuditLogger::log(
            action: 'business.promotion_updated',
            entityType: 'BusinessPromotion',
            entityId: $promotion->id,
            description: "Promotion '{$promotion->title}' updated for business '{$business->name}'.",
            oldValues: $oldValues,
            newValues: $promotion->toArray()
        );

        return $this->successResponse(
            new BusinessPromotionResource($promotion),
            'Business promotion updated successfully.'
        );
    }

    /**
     * Delete promotion.
     */
    public function destroyPromotion(Request $request, $id, $promoId): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $promotion = BusinessPromotion::where('business_id', $business->id)->find($promoId);

        if (!$promotion) {
            return $this->errorResponse('Business promotion not found.', 404);
        }

        $oldValues = $promotion->toArray();
        $promotion->delete();

        AuditLogger::log(
            action: 'business.promotion_deleted',
            entityType: 'BusinessPromotion',
            entityId: (int) $promoId,
            description: "Promotion deleted from business '{$business->name}'.",
            oldValues: $oldValues
        );

        return $this->successResponse(null, 'Business promotion deleted successfully.');
    }

    /**
     * Get reviews for own business.
     */
    public function reviews(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $reviews = Review::with(['user', 'replies.user', 'images'])
            ->where('business_id', $business->id)
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
     * Reply to a review for own business.
     */
    public function replyReview(BusinessReviewReplyRequest $request, $id, $reviewId): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied. You cannot reply to reviews on another owner\'s business.', 403);
        }

        $review = Review::where('business_id', $business->id)->find($reviewId);

        if (!$review) {
            return $this->errorResponse('Review not found for this business.', 404);
        }

        $reply = ReviewReply::create([
            'review_id' => $review->id,
            'user_id' => $user->id,
            'reply' => $request->input('reply'),
        ]);

        $reply->load('user');

        AuditLogger::log(
            action: 'business.review_replied',
            entityType: 'ReviewReply',
            entityId: $reply->id,
            description: "Owner {$user->name} replied to review #{$review->id} on business '{$business->name}'.",
            newValues: $reply->toArray()
        );

        return $this->successResponse($reply, 'Reply posted successfully.', 201);
    }

    /**
     * Get statistics for own business.
     */
    public function statistics(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied. You cannot view another owner\'s statistics.', 403);
        }

        $totalReviews = Review::where('business_id', $business->id)->count();
        $approvedReviews = Review::where('business_id', $business->id)->where('status', 'Approved')->count();
        $avgRating = Review::where('business_id', $business->id)->where('status', 'Approved')->avg('rating') ?: 0.0;
        $totalServices = BusinessService::where('business_id', $business->id)->count();
        $activePromotions = BusinessPromotion::where('business_id', $business->id)->active()->count();
        $totalImages = BusinessImage::where('business_id', $business->id)->count();
        $totalEvents = Event::where('business_id', $business->id)->count();

        return $this->successResponse([
            'business_id' => $business->id,
            'name' => $business->name,
            'verification_status' => $business->verification_status,
            'status' => $business->status,
            'rating' => round((float)$avgRating, 2),
            'total_reviews' => $totalReviews,
            'approved_reviews' => $approvedReviews,
            'total_services' => $totalServices,
            'active_promotions' => $activePromotions,
            'total_images' => $totalImages,
            'total_events' => $totalEvents,
        ], 'Business statistics retrieved successfully.');
    }

    /**
     * Get events for own business.
     */
    public function events(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $events = Event::where('business_id', $business->id)->latest()->get();

        return $this->successResponse($events, 'Business events retrieved successfully.');
    }

    /**
     * Create event for own business.
     */
    public function storeEvent(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $user = $request->user();
        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'price' => ['nullable', 'string', 'max:50'],
            'image_url' => ['nullable', 'string'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['organizer'] = $business->name;
        $validated['province_id'] = $business->province_id;

        $event = Event::create($validated);

        AuditLogger::log(
            action: 'business.event_created',
            entityType: 'Event',
            entityId: $event->id,
            description: "Event '{$event->title}' created for business '{$business->name}'.",
            newValues: $event->toArray()
        );

        return $this->successResponse($event, 'Business event created successfully.', 201);
    }
}
