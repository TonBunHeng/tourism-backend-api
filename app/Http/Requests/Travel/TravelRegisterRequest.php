<?php

namespace App\Http\Requests\Travel;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TravelRegisterRequest extends FormRequest
{
    public const ALLOWED_REGISTRATION_ROLES = [
        'user',
        'User',
        'tourist',
        'Tourist',
        'traveler',
        'Traveler',
        'member',
        'business_owner',
        'Business Owner',
        'business-owner',
        'Business_Owner',
        'business',
        'Business',
        'owner',
        'Owner',
        'businessowner',
        'BusinessOwner',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize role input before validation runs.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('role') && is_string($this->role)) {
            $cleaned = strtolower(trim($this->role));
            $cleaned = str_replace([' ', '/', '-'], '_', $cleaned);
            $cleaned = preg_replace('/_+/', '_', $cleaned);

            if (in_array($cleaned, ['business_owner', 'business', 'owner', 'businessowner'], true)) {
                $this->merge(['role' => User::ROLE_BUSINESS_OWNER]);
            } elseif (in_array($cleaned, ['user', 'tourist', 'traveler', 'member'], true)) {
                $this->merge(['role' => User::ROLE_USER]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:30',
            'location' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|string',
            'role' => [
                'nullable',
                'string',
                Rule::in(array_merge(self::ALLOWED_REGISTRATION_ROLES, [User::ROLE_USER, User::ROLE_BUSINESS_OWNER])),
            ],
        ];
    }
}
