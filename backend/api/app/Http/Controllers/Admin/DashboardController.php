<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Checkpoint;
use App\Models\PatrolLog;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Analytics Data
        $analytics = $this->getAnalyticsData($user);

        return view('admin.dashboard', [
            'totalGuards' => $totalGuards,
            'totalAdmins' => $totalAdmins,
            'totalProjects' => $totalProjects,
            'attendanceToday' => $attendanceToday,
            'patrolToday' => $patrolToday,
            'todayAttendanceList' => $todayAttendanceList,
            'patrolPoints' => $patrolPoints,
            'analytics' => $analytics,
        ]);
    }

    private function getAnalyticsData($user): array
    {
        $projectFilter = $user->isProjectAdmin() && $user->active_project_id ? $user->active_project_id : null;

        // Attendance data for last 30 days
        $attendanceByDay = $this->getAttendanceByDay($projectFilter);
        
        // Patrol statistics
        $patrolStats = $this->getPatrolStats($projectFilter);
        
        // Attendance by project (top 5)
        $attendanceByProject = $this->getAttendanceByProject($projectFilter);

        // Patrol by type
        $patrolByType = $this->getPatrolByType($projectFilter);

        return [
            'attendanceByDay' => $attendanceByDay,
            'patrolStats' => $patrolStats,
            'attendanceByProject' => $attendanceByProject,
            'patrolByType' => $patrolByType,
        ];
    }

    private function getAttendanceByDay(?int $projectId): array
    {
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $query = AttendanceLog::select(
                DB::raw('DATE(occurred_at) as date'),
                DB::raw('SUM(CASE WHEN type = "clock_in" THEN 1 ELSE 0 END) as clock_in'),
                DB::raw('SUM(CASE WHEN type = "clock_out" THEN 1 ELSE 0 END) as clock_out')
            )
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(occurred_at)'))
            ->orderBy('date');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $data = $query->get()->keyBy('date');

        // Fill missing dates
        $result = ['labels' => [], 'clockIn' => [], 'clockOut' => []];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $result['labels'][] = $currentDate->format('d M');
            $result['clockIn'][] = (int) ($data[$dateStr]->clock_in ?? 0);
            $result['clockOut'][] = (int) ($data[$dateStr]->clock_out ?? 0);
            $currentDate->addDay();
        }

        return $result;
    }

    private function getPatrolStats(?int $projectId): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $thisMonthQuery = PatrolLog::where('occurred_at', '>=', $thisMonth);
        $lastMonthQuery = PatrolLog::whereBetween('occurred_at', [$lastMonth, $lastMonthEnd]);

        if ($projectId) {
            $thisMonthQuery->where('project_id', $projectId);
            $lastMonthQuery->where('project_id', $projectId);
        }

        $thisMonthCount = $thisMonthQuery->count();
        $lastMonthCount = $lastMonthQuery->count();

        $percentChange = $lastMonthCount > 0 
            ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) 
            : 0;

        // Count checkpoints visited this month
        $checkpointsVisited = PatrolLog::where('occurred_at', '>=', $thisMonth)
            ->whereNotNull('checkpoint_id')
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->distinct('checkpoint_id')
            ->count('checkpoint_id');

        $totalCheckpoints = Checkpoint::when($projectId, fn($q) => $q->where('project_id', $projectId))->count();

        return [
            'thisMonth' => $thisMonthCount,
            'lastMonth' => $lastMonthCount,
            'percentChange' => $percentChange,
            'checkpointsVisited' => $checkpointsVisited,
            'totalCheckpoints' => $totalCheckpoints,
            'coveragePercent' => $totalCheckpoints > 0 ? round(($checkpointsVisited / $totalCheckpoints) * 100, 1) : 0,
        ];
    }

    private function getAttendanceByProject(?int $projectId): array
    {
        $startDate = Carbon::now()->startOfMonth();

        $query = AttendanceLog::select('project_id', DB::raw('COUNT(*) as count'))
            ->where('occurred_at', '>=', $startDate)
            ->groupBy('project_id')
            ->orderByDesc('count')
            ->limit(5);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $data = $query->with('project:id,name')->get();

        return [
            'labels' => $data->pluck('project.name')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    private function getPatrolByType(?int $projectId): array
    {
        $startDate = Carbon::now()->startOfMonth();

        $query = PatrolLog::select('type', DB::raw('COUNT(*) as count'))
            ->where('occurred_at', '>=', $startDate)
            ->groupBy('type');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $data = $query->get()->keyBy('type');

        return [
            'patrol' => (int) ($data['patrol']->count ?? 0),
            'sos' => (int) ($data['sos']->count ?? 0),
            'incident' => (int) ($data['incident']->count ?? 0),
        ];
    }
}
