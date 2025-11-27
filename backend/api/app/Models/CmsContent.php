<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsContent extends Model
{
    protected $fillable = ['key', 'title', 'subtitle', 'body', 'image'];
}
