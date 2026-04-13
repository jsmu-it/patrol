<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->recordActivity('created');
        });

        static::updated(function ($model) {
            $model->recordActivity('updated');
        });

        static::deleted(function ($model) {
            $model->recordActivity('deleted');
        });
    }

    protected function recordActivity($event)
    {
        if (!Auth::check()) {
            return;
        }

        $properties = null;
        if ($event === 'updated') {
            $properties = [
                'old' => $this->getOriginal(),
                'new' => $this->getChanges(),
            ];
            // Remove timestamps and hidden fields if needed
        } elseif ($event === 'created') {
             $properties = [
                'new' => $this->getAttributes(),
            ];
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'description' => "User " . Auth::user()->name . " {$event} " . class_basename($this) . " #{$this->id}",
            'event' => $event,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
