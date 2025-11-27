<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'shift_id' => $this->shift_id,
            'type' => $this->type,
            'occurred_at' => $this->occurred_at?->toISOString(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'selfie_photo_path' => $this->selfie_photo_path,
            'note' => $this->note,
            'mode' => $this->mode,
            'status_dinas' => $this->status_dinas,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
