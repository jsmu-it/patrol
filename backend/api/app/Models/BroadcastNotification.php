<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BroadcastNotification extends Model
{
    protected $fillable = [
        'sent_by',
        'title',
        'message',
        'image',
        'target',
        'target_project_id',
        'target_role',
        'recipients_count',
        'success_count',
        'failed_count',
        'sent_at',
    ];

    protected $casts = [
        'recipients_count' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'sent_at' => 'datetime',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        return Storage::disk('public')->url($this->image);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function targetProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'target_project_id');
    }
}
