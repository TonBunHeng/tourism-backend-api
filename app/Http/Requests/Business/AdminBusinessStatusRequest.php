<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class AdminBusinessStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
