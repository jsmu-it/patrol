<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'default_quota',
        'is_active',
    ];

    protected $casts = [
        'default_quota' => 'integer',
        'is_active' => 'boolean',
    ];

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get active leave types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
