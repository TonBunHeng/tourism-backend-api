<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessPromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'discount_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'promo_code' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
            'banner_url' => ['nullable', 'string'],
            'banner' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
        ];
    }
}
