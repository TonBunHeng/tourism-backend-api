<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'place_id' => 'required|exists:places,id',
            'visited' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
