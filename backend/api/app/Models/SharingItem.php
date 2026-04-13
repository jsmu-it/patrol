<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SharingItem extends Model
{
    protected $fillable = [
        'name',
        'type',
        'mime_type',
        'path',
        'size',
        'parent_id',
        'user_id',
        'password',
    ];

    protected $hidden = ['password'];

    public function parent()
    {
        return $this->belongsTo(SharingItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(SharingItem::class, 'parent_id')->orderBy('type', 'desc')->orderBy('name');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function getHumanSizeAttribute()
    {
        if (!$this->size) return '-';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}
