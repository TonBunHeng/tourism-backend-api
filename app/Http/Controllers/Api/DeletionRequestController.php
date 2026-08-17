<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeletionRequestResource;
use App\Models\DeletionRequest;
use App\Models\DeletionRequestItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeletionRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = DeletionRequest::with(['user', 'processedBy', 'items']);

        if (!in_array($user->role, ['Super Admin', 'Admin'])) {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($urgency = $request->query('urgency')) {
            $query->where('urgency', $urgency);
        }

        $requests = $query->orderBy('id', 'desc')->get();

        return $this->successResponse(DeletionRequestResource::collection($requests), 'Deletion requests retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_type' => ['required', Rule::in(['account', 'item'])],
            'reason' => 'required|string',
            'additional_info' => 'nullable|string',
            'urgency' => ['nullable', Rule::in(['critical', 'high', 'medium', 'low'])],
            'items' => 'nullable|array',
            'items.*.item_type' => 'required|string|max:50',
            'items.*.item_id' => 'nullable|integer',
            'items.*.item_name' => 'required|string|max:150',
            'items.*.category' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        $delRequest = DeletionRequest::create([
            'user_id' => $user->id,
            'request_type' => $validated['request_type'],
            'reason' => $validated['reason'],
            'additional_info' => $validated['additional_info'] ?? null,
            'urgency' => $validated['urgency'] ?? 'low',
            'status' => 'pending',
        ]);

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                DeletionRequestItem::create([
                    'deletion_request_id' => $delRequest->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'category' => $item['category'] ?? null,
                    'date_added' => now()->toDateString(),
                ]);
            }
        }

        $delRequest->load(['user', 'processedBy', 'items']);

        return $this->successResponse(new DeletionRequestResource($delRequest), 'Deletion request submitted.', 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $delRequest = DeletionRequest::with(['user', 'processedBy', 'items'])->find($id);

        if (!$delRequest) {
            return $this->errorResponse('Deletion request not found.', 404);
        }

        return $this->successResponse(new DeletionRequestResource($delRequest), 'Deletion request details.');
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $delRequest = DeletionRequest::find($id);

        if (!$delRequest) {
            return $this->errorResponse('Deletion request not found.', 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'archived'])],
            'admin_notes' => 'nullable|string',
        ]);

        $delRequest->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $delRequest->admin_notes,
            'processed_by_user_id' => $request->user()->id,
            'processed_at' => now(),
        ]);

        $delRequest->load(['user', 'processedBy', 'items']);

        return $this->successResponse(new DeletionRequestResource($delRequest), 'Deletion request status updated.');
    }
}
