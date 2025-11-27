<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_name',
        'address',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'is_active',
    ];

    public function guards()
    {
        return $this->hasMany(User::class, 'active_project_id');
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class)
            ->withTimestamps()
            ->withPivot('is_active');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function patrolLogs()
    {
        return $this->hasMany(PatrolLog::class);
    }

    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class);
    }
}
