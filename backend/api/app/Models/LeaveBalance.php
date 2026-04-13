<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'quota',
        'used',
        'year',
    ];

    protected $casts = [
        'quota' => 'integer',
        'used' => 'integer',
        'year' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get remaining balance
     */
    public function getRemainingAttribute(): int
    {
        return max(0, $this->quota - $this->used);
    }

    /**
     * Deduct from balance
     */
    public function deduct(int $days): bool
    {
        if ($this->remaining < $days && $this->quota > 0) {
            return false; // Not enough balance
        }

        $this->used += $days;
        return $this->save();
    }

    /**
     * Get balance for user, type, and year (or create if not exists)
     */
    public static function getOrCreate(int $userId, int $leaveTypeId, ?int $year = null): self
    {
        $year = $year ?? now()->year;
        
        $balance = self::where('user_id', $userId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (!$balance) {
            $leaveType = LeaveType::find($leaveTypeId);
            $balance = self::create([
                'user_id' => $userId,
                'leave_type_id' => $leaveTypeId,
                'quota' => $leaveType->default_quota ?? 0,
                'used' => 0,
                'year' => $year,
            ]);
        }

        return $balance;
    }
}
