<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsClient extends Model
{
    protected $fillable = ['name', 'logo', 'website', 'order'];
}
