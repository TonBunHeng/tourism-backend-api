<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'place_id' => $isUpdate ? 'sometimes|required|exists:places,id' : 'required|exists:places,id',
            'rating' => $isUpdate ? 'sometimes|required|integer|min:1|max:5' : 'required|integer|min:1|max:5',
            'cleanliness' => 'nullable|integer|min:1|max:5',
            'value' => 'nullable|integer|min:1|max:5',
            'accessibility' => 'nullable|integer|min:1|max:5',
            'hospitality' => 'nullable|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => $isUpdate ? 'sometimes|required|string' : 'required|string',
            'visit_date' => 'nullable|date',
            'images' => 'nullable|array',
            'images.*' => 'string|max:1000',
        ];
    }
}
