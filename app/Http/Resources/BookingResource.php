<?php

namespace App\Http\Resources;

use App\Traits\NormalizesMediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    use NormalizesMediaUrl;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'user_id' => $this->user_id,
            'business_id' => $this->business_id,
            'service_id' => $this->service_id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'booking_date' => $this->booking_date?->format('Y-m-d'),
            'booking_time' => $this->booking_time,
            'number_of_guests' => (int) $this->number_of_guests,
            'total_price' => (float) $this->total_price,
            'currency' => $this->currency ?? 'USD',
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'special_requests' => $this->special_requests,
            'rejection_reason' => $this->rejection_reason,
            'cancellation_reason' => $this->cancellation_reason,
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'avatar' => $this->normalizeUrl($this->user->avatar),
            ] : null),
            'business' => $this->whenLoaded('business', fn () => $this->business ? [
                'id' => $this->business->id,
                'name' => $this->business->name,
                'slug' => $this->business->slug,
                'owner_id' => $this->business->owner_id,
                'address' => $this->business->address,
                'phone' => $this->business->phone,
                'email' => $this->business->email,
                'cover_image_url' => $this->normalizeUrl($this->business->cover_image_url),
                'rating' => (float) $this->business->rating,
                'province' => $this->business->province ? [
                    'id' => $this->business->province->id,
                    'name' => $this->business->province->name,
                ] : null,
                'category' => $this->business->category ? [
                    'id' => $this->business->category->id,
                    'name' => $this->business->category->name,
                ] : null,
            ] : null),
            'service' => $this->whenLoaded('service', fn () => $this->service ? [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'description' => $this->service->description,
                'price' => (float) $this->service->price,
                'currency' => $this->service->currency,
                'duration_minutes' => $this->service->duration_minutes,
            ] : null),
        ];
    }
}
