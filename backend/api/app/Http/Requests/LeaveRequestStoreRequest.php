<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:100'],
            'leave_type_id' => ['nullable', 'exists:leave_types,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'time_from' => ['nullable', 'date_format:H:i'],
            'time_to' => ['nullable', 'date_format:H:i', 'after:time_from'],
            'reason' => ['required', 'string'],
            'doctor_note' => ['nullable', 'string'],
            'permit_photo' => ['nullable', 'image', 'max:5120'], // 5MB max
        ];
    }
}
