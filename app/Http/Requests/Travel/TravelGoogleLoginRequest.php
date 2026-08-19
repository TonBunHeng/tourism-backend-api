<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelGoogleLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_token' => 'nullable|string',
            'token' => 'nullable|string',
            'access_token' => 'nullable|string',
            'google_id' => 'nullable|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string|max:100',
            'avatar' => 'nullable|string',
        ];
    }
}
