<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Checkpoint;
use App\Models\PatrolLog;
use App\Models\Project;
use App\Models\User;
use App\Reports\AttendanceReportBuilder;
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

        // Get accessible project IDs for current user
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        // Filter if user has limited project access
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $guardsQuery->whereIn('active_project_id', $accessibleProjectIds);
            $projectsQuery->whereIn('id', $accessibleProjectIds);
            $attendanceQuery->whereIn('project_id', $accessibleProjectIds);
            $patrolQuery->whereIn('project_id', $accessibleProjectIds);
            $checkpointsQuery->whereIn('project_id', $accessibleProjectIds);
        } 
        // Fallback: Filter if Project Admin (legacy support)
        elseif ($user->isProjectAdmin() && $user->active_project_id) {
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
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        // Determine filter: accessible project IDs or null (all projects for SUPERADMIN)
        $projectFilter = $user->isSuperAdmin() ? null : $accessibleProjectIds;

        // Attendance data for last 30 days
        $attendanceByDay = $this->getAttendanceByDay($projectFilter);
        
        // Patrol statistics
        $patrolStats = $this->getPatrolStats($projectFilter);
        
        // Attendance by project (top 5)
        $attendanceByProject = $this->getAttendanceByProject($projectFilter);

        // Patrol by type
        $patrolByType = $this->getPatrolByType($projectFilter);

        // Employee performance
        $performance = $this->getAveragePerformance($projectFilter);

        return [
            'attendanceByDay' => $attendanceByDay,
            'patrolStats' => $patrolStats,
            'attendanceByProject' => $attendanceByProject,
            'patrolByType' => $patrolByType,
            'performance' => $performance,
        ];
    }

    private function getAveragePerformance($projectFilter): array
    {
        $startDate = CarbonImmutable::now()->startOfMonth();
        $endDate = CarbonImmutable::now()->endOfDay();
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $guardsQuery = User::where('role', User::ROLE_GUARD);
        if ($projectFilter !== null) {
            $guardsQuery->whereIn('active_project_id', is_array($projectFilter) ? $projectFilter : [$projectFilter]);
        }
        $guards = $guardsQuery->get();

        if ($guards->isEmpty()) {
            return ['average' => 0, 'count' => 0];
        }

        $builder = new AttendanceReportBuilder();
        $totalPercentage = 0;

        foreach ($guards as $guard) {
            $filters = [
                'from' => $startDate,
                'to' => $endDate,
                'project_id' => null,
                'user_id' => $guard->id,
            ];
            
            $sessions = $builder->buildCollection($filters);
            
            $presentDays = $sessions->filter(function($session) {
                return $session['clock_in_time'] !== '-' || str_contains($session['status'] ?? '', 'Cuti');
            })->count();

            $totalPercentage += ($totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0);
        }

        return [
            'average' => round($totalPercentage / $guards->count(), 1),
            'count' => $guards->count()
        ];
    }

    private function getAttendanceByDay($projectFilter): array
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

        if ($projectFilter !== null) {
            $query->whereIn('project_id', is_array($projectFilter) ? $projectFilter : [$projectFilter]);
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

    private function getPatrolStats($projectFilter): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $thisMonthQuery = PatrolLog::where('occurred_at', '>=', $thisMonth);
        $lastMonthQuery = PatrolLog::whereBetween('occurred_at', [$lastMonth, $lastMonthEnd]);

        if ($projectFilter !== null) {
            $projectIds = is_array($projectFilter) ? $projectFilter : [$projectFilter];
            $thisMonthQuery->whereIn('project_id', $projectIds);
            $lastMonthQuery->whereIn('project_id', $projectIds);
        }

        $thisMonthCount = $thisMonthQuery->count();
        $lastMonthCount = $lastMonthQuery->count();

        $percentChange = $lastMonthCount > 0 
            ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1) 
            : 0;

        // Count checkpoints visited this month
        $checkpointsVisited = PatrolLog::where('occurred_at', '>=', $thisMonth)
            ->whereNotNull('checkpoint_id')
            ->when($projectFilter !== null, fn($q) => $q->whereIn('project_id', is_array($projectFilter) ? $projectFilter : [$projectFilter]))
            ->distinct('checkpoint_id')
            ->count('checkpoint_id');

        $totalCheckpoints = Checkpoint::when($projectFilter !== null, fn($q) => $q->whereIn('project_id', is_array($projectFilter) ? $projectFilter : [$projectFilter]))->count();

        return [
            'thisMonth' => $thisMonthCount,
            'lastMonth' => $lastMonthCount,
            'percentChange' => $percentChange,
            'checkpointsVisited' => $checkpointsVisited,
            'totalCheckpoints' => $totalCheckpoints,
            'coveragePercent' => $totalCheckpoints > 0 ? round(($checkpointsVisited / $totalCheckpoints) * 100, 1) : 0,
        ];
    }

    private function getAttendanceByProject($projectFilter): array
    {
        $startDate = Carbon::now()->startOfMonth();

        $query = AttendanceLog::select('project_id', DB::raw('COUNT(*) as count'))
            ->where('occurred_at', '>=', $startDate)
            ->groupBy('project_id')
            ->orderByDesc('count')
            ->limit(5);

        if ($projectFilter !== null) {
            $query->whereIn('project_id', is_array($projectFilter) ? $projectFilter : [$projectFilter]);
        }

        $data = $query->with('project:id,name')->get();

        return [
            'labels' => $data->pluck('project.name')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    private function getPatrolByType($projectFilter): array
    {
        $startDate = Carbon::now()->startOfMonth();

        $query = PatrolLog::select('type', DB::raw('COUNT(*) as count'))
            ->where('occurred_at', '>=', $startDate)
            ->groupBy('type');

        if ($projectFilter !== null) {
            $query->whereIn('project_id', is_array($projectFilter) ? $projectFilter : [$projectFilter]);
        }

        $data = $query->get()->keyBy('type');

        return [
            'patrol' => (int) ($data['patrol']->count ?? 0),
            'sos' => (int) ($data['sos']->count ?? 0),
            'incident' => (int) ($data['incident']->count ?? 0),
        ];
    }
}
