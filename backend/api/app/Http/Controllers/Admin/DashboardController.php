<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Checkpoint;
use App\Models\PatrolLog;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = CarbonImmutable::now('UTC')->toDateString();

        // Query Builders
        $guardsQuery = User::where('role', User::ROLE_GUARD);
        $projectsQuery = Project::query();
        $attendanceQuery = AttendanceLog::whereDate('occurred_at', $today);
        $patrolQuery = PatrolLog::whereDate('occurred_at', $today);
        $checkpointsQuery = Checkpoint::with('project')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Filter if Project Admin
        if ($user->isProjectAdmin() && $user->active_project_id) {
            $guardsQuery->where('active_project_id', $user->active_project_id);
            $projectsQuery->where('id', $user->active_project_id);
            $attendanceQuery->where('project_id', $user->active_project_id);
            $patrolQuery->where('project_id', $user->active_project_id);
            $checkpointsQuery->where('project_id', $user->active_project_id);
        }

        $totalGuards = $guardsQuery->count();
        // Only Super Admins see all admins, Project Admin sees only guards in their project
        $totalAdmins = $user->isProjectAdmin() ? 0 : User::where('role', User::ROLE_ADMIN)->count(); 
        $totalProjects = $projectsQuery->count();

        $attendanceToday = $attendanceQuery->count();
        $patrolToday = $patrolQuery->count();
        
        $todayAttendanceList = $attendanceQuery->with(['user', 'project', 'shift'])
            ->orderBy('occurred_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'user_name' => $log->user->name,
                    'project_name' => $log->project->name,
                    'time' => $log->occurred_at->format('H:i'),
                    'type' => $log->type === 'clock_in' ? 'Masuk' : 'Keluar',
                    'shift' => $log->shift->name ?? '-',
                ];
            });

        $patrolPoints = $checkpointsQuery->get()
            ->map(function (Checkpoint $checkpoint) {
                return [
                    'id' => $checkpoint->id,
                    'lat' => (float) $checkpoint->latitude,
                    'lng' => (float) $checkpoint->longitude,
                    'project' => $checkpoint->project?->name,
                    'title' => $checkpoint->title,
                    'post_name' => $checkpoint->post_name,
                ];
            });

        return view('admin.dashboard', [
            'totalGuards' => $totalGuards,
            'totalAdmins' => $totalAdmins,
            'totalProjects' => $totalProjects,
            'attendanceToday' => $attendanceToday,
            'patrolToday' => $patrolToday,
            'todayAttendanceList' => $todayAttendanceList,
            'patrolPoints' => $patrolPoints,
        ]);
    }
}
