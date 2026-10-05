<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_id' => 'sometimes|required|integer|exists:businesses,id',
            'service_id' => 'nullable|integer|exists:business_services,id',
            'customer_name' => 'nullable|string|max:150',
            'customer_email' => 'nullable|email|max:150',
            'customer_phone' => 'nullable|string|max:50',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'nullable|string|max:20',
            'number_of_guests' => 'nullable|integer|min:1|max:100',
            'total_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'special_requests' => 'nullable|string|max:1000',
        ];
    }
}
