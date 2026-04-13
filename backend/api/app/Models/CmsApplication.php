<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'description',
        'platform',
        'file_path',
        'icon',
        'file_size',
        'download_count',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getPlatformIconAttribute()
    {
        return match($this->platform) {
            'android' => 'fab fa-android',
            'ios' => 'fab fa-apple',
            'windows' => 'fab fa-windows',
            default => 'fas fa-mobile-alt',
        };
    }
}
