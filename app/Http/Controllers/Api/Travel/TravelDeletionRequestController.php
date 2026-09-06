<?php

namespace App\Http\Controllers\Api\Travel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Travel\TravelDeletionRequest as TravelDeletionRequestForm;
use App\Http\Resources\Travel\TravelDeletionRequestResource;
use App\Models\DeletionRequest;
use App\Models\DeletionRequestItem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelDeletionRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $requests = DeletionRequest::with('items')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return $this->successResponse(
            TravelDeletionRequestResource::collection($requests),
            'Deletion requests retrieved successfully.'
        );
    }

    public function store(TravelDeletionRequestForm $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $requestType = $validated['request_type'] ?? 'account';
        $additionalInfo = $validated['additional_info'] 
            ?? ($request->input('email') ? 'Account email: ' . $request->input('email') : null);

        $deletionRequest = DeletionRequest::create([
            'user_id' => $user->id,
            'request_type' => $requestType,
            'reason' => $validated['reason'],
            'additional_info' => $additionalInfo,
            'status' => 'pending',
            'urgency' => $validated['urgency'] ?? 'low',
        ]);

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                DeletionRequestItem::create([
                    'deletion_request_id' => $deletionRequest->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'reason' => $item['reason'] ?? $validated['reason'],
                    'status' => 'pending',
                ]);
            }
        }

        $deletionRequest->load('items');

        \App\Models\Notification::createNotification([
            'type' => 'deletion_request',
            'category' => 'Alerts',
            'title' => 'Deletion Request Pending Approval',
            'description' => "User {$user->name} submitted a deletion request: \"{$deletionRequest->reason}\".",
            'link' => '/deletion-requests',
            'read' => false,
            'data' => [
                'request_id' => $deletionRequest->id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'urgency' => $deletionRequest->urgency,
            ]
        ]);

        return $this->successResponse(
            new TravelDeletionRequestResource($deletionRequest),
            'Deletion request submitted successfully and is awaiting administrator review.',
            201
        );
    }
}
