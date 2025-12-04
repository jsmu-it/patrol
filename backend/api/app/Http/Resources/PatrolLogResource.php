<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatrolLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'checkpoint_id' => $this->checkpoint_id,
            'type' => $this->type,
            'title' => $this->title,
            'post_name' => $this->post_name,
            'description' => $this->description,
            'photo_path' => $this->photo_path,
            'photo_url' => $this->photo_path ? asset('storage/'.$this->photo_path) : null,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'occurred_at' => $this->occurred_at?->format('d-m-Y H:i'),
            'created_at' => $this->created_at?->format('d-m-Y H:i'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i'),
        ];
    }
}
