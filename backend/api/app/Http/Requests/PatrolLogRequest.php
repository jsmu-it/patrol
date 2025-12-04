<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatrolLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::error('Patrol Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'inputs' => $this->all(),
        ]);
        parent::failedValidation($validator);
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'type' => ['nullable', 'string', Rule::in(['patrol', 'sos', 'incident'])],
            'checkpoint_code' => [
                Rule::requiredIf(function () {
                    $type = $this->input('type', 'patrol');

                    return $type === 'patrol';
                }),
                'nullable',
                'string',
            ],
            'title' => ['required', 'string', 'max:255'],
            'post_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}

