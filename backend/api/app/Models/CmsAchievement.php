<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsAchievement extends Model
{
    protected $fillable = ['title', 'year', 'description', 'image', 'order'];
}
