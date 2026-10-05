<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:pending,confirmed,cancelled,completed,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
            'cancellation_reason' => 'nullable|string|max:500',
            'payment_status' => 'nullable|string|in:unpaid,paid,refunded',
        ];
    }
}
