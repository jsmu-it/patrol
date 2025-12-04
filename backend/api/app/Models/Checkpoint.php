<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'code',
        'title',
        'post_name',
        'description',
        'latitude',
        'longitude',
        'radius_meters',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function patrolLogs()
    {
        return $this->hasMany(PatrolLog::class);
    }
}
