<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
            'image_url' => ['nullable', 'string'],
            'caption' => ['nullable', 'string', 'max:255'],
            'is_cover' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer'],
        ];
    }
}
