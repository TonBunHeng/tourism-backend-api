<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_type' => 'required|in:account,item',
            'reason' => 'required|string|max:1000',
            'additional_info' => 'nullable|string|max:2000',
            'items' => 'nullable|array',
            'items.*.item_type' => 'required_with:items|string|max:50',
            'items.*.item_id' => 'required_with:items|integer',
            'items.*.reason' => 'nullable|string|max:255',
        ];
    }
}
