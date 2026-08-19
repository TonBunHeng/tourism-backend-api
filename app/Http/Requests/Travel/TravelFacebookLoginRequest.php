<?php

namespace App\Http\Requests\Travel;

use Illuminate\Foundation\Http\FormRequest;

class TravelFacebookLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'nullable|string',
            'access_token' => 'nullable|string',
            'facebook_id' => 'nullable|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string|max:100',
            'avatar' => 'nullable|string',
        ];
    }
}
