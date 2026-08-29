<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:150',
            'destination' => 'nullable|string|max:150',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'travelers' => 'nullable|integer|min:1|max:50',
            'status' => 'nullable|in:planning,confirmed,completed,cancelled',
            'notes' => 'nullable|string|max:2000',
            'cover_image' => 'nullable|string',
            'is_public' => 'nullable|boolean',
            'itineraries' => 'nullable|array',
            'itineraries.*.id' => 'nullable|integer',
            'itineraries.*.place_id' => 'nullable|integer|exists:places,id',
            'itineraries.*.day_number' => 'required_with:itineraries|integer|min:1',
            'itineraries.*.time_slot' => 'nullable|string|max:50',
            'itineraries.*.activity' => 'required_with:itineraries|string|max:255',
            'itineraries.*.estimated_cost' => 'nullable|numeric|min:0',
            'itineraries.*.duration_minutes' => 'nullable|integer|min:0',
            'itineraries.*.notes' => 'nullable|string|max:1000',
            'itineraries.*.sort_order' => 'nullable|integer',
            'itineraries.*.is_completed' => 'nullable|boolean',
        ];
    }
}
