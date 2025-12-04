<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Project::orderBy('name');

        if ($user->isProjectAdmin() && $user->active_project_id) {
            $query->where('id', $user->active_project_id);
        }

        $projects = $query->paginate(20);

        return view('admin.projects.index', compact('projects'));
    }

    public function create(Request $request): View
    {
        if ($request->user()->isProjectAdmin()) {
            abort(403, 'Project Admin tidak dapat membuat project baru.');
        }
        return view('admin.projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->isProjectAdmin()) {
            abort(403, 'Project Admin tidak dapat membuat project baru.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        Project::create($data);

        return redirect()->route('admin.projects.index')->with('status', 'Project berhasil ditambahkan.');
    }

    public function edit(Request $request, Project $project): View
    {
        $user = $request->user();
        if ($user->isProjectAdmin() && $user->active_project_id !== $project->id) {
            abort(403);
        }

        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        if ($user->isProjectAdmin() && $user->active_project_id !== $project->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $project->update($data);

        return redirect()->route('admin.projects.index')->with('status', 'Project berhasil diperbarui.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        if ($request->user()->isProjectAdmin()) {
            abort(403, 'Project Admin tidak dapat menghapus project.');
        }

        $project->delete();

        return redirect()->route('admin.projects.index')->with('status', 'Project berhasil dihapus.');
    }

    public function editShifts(Request $request, Project $project): View
    {
        $user = $request->user();
        if ($user->isProjectAdmin() && $user->active_project_id !== $project->id) {
            abort(403);
        }

        $shifts = Shift::orderBy('start_time')->get();
        $activeShiftIds = $project->shifts()->pluck('shifts.id')->all();

        return view('admin.projects.shifts', compact('project', 'shifts', 'activeShiftIds'));
    }

    public function updateShifts(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        if ($user->isProjectAdmin() && $user->active_project_id !== $project->id) {
            abort(403);
        }

        $data = $request->validate([
            'shift_ids' => ['array'],
            'shift_ids.*' => ['integer', 'exists:shifts,id'],
        ]);

        $shiftIds = $data['shift_ids'] ?? [];

        $syncData = [];
        foreach ($shiftIds as $shiftId) {
            $syncData[$shiftId] = ['is_active' => true];
        }

        $project->shifts()->sync($syncData);

        return redirect()->route('admin.projects.index')->with('status', 'Shift project berhasil diperbarui.');
    }

    public function editPkwt(Request $request, Project $project): View
    {
        $user = $request->user();
        if ($user->isProjectAdmin() && $user->active_project_id !== $project->id) {
            abort(403);
        }

        return view('admin.projects.pkwt', compact('project'));
    }

    public function updatePkwt(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        if ($user->isProjectAdmin() && $user->active_project_id !== $project->id) {
            abort(403);
        }

        $data = $request->validate([
            'pkwt_template' => ['nullable', 'string'],
        ]);

        $project->update($data);

        return redirect()->route('admin.projects.index')->with('status', 'Template PKWT berhasil diperbarui.');
    }
}
