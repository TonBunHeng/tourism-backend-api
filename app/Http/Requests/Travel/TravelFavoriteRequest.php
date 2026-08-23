<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $placeId = $this->input('place_id') ?? $this->input('placeId') ?? $this->input('id');
        if ($placeId !== null) {
            $this->merge([
                'place_id' => (int) $placeId,
            ]);
        }
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
