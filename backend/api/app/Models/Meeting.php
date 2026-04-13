<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Meeting extends Model
{
    protected $fillable = [
        'title',
        'description',
        'slug',
        'room_name',
        'password',
        'host_id',
        'status',
        'max_participants',
        'scheduled_at',
        'started_at',
        'ended_at',
        'settings',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'settings' => 'array',
        'max_participants' => 'integer',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Generate a unique slug from the title.
     */
    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate a unique Jitsi room name.
     */
    public static function generateRoomName(string $title): string
    {
        return 'jsmuguard-' . Str::slug($title) . '-' . Str::random(8);
    }

    /**
     * Get the public join URL.
     */
    public function getJoinUrlAttribute(): string
    {
        return url('/meeting/' . $this->slug);
    }

    /**
     * Check if meeting is joinable.
     */
    public function isJoinable(): bool
    {
        return in_array($this->status, ['scheduled', 'active']);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
