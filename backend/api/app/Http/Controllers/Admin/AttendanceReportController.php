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

        if ($user->isProjectAdmin() && $user->active_project_id) {
            $projectsQuery->where('id', $user->active_project_id);
            $guardsQuery->where('active_project_id', $user->active_project_id);
        }

        $projects = $projectsQuery->get();
        $guards = $guardsQuery->get();

        $filters = $this->validateFilters($request);

        $records = collect();

        if ($filters) {
            $builder = new AttendanceReportBuilder();
            $records = $builder->buildCollection($filters);
        }

        return view('admin.reports.attendance', [
            'projects' => $projects,
            'guards' => $guards,
            'records' => $records,
            'filters' => $filters,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->validateFilters($request, true);
        $builder = new AttendanceReportBuilder();
        $collection = $builder->buildCollection($filters);

        $filename = 'attendance-report-'.$this->suffixFromFilters($filters).'.xlsx';

        // Need to update Export class signature or how we call it
        // Current Export expects ($rows, $filters, $projectName)
        
        $projectName = 'Semua Project';
        if ($filters['project_id']) {
            $project = Project::find($filters['project_id']);
            if ($project) $projectName = $project->name;
        }

        return Excel::download(new \App\Exports\AttendanceReportExport($collection, $filters, $projectName), $filename);
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

        $from = CarbonImmutable::parse($data['from'], 'UTC')->startOfDay();
        $to = CarbonImmutable::parse($data['to'] ?? $data['from'], 'UTC')->endOfDay();

        $user = $request->user();

        $projectId = $data['project_id'] ?? null;
        if ($user->isProjectAdmin() && $user->active_project_id) {
            $projectId = $user->active_project_id;
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
}
