<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'active_project_id',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public const ROLE_SUPERADMIN = 'SUPERADMIN';

    public const ROLE_ADMIN = 'ADMIN';

    public const ROLE_PROJECT_ADMIN = 'PROJECT_ADMIN';

    public const ROLE_GUARD = 'GUARD';

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPERADMIN, self::ROLE_PROJECT_ADMIN], true);
    }

    public function isProjectAdmin(): bool
    {
        // If role is strictly PROJECT_ADMIN, OR if role is ADMIN but assigned to a specific project
        return $this->role === self::ROLE_PROJECT_ADMIN || ($this->role === self::ROLE_ADMIN && $this->active_project_id !== null);
    }

    public function isGuard(): bool
    {
        return $this->role === self::ROLE_GUARD;
    }

    public function activeProject()
    {
        return $this->belongsTo(Project::class, 'active_project_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function patrolLogs()
    {
        return $this->hasMany(PatrolLog::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }
}
