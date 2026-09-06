<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('request_type') || empty($this->input('request_type'))) {
            $this->merge(['request_type' => 'account']);
        }
    }

    public function rules(): array
    {
        return [
            'request_type' => 'nullable|in:account,item',
            'reason' => 'required|string|max:1000',
            'additional_info' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:255',
            'urgency' => 'nullable|in:critical,high,medium,low',
            'items' => 'nullable|array',
            'items.*.item_type' => 'required_with:items|string|max:50',
            'items.*.item_id' => 'nullable|integer',
            'items.*.reason' => 'nullable|string|max:255',
        ];
    }
}
