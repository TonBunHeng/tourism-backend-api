<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hours' => ['required', 'array'],
            'hours.*.day_of_week' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i,H:i:s'],
            'hours.*.is_closed' => ['nullable', 'boolean'],
        ];
    }
}
