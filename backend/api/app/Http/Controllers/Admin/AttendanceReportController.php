<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Project;
use App\Models\User;
use App\Reports\AttendanceReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $projectsQuery = Project::orderBy('name');
        $guardsQuery = User::where('role', User::ROLE_GUARD)->orderBy('name');

        $accessibleProjectIds = $user->getAccessibleProjectIds();
        $hasLimitedAccess = !$user->isSuperAdmin() && !empty($accessibleProjectIds);

        if ($hasLimitedAccess) {
            $projectsQuery->whereIn('id', $accessibleProjectIds);
            $guardsQuery->whereIn('active_project_id', $accessibleProjectIds);
        }

        $projects = $projectsQuery->get();
        $guards = $guardsQuery->get();

        $filters = $this->validateFilters($request);

        $employees = collect();

        if ($filters) {
            // Query employees from attendance logs (same source as Excel export)
            // This ensures the view list matches the export data exactly
            $logQuery = AttendanceLog::whereBetween('occurred_at', [$filters['from'], $filters['to']])
                ->select('user_id')
                ->distinct();

            if ($filters['project_id']) {
                $logQuery->where('project_id', $filters['project_id']);
            }

            $userIds = $logQuery->pluck('user_id');

            $employees = User::whereIn('id', $userIds)
                ->orderBy('name')
                ->with('activeProject')
                ->get();
        }

        return view('admin.reports.attendance', [
            'projects' => $projects,
            'guards' => $guards,
            'employees' => $employees,
            'filters' => $filters,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->validateFilters($request, true);
        $template = $request->input('template', 'standard');
        
        // Validate template
        if (!in_array($template, ['standard', 'pivot', 'ho', 'summarecon_bogor'])) {
            $template = 'standard';
        }
        
        $builder = new AttendanceReportBuilder();
        $collection = $builder->buildCollection($filters);

        $filename = 'attendance-report-'.$this->suffixFromFilters($filters).'.xlsx';
        
        $projectName = 'Semua Project';
        if ($filters['project_id']) {
            $project = Project::find($filters['project_id']);
            if ($project) $projectName = $project->name;
        }

        $userName = null;
        if ($filters['user_id']) {
            $user = User::find($filters['user_id']);
            if ($user) $userName = $user->name;
        }

        return Excel::download(new \App\Exports\AttendanceReportExport($collection, $filters, $projectName, $userName, $template), $filename);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->validateFilters($request, true);
        $builder = new AttendanceReportBuilder();
        $collection = $builder->buildCollection($filters);

        $pdf = Pdf::loadView('admin.reports.attendance_pdf', [
            'records' => $collection,
            'filters' => $filters,
        ]);

        $filename = 'attendance-report-'.$this->suffixFromFilters($filters).'.pdf';

        return $pdf->download($filename);
    }

    private function validateFilters(Request $request, bool $required = false): ?array
    {
        if (! $required && ! $request->filled('from')) {
            return null;
        }

        $data = $request->validate([
            'from' => [$required ? 'required' : 'nullable', 'date'],
            'to' => [$required ? 'required' : 'nullable', 'date', 'after_or_equal:from'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (! ($data['from'] ?? null)) {
            return null;
        }

        $from = CarbonImmutable::parse($data['from'])->startOfDay();
        $to = CarbonImmutable::parse($data['to'] ?? $data['from'])->endOfDay();

        $user = $request->user();

        $projectId = $data['project_id'] ?? null;
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            // Enforce accessible projects — user can only filter within their assigned projects
            if ($projectId && !in_array($projectId, $accessibleProjectIds)) {
                $projectId = null; // Reset to prevent unauthorized access
            }
        }

        return [
            'from' => $from,
            'to' => $to,
            'project_id' => $projectId,
            'user_id' => $data['user_id'] ?? null,
            'sort_by_project' => $request->boolean('sort_by_project'),
        ];
    }

    private function suffixFromFilters(array $filters): string
    {
        $parts = [
            $filters['from']->format('Ymd'),
            $filters['to']->format('Ymd'),
        ];

        if ($filters['project_id']) {
            $parts[] = 'proj'.$filters['project_id'];
        }

        if ($filters['user_id']) {
            $parts[] = 'user'.$filters['user_id'];
        }

        return Str::slug(implode('-', $parts));
    }

    public function downloadUserAttendance(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'template' => ['nullable', 'string', 'in:standard,pivot,ho,summarecon_bogor'],
        ]);

        $template = $data['template'] ?? 'standard';
        
        $user = User::with(['activeProject', 'profile'])->find($data['user_id']);
        
        $from = CarbonImmutable::parse($data['from'])->startOfDay();
        $to = CarbonImmutable::parse($data['to'])->endOfDay();

        $filters = [
            'from' => $from,
            'to' => $to,
            'project_id' => $data['project_id'] ?? null,
            'user_id' => $user->id,
            'sort_by_project' => false,
        ];

        $builder = new AttendanceReportBuilder();
        $collection = $builder->buildCollection($filters);

        $projectName = $user->activeProject?->name ?? 'Tanpa Project';
        $fileName = 'absen_' . Str::slug($user->name) . '_' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.xlsx';

        return Excel::download(new \App\Exports\AttendanceReportExport($collection, $filters, $projectName, $user->name, $template), $fileName);
    }

    public function performance(Request $request): View
    {
        $user = $request->user();
        $projectsQuery = Project::orderBy('name');
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $projectsQuery->whereIn('id', $accessibleProjectIds);
        }
        
        $projects = $projectsQuery->get();

        $from = $request->input('from') ? CarbonImmutable::parse($request->input('from'))->startOfDay() : CarbonImmutable::now()->startOfMonth();
        $to = $request->input('to') ? CarbonImmutable::parse($request->input('to'))->endOfDay() : CarbonImmutable::now()->endOfDay();
        $projectId = $request->input('project_id');

        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            if ($projectId && !in_array($projectId, $accessibleProjectIds)) {
                $projectId = null;
            }
        }

        $guardsQuery = User::where('role', User::ROLE_GUARD);
        if ($projectId) {
            $guardsQuery->where('active_project_id', $projectId);
        } elseif (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $guardsQuery->whereIn('active_project_id', $accessibleProjectIds);
        }
        
        $guards = $guardsQuery->with(['activeProject', 'profile'])->orderBy('name')->get();

        $builder = new AttendanceReportBuilder();
        $performanceData = [];
        
        $totalDays = $from->diffInDays($to) + 1;

        foreach ($guards as $guard) {
            $filters = [
                'from' => $from,
                'to' => $to,
                'project_id' => null,
                'user_id' => $guard->id,
            ];
            
            $sessions = $builder->buildCollection($filters);
            
            $presentDays = $sessions->filter(function($session) {
                return $session['clock_in_time'] !== '-' || str_contains($session['status'], 'Cuti');
            })->count();

            $percentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;
            
            $performanceData[] = [
                'user' => $guard,
                'present_days' => $presentDays,
                'total_days' => $totalDays,
                'percentage' => $percentage,
            ];
        }

        // Sort by percentage descending
        usort($performanceData, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });

        return view('admin.reports.performance', [
            'projects' => $projects,
            'performanceData' => $performanceData,
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'project_id' => $projectId,
            ],
        ]);
    }
}
