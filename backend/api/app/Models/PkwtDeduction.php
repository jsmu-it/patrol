<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkwtDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'pkwt_record_id',
        'pkwt_deduction_type_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function pkwtRecord()
    {
        return $this->belongsTo(PkwtRecord::class);
    }

    public function deductionType()
    {
        return $this->belongsTo(PkwtDeductionType::class, 'pkwt_deduction_type_id');
    }
}
