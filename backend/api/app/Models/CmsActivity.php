<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsActivity extends Model
{
    protected $fillable = ['title', 'slug', 'date', 'short_description', 'content', 'image', 'type'];
    
    protected $casts = [
        'date' => 'date',
    ];
}
