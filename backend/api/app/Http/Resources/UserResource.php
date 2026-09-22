<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->profile;

        // Saldo cuti dihitung di satu tempat bersama dashboard, supaya angka
        // yang dilihat karyawan di aplikasi tidak pernah berbeda dari yang
        // dilihat HR di dashboard.
        $berhakCuti    = \App\Services\SaldoCuti::berhakCuti($this->resource);
        $leaveBalances = \App\Services\SaldoCuti::untuk($this->resource)
            ->map(fn (array $b) => \Illuminate\Support\Arr::except($b, ['tersimpan']))
            ->all();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'username' => $this->username,
            'supervisor_id' => $this->supervisor_id,
            'supervisor' => $this->whenLoaded('supervisor', function () {
                return [
                    'id' => $this->supervisor->id,
                    'name' => $this->supervisor->name,
                ];
            }),
            'active_project_id' => $this->active_project_id,
            'nip' => $profile?->nip,
            'position' => $profile?->position,
            'division' => $profile?->division,
            'profile_photo_path' => $profile?->profile_photo_path,
            'profile_photo_url' => $profile && $profile->profile_photo_path
                ? asset('storage/'.$profile->profile_photo_path)
                : null,
            'active_project_name' => $this->activeProject?->name,
            'project_lat' => $this->activeProject?->latitude,
            'project_lng' => $this->activeProject?->longitude,
            'project_radius' => $this->activeProject?->geofence_radius_meters,
            'leave_balances' => $leaveBalances,
            // Penempatan di luar Head Office memang tidak punya jatah cuti;
            // dibedakan dari "punya jatah tetapi sisa nol".
            'leave_eligible' => $berhakCuti,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
