<?php

namespace App\Http\Controllers\Api\Guide;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\GalleryMediaResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\ReviewResource;
use App\Models\Event;
use App\Models\GalleryMedia;
use App\Models\Place;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuideController extends Controller
{
    use ApiResponse;

    /**
     * Get Guide Dashboard overview and metrics.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalPlaces = Place::count();
        $totalEvents = Event::count();
        $totalMedia = GalleryMedia::where('uploaded_by_user_id', $user->id)->count();
        $pendingReviews = Review::where('status', 'Pending')->count();

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar' => $user->avatar,
            ],
            'places_count' => $totalPlaces,
            'events_count' => $totalEvents,
            'media_count' => $totalMedia,
            'pending_reviews_count' => $pendingReviews,
            'inquiries_count' => 0,
        ], 'Guide dashboard overview retrieved successfully.');
    }

    /**
     * List places for guide management.
     */
    public function places(Request $request): JsonResponse
    {
        $query = Place::with(['category', 'province']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $places = $query->latest()->paginate((int) $request->input('per_page', 15));

        return $this->successResponse([
            'places' => PlaceResource::collection($places),
            'pagination' => [
                'current_page' => $places->currentPage(),
                'last_page' => $places->lastPage(),
                'per_page' => $places->perPage(),
                'total' => $places->total(),
            ],
        ], 'Places retrieved successfully.');
    }

    /**
     * Create a new place (Guide/Editor).
     */
    public function storePlace(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'province_id' => ['required', 'exists:provinces,id'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'entrance_fee' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'cover_image_url' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['address'] = $validated['address'] ?? '';

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('places', 'public');
            $validated['cover_image_url'] = Storage::url($path);
        }

        $place = Place::create($validated);
        $place->load(['category', 'province']);

        AuditLogger::log(
            action: 'place.created',
            entityType: 'Place',
            entityId: $place->id,
            description: "Place '{$place->name}' was created by guide {$request->user()->name}.",
            newValues: $place->toArray()
        );

        return $this->successResponse(
            new PlaceResource($place),
            'Place created successfully by Guide/Editor.',
            201
        );
    }

    /**
     * Show single place details.
     */
    public function showPlace(Request $request, $id): JsonResponse
    {
        $place = Place::with(['category', 'province', 'events', 'reviews'])->find($id);

        if (!$place) {
            return $this->errorResponse('Place not found.', 404);
        }

        return $this->successResponse(
            new PlaceResource($place),
            'Place details retrieved successfully.'
        );
    }

    /**
     * Update an existing place.
     */
    public function updatePlace(Request $request, $id): JsonResponse
    {
        $place = Place::find($id);

        if (!$place) {
            return $this->errorResponse('Place not found.', 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'province_id' => ['sometimes', 'required', 'exists:provinces,id'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'opening_hours' => ['nullable', 'string', 'max:255'],
            'entrance_fee' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'cover_image_url' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('places', 'public');
            $validated['cover_image_url'] = Storage::url($path);
        }

        $oldValues = $place->toArray();
        $place->update($validated);
        $place->load(['category', 'province']);

        AuditLogger::log(
            action: 'place.updated',
            entityType: 'Place',
            entityId: $place->id,
            description: "Place '{$place->name}' was updated by guide {$request->user()->name}.",
            oldValues: $oldValues,
            newValues: $place->toArray()
        );

        return $this->successResponse(
            new PlaceResource($place),
            'Place updated successfully.'
        );
    }

    /**
     * List events for guide management.
     */
    public function events(Request $request): JsonResponse
    {
        $events = Event::with(['province', 'place', 'business'])
            ->latest()
            ->paginate((int) $request->input('per_page', 15));

        return $this->successResponse([
            'events' => EventResource::collection($events),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ], 'Events retrieved successfully.');
    }

    /**
     * Create tourism event.
     */
    public function storeEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'place_id' => ['nullable', 'exists:places,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'price' => ['nullable', 'string', 'max:50'],
            'image_url' => ['nullable', 'string'],
            'organizer' => ['nullable', 'string', 'max:150'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $validated['location'] = $validated['location'] ?? '';
        $validated['category'] = $validated['category'] ?? 'General';
        $validated['start_date'] = $validated['start_date'] ?? now()->toDateString();

        $event = Event::create($validated);

        AuditLogger::log(
            action: 'event.created',
            entityType: 'Event',
            entityId: $event->id,
            description: "Event '{$event->title}' created by guide {$request->user()->name}.",
            newValues: $event->toArray()
        );

        return $this->successResponse(
            new EventResource($event),
            'Tourism event created successfully.',
            201
        );
    }

    /**
     * Show single event details.
     */
    public function showEvent(Request $request, $id): JsonResponse
    {
        $event = Event::with(['province', 'place', 'business'])->find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        return $this->successResponse(
            new EventResource($event),
            'Event details retrieved successfully.'
        );
    }

    /**
     * Update tourism event.
     */
    public function updateEvent(Request $request, $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'place_id' => ['nullable', 'exists:places,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'price' => ['nullable', 'string', 'max:50'],
            'image_url' => ['nullable', 'string'],
            'organizer' => ['nullable', 'string', 'max:150'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $oldValues = $event->toArray();
        $event->update($validated);

        AuditLogger::log(
            action: 'event.updated',
            entityType: 'Event',
            entityId: $event->id,
            description: "Event '{$event->title}' updated by guide {$request->user()->name}.",
            oldValues: $oldValues,
            newValues: $event->toArray()
        );

        return $this->successResponse(
            new EventResource($event),
            'Event updated successfully.'
        );
    }

    /**
     * List galleries/media.
     */
    public function galleries(Request $request): JsonResponse
    {
        $query = GalleryMedia::with(['uploader', 'province', 'place']);

        if ($request->has('province_id')) {
            $query->where('province_id', $request->input('province_id'));
        }

        $galleries = $query->latest()->paginate((int) $request->input('per_page', 15));

        return $this->successResponse([
            'galleries' => GalleryMediaResource::collection($galleries),
            'pagination' => [
                'current_page' => $galleries->currentPage(),
                'last_page' => $galleries->lastPage(),
                'per_page' => $galleries->perPage(),
                'total' => $galleries->total(),
            ],
        ], 'Gallery media retrieved successfully.');
    }

    /**
     * Upload gallery media.
     */
    public function storeGallery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_type' => ['nullable', 'string', 'in:image,video'],
            'media_url' => ['nullable', 'string'],
            'province_id' => ['nullable', 'exists:provinces,id'],
            'place_id' => ['nullable', 'exists:places,id'],
        ]);

        $user = $request->user();
        $validated['uploaded_by_user_id'] = $user->id;
        $validated['media_type'] = $validated['media_type'] ?? 'image';

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('galleries', 'public');
            $validated['media_url'] = Storage::url($path);
        }

        if (empty($validated['media_url'])) {
            return $this->errorResponse('A valid media file or media_url is required.', 422);
        }

        $gallery = GalleryMedia::create($validated);
        $gallery->load(['uploader', 'province', 'place']);

        AuditLogger::log(
            action: 'gallery.uploaded',
            entityType: 'GalleryMedia',
            entityId: $gallery->id,
            description: "Gallery media '{$gallery->title}' uploaded by guide {$user->name}.",
            newValues: $gallery->toArray()
        );

        return $this->successResponse(
            new GalleryMediaResource($gallery),
            'Gallery media uploaded successfully.',
            201
        );
    }

    /**
     * Delete gallery media.
     */
    public function destroyGallery(Request $request, $id): JsonResponse
    {
        $gallery = GalleryMedia::find($id);

        if (!$gallery) {
            return $this->errorResponse('Gallery media not found.', 404);
        }

        $oldValues = $gallery->toArray();
        $gallery->delete();

        AuditLogger::log(
            action: 'gallery.deleted',
            entityType: 'GalleryMedia',
            entityId: (int) $id,
            description: "Gallery media '{$gallery->title}' deleted by guide {$request->user()->name}.",
            oldValues: $oldValues
        );

        return $this->successResponse(null, 'Gallery media deleted successfully.');
    }

    /**
     * List reviews for guide assistance & moderation inspection.
     */
    public function reviews(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'place', 'business', 'replies.user']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->latest()->paginate((int) $request->input('per_page', 15));

        return $this->successResponse([
            'reviews' => ReviewResource::collection($reviews),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ], 'Reviews retrieved successfully.');
    }

    /**
     * Reply to a review as a Guide/Editor assistant.
     */
    public function replyReview(Request $request, $id): JsonResponse
    {
        $review = Review::find($id);

        if (!$review) {
            return $this->errorResponse('Review not found.', 404);
        }

        $validated = $request->validate([
            'reply' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $reply = ReviewReply::create([
            'review_id' => $review->id,
            'user_id' => $user->id,
            'reply' => $validated['reply'],
        ]);

        $reply->load('user');

        AuditLogger::log(
            action: 'guide.review_replied',
            entityType: 'ReviewReply',
            entityId: $reply->id,
            description: "Guide {$user->name} posted official assistance reply to review #{$review->id}.",
            newValues: $reply->toArray()
        );

        return $this->successResponse($reply, 'Guide reply posted successfully.', 201);
    }

    /**
     * Delete place (Guide/Editor or Admin).
     */
    public function destroyPlace(Request $request, $id): JsonResponse
    {
        $place = Place::find($id);

        if (!$place) {
            return $this->errorResponse('Place not found.', 404);
        }

        $oldValues = $place->toArray();
        $placeName = $place->name;

        $place->delete();

        AuditLogger::log(
            action: 'place.deleted',
            entityType: 'Place',
            entityId: (int) $id,
            description: "Place '{$placeName}' deleted by {$request->user()->name}.",
            oldValues: $oldValues
        );

        return $this->successResponse(null, "Place '{$placeName}' deleted successfully.");
    }

    /**
     * Delete event (Guide/Editor or Admin).
     */
    public function destroyEvent(Request $request, $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        $oldValues = $event->toArray();
        $eventTitle = $event->title;

        $event->delete();

        AuditLogger::log(
            action: 'event.deleted',
            entityType: 'Event',
            entityId: (int) $id,
            description: "Event '{$eventTitle}' deleted by {$request->user()->name}.",
            oldValues: $oldValues
        );

        return $this->successResponse(null, "Event '{$eventTitle}' deleted successfully.");
    }
}
