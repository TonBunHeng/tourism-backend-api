<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelGalleryCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normalize comment content from any alternative payload keys
        $commentValue = $this->input('comment')
            ?? $this->input('text')
            ?? $this->input('content')
            ?? $this->input('message')
            ?? $this->input('comment_text');

        if ($commentValue !== null) {
            $this->merge([
                'comment' => $commentValue,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'comment' => 'required|string|max:2000',
            'parent_id' => 'nullable|integer|exists:gallery_comments,id',
        ];
    }
}
