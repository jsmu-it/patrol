<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Testimonial extends Model
{
    protected $fillable = [
        'client_name',
        'client_position',
        'client_company',
        'client_photo',
        'content',
        'rating',
        'token',
        'status',
        'order',
        'is_featured',
        'submitted_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'order' => 'integer',
        'is_featured' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($testimonial) {
            if (empty($testimonial->token)) {
                $testimonial->token = Str::random(32);
            }
        });
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
