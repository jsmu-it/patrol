<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkwtRecord extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_SENT = 'sent';
    const STATUS_SIGNED = 'signed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_TERMINATED = 'terminated';

    protected $fillable = [
        'pkwt_number',
        'job_application_id',
        'ktp_number',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'email',
        'phone',
        'position_id',
        'project_id',
        'contract_start',
        'contract_end',
        'leave_type_id',
        'status',
        'user_id',
        'sent_at',
        'activated_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'contract_start' => 'date',
        'contract_end' => 'date',
        'sent_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PkwtRecord $pkwt) {
            if (empty($pkwt->pkwt_number)) {
                $lastNumber = static::max('id') ?? 0;
                $pkwt->pkwt_number = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships
    public function jobApplication()
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incomes()
    {
        return $this->hasMany(PkwtIncome::class);
    }

    public function deductions()
    {
        return $this->hasMany(PkwtDeduction::class);
    }

    // Computed attributes
    public function getTotalIncomeAttribute(): float
    {
        return $this->incomes->sum('amount');
    }

    public function getTotalDeductionAttribute(): float
    {
        return $this->deductions->sum('amount');
    }

    public function getNetSalaryAttribute(): float
    {
        return $this->total_income - $this->total_deduction;
    }

    public function getTtlAttribute(): string
    {
        if ($this->birth_place && $this->birth_date) {
            return $this->birth_place . ', ' . $this->birth_date->format('d-m-Y');
        }
        return '-';
    }

    public function getContractPeriodAttribute(): string
    {
        if ($this->contract_start && $this->contract_end) {
            return $this->contract_start->format('d/m/Y') . ' - ' . $this->contract_end->format('d/m/Y');
        }
        return '-';
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Helper methods for status
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_SENT => 'Terkirim',
            self::STATUS_SIGNED => 'Ditandatangani',
            self::STATUS_EXPIRED => 'Kadaluarsa',
            self::STATUS_TERMINATED => 'Diberhentikan',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_ACTIVE => 'green',
            self::STATUS_SENT => 'blue',
            self::STATUS_SIGNED => 'indigo',
            self::STATUS_EXPIRED => 'yellow',
            self::STATUS_TERMINATED => 'red',
            default => 'gray',
        };
    }
}
