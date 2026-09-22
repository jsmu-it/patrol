<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceClockOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            // Radius ketidakpastian GPS dari perangkat, dipakai sebagai toleransi geofence.
            'accuracy'    => ['nullable', 'numeric', 'between:0,10000'],
            'note' => ['nullable', 'string'],
            'selfie' => ['nullable', 'image', 'max:5120'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
