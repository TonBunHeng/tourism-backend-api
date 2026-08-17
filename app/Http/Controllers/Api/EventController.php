<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventTag;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['place', 'province', 'tags']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('organizer', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($provinceId = $request->query('province_id')) {
            $query->where('province_id', $provinceId);
        }

        if ($placeId = $request->query('place_id')) {
            $query->where('place_id', $placeId);
        }

        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN);
            $query->where('featured', $featured);
        }

        $perPage = (int) $request->query('per_page', 10);
        $events = $query->orderBy('start_date', 'asc')->paginate($perPage);

        return $this->successResponse(EventResource::collection($events), 'Events retrieved successfully.', 200, [
            'total' => $events->total(),
            'per_page' => $events->perPage(),
            'current_page' => $events->currentPage(),
            'last_page' => $events->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'place_id' => 'nullable|exists:places,id',
            'province_id' => 'nullable|exists:provinces,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|string|max:20',
            'attendees_count' => 'integer|min:0',
            'price' => 'nullable|string|max:50',
            'organizer' => 'nullable|string|max:150',
            'featured' => 'boolean',
            'rating' => 'numeric|min:0|max:5',
            'image_url' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(['Upcoming', 'Ongoing', 'Completed', 'Cancelled', 'Scheduled'])],
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $event = Event::create($validated);

        foreach ($tags as $tagName) {
            EventTag::create([
                'event_id' => $event->id,
                'tag_name' => $tagName,
            ]);
        }

        $event->load(['place', 'province', 'tags']);

        return $this->successResponse(new EventResource($event), 'Event created successfully.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $event = Event::with(['place', 'province', 'tags'])->find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        return $this->successResponse(new EventResource($event), 'Event details retrieved successfully.');
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:200',
            'category' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'place_id' => 'nullable|exists:places,id',
            'province_id' => 'nullable|exists:provinces,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|string|max:20',
            'attendees_count' => 'sometimes|integer|min:0',
            'price' => 'nullable|string|max:50',
            'organizer' => 'nullable|string|max:150',
            'featured' => 'sometimes|boolean',
            'rating' => 'sometimes|numeric|min:0|max:5',
            'image_url' => 'nullable|string|max:255',
            'status' => ['sometimes', Rule::in(['Upcoming', 'Ongoing', 'Completed', 'Cancelled', 'Scheduled'])],
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        if (array_key_exists('tags', $validated)) {
            $tags = $validated['tags'] ?? [];
            unset($validated['tags']);

            EventTag::where('event_id', $event->id)->delete();
            foreach ($tags as $tagName) {
                EventTag::create([
                    'event_id' => $event->id,
                    'tag_name' => $tagName,
                ]);
            }
        }

        $event->update($validated);
        $event->load(['place', 'province', 'tags']);

        return $this->successResponse(new EventResource($event), 'Event updated successfully.');
    }

    public function destroy(string $id): JsonResponse
    {
        $event = Event::find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        $event->delete();

        return $this->successResponse(null, 'Event deleted successfully.');
    }
}
