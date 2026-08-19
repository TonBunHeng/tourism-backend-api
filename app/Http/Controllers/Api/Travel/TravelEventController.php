<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Resources\Travel\TravelEventResource;
use App\Models\Event;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelEventController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['place', 'province', 'tags'])
            ->where('status', '!=', 'Cancelled');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('organizer', 'like', "%{$search}%");
            });
        }

        if ($provinceId = $request->query('province_id')) {
            $query->where('province_id', $provinceId);
        }

        if ($category = $request->query('category')) {
            if ($category !== 'All') {
                $query->where('category', $category);
            }
        }

        if ($status = $request->query('status')) {
            $today = Carbon::today()->toDateString();
            if ($status === 'Upcoming') {
                $query->where('start_date', '>', $today);
            } elseif ($status === 'Ongoing') {
                $query->where('start_date', '<=', $today)
                      ->where(function ($q) use ($today) {
                          $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
                      });
            } elseif ($status === 'Past' || $status === 'Completed') {
                $query->where(function ($q) use ($today) {
                    $q->whereNotNull('end_date')->where('end_date', '<', $today)
                      ->orWhere(function ($q2) use ($today) {
                          $q2->whereNull('end_date')->where('start_date', '<', $today);
                      });
                });
            }
        }

        if ($startDate = $request->query('start_date')) {
            $query->where('start_date', '>=', $startDate);
        }

        if ($endDate = $request->query('end_date')) {
            $query->where('end_date', '<=', $endDate);
        }

        if ($request->has('featured')) {
            $featured = filter_var($request->query('featured'), FILTER_VALIDATE_BOOLEAN);
            $query->where('featured', $featured);
        }

        $perPage = (int) $request->query('per_page', 10);
        $events = $query->orderBy('start_date', 'asc')->paginate($perPage);

        return $this->successResponse(
            TravelEventResource::collection($events),
            'Events retrieved successfully.',
            200,
            [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $event = Event::with(['place', 'province', 'tags'])->find($id);

        if (!$event) {
            return $this->errorResponse('Event not found.', 404);
        }

        return $this->successResponse(
            new TravelEventResource($event),
            'Event details retrieved successfully.'
        );
    }
}
