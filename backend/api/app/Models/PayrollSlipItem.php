<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSlipItem extends Model
{
    protected $fillable = [
        'payroll_slip_id',
        'type',
        'label',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payrollSlip(): BelongsTo
    {
        return $this->belongsTo(PayrollSlip::class);
    }
}
