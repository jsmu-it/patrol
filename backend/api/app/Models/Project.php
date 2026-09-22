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
        'pkwt_title',
        'pkwt_template',
    ];

    /** Lokasi kerja yang tersedia — nama project yang masih aktif. */
    public static function daftarLokasi(): \Illuminate\Support\Collection
    {
        return static::where('is_active', true)->orderBy('name')->pluck('name');
    }

    public function guards()
    {
        return $this->hasMany(User::class, 'active_project_id');
    }

    public function shifts()
    {
        // Shift kini milik project, bukan lagi daftar global yang dicentang.
        return $this->hasMany(Shift::class)->orderBy('start_time');
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
