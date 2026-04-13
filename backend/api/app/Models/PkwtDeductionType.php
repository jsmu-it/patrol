<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkwtDeductionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function pkwtDeductions()
    {
        return $this->hasMany(PkwtDeduction::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
