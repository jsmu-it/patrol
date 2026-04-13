<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkwtIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'pkwt_record_id',
        'pkwt_income_type_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function pkwtRecord()
    {
        return $this->belongsTo(PkwtRecord::class);
    }

    public function incomeType()
    {
        return $this->belongsTo(PkwtIncomeType::class, 'pkwt_income_type_id');
    }
}
