<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // When occurred_at is provided, this is an offline replay — selfie may no longer exist
        // on the device (OS clears temp/cache), so we allow it to be nullable in that case.
        $selfieRule = $this->filled('occurred_at')
            ? ['nullable', 'image', 'max:5120']
            : ['required', 'image', 'max:5120'];

        return [
            'shift_id'    => ['required', 'integer', 'exists:shifts,id'],
            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
            'mode'        => ['required', 'in:normal,dinas'],
            'note'        => ['nullable', 'string'],
            'selfie'      => $selfieRule,
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
