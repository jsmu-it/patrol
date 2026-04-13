<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkwtIncomeType extends Model
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

    public function pkwtIncomes()
    {
        return $this->hasMany(PkwtIncome::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
