<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    public const TYPE_CLOCK_IN = 'clock_in';
    public const TYPE_CLOCK_OUT = 'clock_out';

    public const MODE_NORMAL = 'normal';
    public const MODE_DINAS = 'dinas';

    public const STATUS_DINAS_PENDING = 'pending';
    public const STATUS_DINAS_APPROVED = 'approved';
    public const STATUS_DINAS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'project_id',
        'shift_id',
        'type',
        'occurred_at',
        'latitude',
        'longitude',
        'selfie_photo_path',
        'note',
        'mode',
        'status_dinas',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
