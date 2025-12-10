<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastNotification extends Model
{
    protected $fillable = [
        'sent_by',
        'title',
        'message',
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

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function targetProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'target_project_id');
    }
}
