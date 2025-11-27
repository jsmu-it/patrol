<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->profile;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'active_project_id' => $this->active_project_id,
            'nip' => $profile?->nip,
            'profile_photo_path' => $profile?->profile_photo_path,
            'profile_photo_url' => $profile && $profile->profile_photo_path
                ? asset('storage/'.$profile->profile_photo_path)
                : null,
            'active_project_name' => $this->activeProject?->name,
            'project_lat' => $this->activeProject?->latitude,
            'project_lng' => $this->activeProject?->longitude,
            'project_radius' => $this->activeProject?->geofence_radius_meters,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
