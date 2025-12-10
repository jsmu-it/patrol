<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollSlip extends Model
{
    protected $fillable = [
        'user_id',
        'period_month',
        'nip',
        'name',
        'unit',
        'position',
        'total_income',
        'total_deduction',
        'net_income',
        'sign_location',
        'sign_date',
    ];

    protected $casts = [
        'total_income' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_income' => 'decimal:2',
        'sign_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollSlipItem::class)->orderBy('sort_order');
    }

    public function incomeItems(): HasMany
    {
        return $this->hasMany(PayrollSlipItem::class)
            ->where('type', 'income')
            ->where('amount', '>', 0)
            ->orderBy('sort_order');
    }

    public function deductionItems(): HasMany
    {
        return $this->hasMany(PayrollSlipItem::class)
            ->where('type', 'deduction')
            ->where('amount', '>', 0)
            ->orderBy('sort_order');
    }
}
