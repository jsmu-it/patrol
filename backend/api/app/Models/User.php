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
    public const ROLE_HRD = 'HRD';
    public const ROLE_PAYROLL = 'PAYROLL';
    public const ROLE_CMS = 'CMS';

    public static function adminRoles(): array
    {
        return [
            self::ROLE_SUPERADMIN,
            self::ROLE_ADMIN,
            self::ROLE_PROJECT_ADMIN,
            self::ROLE_HRD,
            self::ROLE_PAYROLL,
            self::ROLE_CMS,
        ];
    }

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
        return $this->role === self::ROLE_PROJECT_ADMIN || ($this->role === self::ROLE_ADMIN && $this->active_project_id !== null);
    }

    public function isGuard(): bool
    {
        return $this->role === self::ROLE_GUARD;
    }

    public function isHrd(): bool
    {
        return $this->role === self::ROLE_HRD;
    }

    public function isPayroll(): bool
    {
        return $this->role === self::ROLE_PAYROLL;
    }

    public function isCms(): bool
    {
        return $this->role === self::ROLE_CMS;
    }

    public function canAccessMenu(string $menu): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $menuAccess = [
            'dashboard' => [self::ROLE_ADMIN, self::ROLE_PROJECT_ADMIN],
            'users' => [self::ROLE_ADMIN, self::ROLE_PROJECT_ADMIN],
            'projects' => [self::ROLE_ADMIN, self::ROLE_PROJECT_ADMIN],
            'patrol' => [self::ROLE_ADMIN, self::ROLE_PROJECT_ADMIN],
            'shifts' => [self::ROLE_ADMIN, self::ROLE_PROJECT_ADMIN],
            'reports' => [self::ROLE_ADMIN, self::ROLE_PROJECT_ADMIN],
            'approvals' => [self::ROLE_ADMIN, self::ROLE_PROJECT_ADMIN],
            'broadcast' => [self::ROLE_ADMIN, self::ROLE_PROJECT_ADMIN],
            'hrd' => [self::ROLE_HRD],
            'payroll' => [self::ROLE_PAYROLL],
            'pkwt' => [self::ROLE_HRD],
            'careers' => [self::ROLE_HRD],
            'cms' => [self::ROLE_CMS],
            'settings' => [self::ROLE_CMS],
        ];

        return in_array($this->role, $menuAccess[$menu] ?? [], true);
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
