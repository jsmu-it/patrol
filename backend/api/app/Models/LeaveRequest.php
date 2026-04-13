<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'type',
        'leave_type_id',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'reason',
        'status',
        'doctor_note',
        'permit_photo',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
