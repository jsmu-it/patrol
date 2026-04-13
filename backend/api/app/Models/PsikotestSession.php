<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PsikotestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'participant_name',
        'participant_email',
        'participant_phone',
        'test_type',
        'question_set_id',
        'status',
        'access_token',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (PsikotestSession $session) {
            if (empty($session->access_token)) {
                $session->access_token = Str::random(32);
            }
            if (empty($session->expires_at)) {
                $session->expires_at = now()->addDays(7);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function result()
    {
        return $this->hasOne(PsikotestResult::class, 'session_id');
    }

    public function kraepelinQuestionSet()
    {
        return $this->belongsTo(KraepelinQuestion::class, 'question_set_id');
    }

    public function papikostikQuestionSet()
    {
        return $this->belongsTo(PapikostikQuestion::class, 'question_set_id');
    }

    public function getQuestionSet()
    {
        if ($this->test_type === 'kraepelin') {
            return $this->kraepelinQuestionSet;
        }
        return $this->papikostikQuestionSet;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function scopeKraepelin($query)
    {
        return $query->where('test_type', 'kraepelin');
    }

    public function scopePapikostik($query)
    {
        return $query->where('test_type', 'papikostik');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
