<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\AdminBusinessStatusRequest;
use App\Http\Requests\Business\UpdateBusinessRequest;
use App\Http\Resources\BusinessDetailResource;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Models\Notification;
use App\Services\AuditLogger;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBusinessController extends Controller
{
    use ApiResponse;

    /**
     * List all businesses for Admin management.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Business::with(['owner', 'category', 'province'])
            ->withCount(['images', 'services', 'reviews']);

        if ($request->has('verification_status')) {
            $query->where('verification_status', $request->input('verification_status'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->has('province_id')) {
            $query->where('province_id', $request->input('province_id'));
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $businesses = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'businesses' => BusinessResource::collection($businesses),
            'pagination' => [
                'current_page' => $businesses->currentPage(),
                'last_page' => $businesses->lastPage(),
                'per_page' => $businesses->perPage(),
                'total' => $businesses->total(),
            ],
        ], 'Businesses retrieved successfully for administration.');
    }

    /**
     * Show single business for Admin review.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $business = Business::with(['owner', 'category', 'province', 'images', 'services', 'hours', 'promotions', 'verifiedBy'])->find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        return $this->successResponse(
            new BusinessDetailResource($business),
            'Business retrieved successfully.'
        );
    }

    /**
     * Update business details by Admin.
     */
    public function update(UpdateBusinessRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $admin = $request->user();
        $oldValues = $business->toArray();

        $business->update($request->validated());
        $business->load(['owner', 'category', 'province', 'images', 'services', 'hours', 'promotions']);

        AuditLogger::log(
            action: 'business.admin_updated',
            entityType: 'Business',
            entityId: $business->id,
            description: "Business '{$business->name}' was updated by administrator {$admin->name}.",
            oldValues: $oldValues,
            newValues: $business->toArray()
        );

        return $this->successResponse(
            new BusinessDetailResource($business),
            'Business updated successfully by administrator.'
        );
    }

    /**
     * Delete business by Admin.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $admin = $request->user();
        $businessName = $business->name;
        $businessId = $business->id;
        $oldValues = $business->toArray();

        $business->delete();

        AuditLogger::log(
            action: 'business.admin_deleted',
            entityType: 'Business',
            entityId: $businessId,
            description: "Business '{$businessName}' was deleted by administrator {$admin->name}.",
            oldValues: $oldValues
        );

        return $this->successResponse(null, "Business '{$businessName}' deleted successfully.");
    }

    /**
     * Approve business.
     */
    public function approve(AdminBusinessStatusRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $admin = $request->user();
        $oldValues = $business->toArray();

        $business->update([
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'rejection_reason' => null,
            'status' => 'active',
        ]);

        $business->load(['owner', 'category', 'province']);

        // Log audit
        AuditLogger::log(
            action: 'business.approved',
            entityType: 'Business',
            entityId: $business->id,
            description: "Business '{$business->name}' was approved by administrator {$admin->name}.",
            oldValues: $oldValues,
            newValues: $business->toArray()
        );

        // Notify Business Owner
        if ($business->owner_id) {
            Notification::createNotification([
                'user_id' => $business->owner_id,
                'type' => 'business_approved',
                'category' => 'Business',
                'title' => 'Business Approved!',
                'description' => "Congratulations! Your business '{$business->name}' has been verified and approved by the tourism board.",
                'link' => "/business/businesses/{$business->id}",
                'data' => [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'status' => 'approved',
                ],
            ]);
        }

        return $this->successResponse(
            new BusinessDetailResource($business),
            "Business '{$business->name}' has been approved and is now public."
        );
    }

    /**
     * Reject business.
     */
    public function reject(AdminBusinessStatusRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $admin = $request->user();
        $reason = $request->input('rejection_reason') ?? $request->input('reason') ?? 'Requirements not satisfied.';
        $oldValues = $business->toArray();

        $business->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $business->load(['owner', 'category', 'province']);

        AuditLogger::log(
            action: 'business.rejected',
            entityType: 'Business',
            entityId: $business->id,
            description: "Business '{$business->name}' was rejected by administrator {$admin->name}. Reason: {$reason}",
            oldValues: $oldValues,
            newValues: $business->toArray()
        );

        // Notify Business Owner
        if ($business->owner_id) {
            Notification::createNotification([
                'user_id' => $business->owner_id,
                'type' => 'business_rejected',
                'category' => 'Business',
                'title' => 'Business Verification Update',
                'description' => "Your business '{$business->name}' could not be approved at this time. Reason: {$reason}",
                'link' => "/business/businesses/{$business->id}",
                'data' => [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'reason' => $reason,
                ],
            ]);
        }

        return $this->successResponse(
            new BusinessDetailResource($business),
            "Business '{$business->name}' has been rejected."
        );
    }

    /**
     * Suspend business.
     */
    public function suspend(AdminBusinessStatusRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $admin = $request->user();
        $reason = $request->input('reason') ?? 'Administrative suspension.';
        $oldValues = $business->toArray();

        $business->update([
            'status' => 'suspended',
        ]);

        $business->load(['owner', 'category', 'province']);

        AuditLogger::log(
            action: 'business.suspended',
            entityType: 'Business',
            entityId: $business->id,
            description: "Business '{$business->name}' was suspended by administrator {$admin->name}. Reason: {$reason}",
            oldValues: $oldValues,
            newValues: $business->toArray()
        );

        // Notify Business Owner
        if ($business->owner_id) {
            Notification::createNotification([
                'user_id' => $business->owner_id,
                'type' => 'business_suspended',
                'category' => 'Business',
                'title' => 'Business Suspended',
                'description' => "Your business '{$business->name}' has been suspended. Reason: {$reason}",
                'link' => "/business/businesses/{$business->id}",
                'data' => [
                    'business_id' => $business->id,
                    'reason' => $reason,
                ],
            ]);
        }

        return $this->successResponse(
            new BusinessDetailResource($business),
            "Business '{$business->name}' has been suspended."
        );
    }

    /**
     * Activate business.
     */
    public function activate(AdminBusinessStatusRequest $request, $id): JsonResponse
    {
        $business = Business::find($id);

        if (!$business) {
            return $this->errorResponse('Business not found.', 404);
        }

        $admin = $request->user();
        $oldValues = $business->toArray();

        $business->update([
            'status' => 'active',
        ]);

        $business->load(['owner', 'category', 'province']);

        AuditLogger::log(
            action: 'business.activated',
            entityType: 'Business',
            entityId: $business->id,
            description: "Business '{$business->name}' was activated by administrator {$admin->name}.",
            oldValues: $oldValues,
            newValues: $business->toArray()
        );

        return $this->successResponse(
            new BusinessDetailResource($business),
            "Business '{$business->name}' is now active."
        );
    }
}
