<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profile = $this->profile;

        // Get active leave types and user's balances for current year
        $activeTypes = \App\Models\LeaveType::active()->get();
        $actualBalances = $this->leaveBalances()
            ->where('year', now()->year)
            ->with('leaveType')
            ->get();
        
        $actualBalancesMap = $actualBalances->keyBy('leave_type_id');

        // Always show all active leave types
        $leaveBalances = $activeTypes->map(function ($type) use ($actualBalancesMap) {
            $balance = $actualBalancesMap->get($type->id);
            $quota = $balance ? (int) $balance->quota : (int) ($type->default_quota ?? 0);
            $used = $balance ? (int) $balance->used : 0;
            
            return [
                'leave_type_id' => (int) $type->id,
                'leave_type_name' => (string) $type->name,
                'quota' => (int) $quota,
                'used' => (int) $used,
                'remaining' => (int) max(0, $quota - $used),
            ];
        });

        // Add any actual balances that belong to inactive leave types
        foreach ($actualBalances as $balance) {
            if (!$activeTypes->contains('id', $balance->leave_type_id)) {
                $leaveBalances->push([
                    'leave_type_id' => (int) $balance->leave_type_id,
                    'leave_type_name' => (string) ($balance->leaveType?->name ?? 'Unknown'),
                    'quota' => (int) $balance->quota,
                    'used' => (int) $balance->used,
                    'remaining' => (int) max(0, $balance->quota - $balance->used),
                ]);
            }
        }

        $leaveBalances = $leaveBalances->values()->all();

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
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
