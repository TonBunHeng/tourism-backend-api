<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Business;
use App\Models\Notification;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessBookingController extends Controller
{
    use ApiResponse;

    /**
     * List all bookings for businesses owned by the authenticated owner.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Booking::with(['business.province', 'business.category', 'business.images', 'service', 'user']);

        if (!$user->isAdmin()) {
            $query->whereHas('business', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        if ($businessId = $request->input('business_id')) {
            $query->where('business_id', $businessId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
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
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
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

        return $this->successResponse([
            'bookings' => BookingResource::collection($bookings),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ], 'Business bookings retrieved successfully.');
    }

    /**
     * List bookings for a specific owned business.
     */
    public function businessBookings(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $business = Business::find($id);
        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        if ($business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Unauthorized to view bookings for this business.', 403);
        }

        $query = Booking::with(['service', 'user'])
            ->where('business_id', $business->id);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('booking_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('booking_date', '<=', $dateTo);
        }

        $perPage = min((int) $request->input('per_page', 15), 50);
        $bookings = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
            ],
            'bookings' => BookingResource::collection($bookings),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ], "Bookings for business '{$business->name}' retrieved successfully.");
    }

    /**
     * Show booking details for a business owner.
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(['business.province', 'business.category', 'business.images', 'service', 'user'])
            ->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if ($booking->business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Unauthorized to view this booking.', 403);
        }

        return $this->successResponse(new BookingResource($booking), 'Booking details retrieved successfully.');
    }

    /**
     * Confirm a booking request.
     */
    public function confirm(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(['business', 'service', 'user'])->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if ($booking->business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Unauthorized to manage this booking.', 403);
        }

        if (!$booking->canBeConfirmed()) {
            return $this->errorResponse("Only pending bookings can be confirmed. Current status: '{$booking->status}'.", 422);
        }

        $oldStatus = $booking->status;

        $booking->update([
            'status' => Booking::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        // Notify Traveler
        Notification::createNotification([
            'user_id' => $booking->user_id,
            'type' => 'booking_confirmed',
            'category' => 'Booking',
            'title' => 'Booking Confirmed!',
            'description' => "Your booking (#{$booking->booking_reference}) for {$booking->business->name} on " . $booking->booking_date?->format('Y-m-d') . " has been confirmed!",
            'link' => "/bookings/{$booking->id}",
            'data' => [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'business_id' => $booking->business->id,
                'business_name' => $booking->business->name,
                'booking_date' => $booking->booking_date?->format('Y-m-d'),
                'booking_time' => $booking->booking_time,
            ],
        ]);

        // Audit Log
        AuditLogger::log(
            action: 'booking_confirmed',
            entityType: 'Booking',
            entityId: $booking->id,
            description: "Owner {$user->name} confirmed booking {$booking->booking_reference} for business {$booking->business->name}.",
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => Booking::STATUS_CONFIRMED, 'confirmed_at' => now()->toIso8601String()],
            userId: $user->id
        );

        $booking->load(['business.province', 'business.category', 'business.images', 'service', 'user']);

        return $this->successResponse(new BookingResource($booking), 'Booking confirmed successfully.');
    }

    /**
     * Reject a booking request.
     */
    public function reject(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(['business', 'service', 'user'])->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if ($booking->business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Unauthorized to manage this booking.', 403);
        }

        if (!$booking->canBeRejected()) {
            return $this->errorResponse("This booking cannot be rejected because its current status is '{$booking->status}'.", 422);
        }

        $reason = $request->input('rejection_reason') ?: 'Declined by host due to availability.';
        $oldStatus = $booking->status;

        $booking->update([
            'status' => Booking::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        // Notify Traveler
        Notification::createNotification([
            'user_id' => $booking->user_id,
            'type' => 'booking_rejected',
            'category' => 'Booking',
            'title' => 'Booking Declined',
            'description' => "Your booking (#{$booking->booking_reference}) for {$booking->business->name} was declined: {$reason}",
            'link' => "/bookings/{$booking->id}",
            'data' => [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'business_id' => $booking->business->id,
                'business_name' => $booking->business->name,
                'rejection_reason' => $reason,
            ],
        ]);

        // Audit Log
        AuditLogger::log(
            action: 'booking_rejected',
            entityType: 'Booking',
            entityId: $booking->id,
            description: "Owner {$user->name} rejected booking {$booking->booking_reference} for business {$booking->business->name}.",
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => Booking::STATUS_REJECTED, 'rejection_reason' => $reason],
            userId: $user->id
        );

        $booking->load(['business.province', 'business.category', 'business.images', 'service', 'user']);

        return $this->successResponse(new BookingResource($booking), 'Booking rejected successfully.');
    }

    /**
     * Mark a booking as completed.
     */
    public function complete(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $booking = Booking::with(['business', 'service', 'user'])->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if ($booking->business->owner_id !== $user->id && !$user->isAdmin()) {
            return $this->errorResponse('Unauthorized to manage this booking.', 403);
        }

        if (!$booking->canBeCompleted()) {
            return $this->errorResponse("Only confirmed bookings can be marked as completed. Current status: '{$booking->status}'.", 422);
        }

        $oldStatus = $booking->status;

        $booking->update([
            'status' => Booking::STATUS_COMPLETED,
            'completed_at' => now(),
            'payment_status' => $request->input('payment_status', Booking::PAYMENT_PAID),
        ]);

        // Notify Traveler
        Notification::createNotification([
            'user_id' => $booking->user_id,
            'type' => 'booking_completed',
            'category' => 'Booking',
            'title' => 'Booking Completed',
            'description' => "Your visit to {$booking->business->name} has concluded. Thank you for booking! You can share your feedback by leaving a review.",
            'link' => "/businesses/{$booking->business->id}",
            'data' => [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'business_id' => $booking->business->id,
                'business_name' => $booking->business->name,
            ],
        ]);

        // Audit Log
        AuditLogger::log(
            action: 'booking_completed',
            entityType: 'Booking',
            entityId: $booking->id,
            description: "Owner {$user->name} marked booking {$booking->booking_reference} as completed.",
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => Booking::STATUS_COMPLETED, 'completed_at' => now()->toIso8601String()],
            userId: $user->id
        );

        $booking->load(['business.province', 'business.category', 'business.images', 'service', 'user']);

        return $this->successResponse(new BookingResource($booking), 'Booking marked as completed.');
    }

    /**
     * Flexible status update.
     */
    public function updateStatus(UpdateBookingStatusRequest $request, int|string $id): JsonResponse
    {
        $status = $request->input('status');

        return match ($status) {
            Booking::STATUS_CONFIRMED => $this->confirm($request, $id),
            Booking::STATUS_REJECTED => $this->reject($request, $id),
            Booking::STATUS_COMPLETED => $this->complete($request, $id),
            default => $this->errorResponse("Direct status transition to '{$status}' is not supported through this action.", 422),
        };
    }

    /**
     * Get booking statistics for the business owner.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Booking::query();

        if (!$user->isAdmin()) {
            $query->whereHas('business', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        if ($businessId = $request->input('business_id')) {
            $query->where('business_id', $businessId);
        }

        $total = (clone $query)->count();
        $pending = (clone $query)->where('status', Booking::STATUS_PENDING)->count();
        $confirmed = (clone $query)->where('status', Booking::STATUS_CONFIRMED)->count();
        $completed = (clone $query)->where('status', Booking::STATUS_COMPLETED)->count();
        $cancelled = (clone $query)->where('status', Booking::STATUS_CANCELLED)->count();
        $rejected = (clone $query)->where('status', Booking::STATUS_REJECTED)->count();

        $revenue = (clone $query)->where('status', Booking::STATUS_COMPLETED)->sum('total_price');
        $upcoming = (clone $query)->where('status', Booking::STATUS_CONFIRMED)
            ->whereDate('booking_date', '>=', now()->toDateString())
            ->count();

        return $this->successResponse([
            'total_bookings' => $total,
            'pending_bookings' => $pending,
            'confirmed_bookings' => $confirmed,
            'completed_bookings' => $completed,
            'cancelled_bookings' => $cancelled,
            'rejected_bookings' => $rejected,
            'upcoming_bookings' => $upcoming,
            'total_revenue' => round((float) $revenue, 2),
            'currency' => 'USD',
        ], 'Booking statistics retrieved successfully.');
    }
}
