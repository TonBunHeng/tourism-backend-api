<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,medium,high,critical',
            'message' => 'required|string|max:5000',
        ];
    }
}
