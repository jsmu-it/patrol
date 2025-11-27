<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsHeroSlide extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'subtitle', 'image', 'order'];
}
