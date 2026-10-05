<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Notification;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    use ApiResponse;

    /**
     * List all bookings across the platform for administrators.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Booking::with(['business.province', 'business.category', 'business.owner', 'service', 'user']);

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
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('business', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $bookings = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'bookings' => BookingResource::collection($bookings),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ], 'Admin bookings retrieved successfully.');
    }

    /**
     * Show any booking details for administrators.
     */
    public function show(int|string $id): JsonResponse
    {
        $booking = Booking::with(['business.province', 'business.category', 'business.owner', 'service', 'user'])
            ->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        return $this->successResponse(new BookingResource($booking), 'Booking retrieved successfully.');
    }

    /**
     * Update booking status as administrator.
     */
    public function updateStatus(UpdateBookingStatusRequest $request, int|string $id): JsonResponse
    {
        $admin = $request->user();
        $booking = Booking::with(['business', 'service', 'user'])->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        $oldStatus = $booking->status;
        $newStatus = $request->input('status');

        $updates = ['status' => $newStatus];

        if ($newStatus === Booking::STATUS_CONFIRMED) {
            $updates['confirmed_at'] = now();
        } elseif ($newStatus === Booking::STATUS_COMPLETED) {
            $updates['completed_at'] = now();
            if ($request->has('payment_status')) {
                $updates['payment_status'] = $request->input('payment_status');
            }
        } elseif ($newStatus === Booking::STATUS_CANCELLED) {
            $updates['cancelled_at'] = now();
            if ($request->has('cancellation_reason')) {
                $updates['cancellation_reason'] = $request->input('cancellation_reason');
            }
        } elseif ($newStatus === Booking::STATUS_REJECTED) {
            $updates['rejection_reason'] = $request->input('rejection_reason');
        }

        $booking->update($updates);

        // Notify Traveler
        Notification::createNotification([
            'user_id' => $booking->user_id,
            'type' => 'booking_status_updated',
            'category' => 'Booking',
            'title' => 'Booking Status Updated',
            'description' => "Your booking (#{$booking->booking_reference}) status has been updated to {$newStatus}.",
            'link' => "/bookings/{$booking->id}",
            'data' => [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'status' => $newStatus,
            ],
        ]);

        // Audit Log
        AuditLogger::log(
            action: 'admin_booking_status_updated',
            entityType: 'Booking',
            entityId: $booking->id,
            description: "Admin {$admin->name} changed booking {$booking->booking_reference} status from {$oldStatus} to {$newStatus}.",
            oldValues: ['status' => $oldStatus],
            newValues: $updates,
            userId: $admin->id
        );

        $booking->load(['business.province', 'business.category', 'business.owner', 'service', 'user']);

        return $this->successResponse(new BookingResource($booking), "Booking status updated to '{$newStatus}'.");
    }

    /**
     * Delete booking (Super Admin).
     */
    public function destroy(int|string $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        $booking->delete();

        return $this->successResponse(null, 'Booking deleted successfully.');
    }
}
