<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'tolerance_minutes',
        'is_default',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withTimestamps()
            ->withPivot('is_active');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
