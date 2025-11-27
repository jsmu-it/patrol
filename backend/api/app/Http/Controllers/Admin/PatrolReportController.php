<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatrolLog;
use App\Models\Project;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class PatrolReportController extends Controller
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
            $records = $this->buildCollection($filters);
        }

        return view('admin.reports.patrol', [
            'projects' => $projects,
            'guards' => $guards,
            'records' => $records,
            'filters' => $filters,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $filters = $this->validateFilters($request, true);
        $collection = $this->buildCollection($filters);

        $filename = 'patrol-report-'.$this->suffixFromFilters($filters).'.xlsx';

        return Excel::download(new \App\Exports\PatrolReportExport($collection), $filename);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->validateFilters($request, true);
        $collection = $this->buildCollection($filters);

        $pdf = Pdf::loadView('admin.reports.patrol_pdf', [
            'records' => $collection,
            'filters' => $filters,
        ]);

        $filename = 'patrol-report-'.$this->suffixFromFilters($filters).'.pdf';

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

    private function buildCollection(array $filters): Collection
    {
        $query = PatrolLog::query()
            ->with(['user', 'project', 'checkpoint'])
            ->whereBetween('occurred_at', [$filters['from'], $filters['to']])
            ->orderBy('occurred_at');

        if ($filters['project_id']) {
            $query->where('project_id', $filters['project_id']);
        }

        if ($filters['user_id']) {
            $query->where('user_id', $filters['user_id']);
        }

        $logs = $query->get();

        if (! empty($filters['sort_by_project'])) {
            $logs = $logs->sortBy(function (PatrolLog $log) {
                $project = $log->project?->name ?? '';
                $user = $log->user?->name ?? '';
                $time = $log->occurred_at?->format('Y-m-d H:i:s') ?? '';

                return $project.'|'.$user.'|'.$time;
            })->values();
        }

        return $logs->map(function (PatrolLog $log) {
            $typeLabel = match ($log->type) {
                'sos' => 'SOS',
                'incident' => 'Insiden',
                default => 'Patroli',
            };

            return [
                'date' => $log->occurred_at?->toDateString(),
                'time' => $log->occurred_at?->format('H:i:s'),
                'user' => $log->user?->name,
                'username' => $log->user?->username,
                'project' => $log->project?->name,
                'checkpoint' => $log->checkpoint?->code,
                'type' => $typeLabel,
                'title' => $log->title,
                'post_name' => $log->post_name,
                'description' => $log->description,
                'photo_url' => $log->photo_path ? asset('storage/'.$log->photo_path) : null,
            ];
        });
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
