<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class BusinessReviewReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'max:2000'],
        ];
    }
}
