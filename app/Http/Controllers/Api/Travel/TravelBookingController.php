<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\CancelBookingRequest;
use App\Http\Requests\Travel\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Business;
use App\Models\BusinessService;
use App\Models\Notification;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelBookingController extends Controller
{
    use ApiResponse;

    /**
     * List authenticated traveler's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Booking::with(['business.province', 'business.category', 'business.images', 'service'])
            ->where('user_id', $user->id);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($businessId = $request->input('business_id')) {
            $query->where('business_id', $businessId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('booking_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('booking_date', '<=', $dateTo);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('business', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'date_asc' => $query->orderBy('booking_date', 'asc')->orderBy('booking_time', 'asc'),
            'date_desc' => $query->orderBy('booking_date', 'desc')->orderBy('booking_time', 'desc'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $perPage = min((int) $request->input('per_page', 15), 50);
        $bookings = $query->paginate($perPage);

        $statistics = [
            'total' => Booking::where('user_id', $user->id)->count(),
            'pending' => Booking::where('user_id', $user->id)->where('status', Booking::STATUS_PENDING)->count(),
            'confirmed' => Booking::where('user_id', $user->id)->where('status', Booking::STATUS_CONFIRMED)->count(),
            'completed' => Booking::where('user_id', $user->id)->where('status', Booking::STATUS_COMPLETED)->count(),
            'cancelled' => Booking::where('user_id', $user->id)->where('status', Booking::STATUS_CANCELLED)->count(),
            'rejected' => Booking::where('user_id', $user->id)->where('status', Booking::STATUS_REJECTED)->count(),
        ];

        return $this->successResponse([
            'bookings' => BookingResource::collection($bookings),
            'statistics' => $statistics,
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ], 'Travel bookings retrieved successfully.');
    }

    /**
     * Create a new booking for a business.
     */
    public function store(StoreBookingRequest $request, ?int $id = null): JsonResponse
    {
        $user = $request->user();
        $businessId = $id ?? $request->input('business_id');

        if (!$businessId) {
            return $this->errorResponse('A valid business_id is required.', 422);
        }

        $business = Business::find($businessId);
        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        if (!$business->isActive() || !$business->isApproved()) {
            return $this->errorResponse('This business is currently not accepting bookings.', 400);
        }

        if ($business->owner_id === $user->id) {
            return $this->errorResponse('You cannot book your own business.', 422);
        }

        $service = null;
        if ($serviceId = $request->input('service_id')) {
            $service = BusinessService::find($serviceId);
            if (!$service || $service->business_id !== $business->id) {
                return $this->errorResponse('The selected service does not belong to this business.', 422);
            }
            if (!$service->is_available) {
                return $this->errorResponse('The selected service is currently unavailable for booking.', 422);
            }
        }

        $guests = max(1, (int) $request->input('number_of_guests', 1));

        $totalPrice = 0.00;
        if ($request->has('total_price') && $request->input('total_price') !== null) {
            $totalPrice = (float) $request->input('total_price');
        } elseif ($service && $service->price !== null) {
            $totalPrice = (float) ($service->price * $guests);
        }

        $currency = $request->input('currency') ?: ($service?->currency ?: 'USD');

        $booking = Booking::create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'service_id' => $service?->id,
            'customer_name' => $request->input('customer_name') ?: $user->name,
            'customer_email' => $request->input('customer_email') ?: $user->email,
            'customer_phone' => $request->input('customer_phone') ?: $user->phone,
            'booking_date' => $request->input('booking_date'),
            'booking_time' => $request->input('booking_time'),
            'number_of_guests' => $guests,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'status' => Booking::STATUS_PENDING,
            'payment_status' => Booking::PAYMENT_UNPAID,
            'special_requests' => $request->input('special_requests'),
        ]);

        // 1. Notify the Business Owner
        if ($business->owner_id) {
            Notification::createNotification([
                'user_id' => $business->owner_id,
                'type' => 'booking_received',
                'category' => 'Booking',
                'title' => 'New Booking Request',
                'description' => "New booking (#{$booking->booking_reference}) received from {$booking->customer_name} for {$business->name}.",
                'link' => "/business/bookings/{$booking->id}",
                'data' => [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'customer_name' => $booking->customer_name,
                    'booking_date' => $booking->booking_date?->format('Y-m-d'),
                    'guests' => $booking->number_of_guests,
                    'total_price' => $booking->total_price,
                ],
            ]);
        }

        // 2. Notify the Traveler
        Notification::createNotification([
            'user_id' => $user->id,
            'type' => 'booking_created',
            'category' => 'Booking',
            'title' => 'Booking Request Submitted',
            'description' => "Your booking request (#{$booking->booking_reference}) for {$business->name} has been placed and is awaiting confirmation.",
            'link' => "/bookings/{$booking->id}",
            'data' => [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'business_id' => $business->id,
                'business_name' => $business->name,
                'booking_date' => $booking->booking_date?->format('Y-m-d'),
            ],
        ]);

        // Audit Log
        AuditLogger::log(
            action: 'booking_created',
            entityType: 'Booking',
            entityId: $booking->id,
            description: "Traveler {$user->name} created booking {$booking->booking_reference} for business {$business->name}.",
            newValues: $booking->toArray(),
            userId: $user->id
        );

        $booking->load(['business.province', 'business.category', 'business.images', 'service']);

        return $this->createdResponse(new BookingResource($booking), 'Booking request created successfully.');
    }

    /**
     * Show traveler's booking details.
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(['business.province', 'business.category', 'business.images', 'service'])
            ->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if ($booking->user_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Unauthorized to view this booking.', 403);
        }

        return $this->successResponse(new BookingResource($booking), 'Booking details retrieved successfully.');
    }

    /**
     * Cancel a traveler's booking.
     */
    public function cancel(CancelBookingRequest $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(['business', 'service'])->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if ($booking->user_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Unauthorized to cancel this booking.', 403);
        }

        if (!$booking->canBeCancelled()) {
            return $this->errorResponse("This booking cannot be cancelled because its status is already '{$booking->status}'.", 422);
        }

        $oldStatus = $booking->status;
        $reason = $request->input('cancellation_reason') ?: 'Cancelled by traveler';

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        // Notify Business Owner
        if ($booking->business && $booking->business->owner_id) {
            Notification::createNotification([
                'user_id' => $booking->business->owner_id,
                'type' => 'booking_cancelled',
                'category' => 'Booking',
                'title' => 'Booking Cancelled',
                'description' => "Booking (#{$booking->booking_reference}) was cancelled by {$booking->customer_name}.",
                'link' => "/business/bookings/{$booking->id}",
                'data' => [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'cancellation_reason' => $reason,
                ],
            ]);
        }

        // Audit Log
        AuditLogger::log(
            action: 'booking_cancelled',
            entityType: 'Booking',
            entityId: $booking->id,
            description: "Traveler {$user->name} cancelled booking {$booking->booking_reference}.",
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => Booking::STATUS_CANCELLED, 'cancellation_reason' => $reason],
            userId: $user->id
        );

        $booking->load(['business.province', 'business.category', 'business.images', 'service']);

        return $this->successResponse(new BookingResource($booking), 'Booking cancelled successfully.');
    }
}
