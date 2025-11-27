<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Checkpoint;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckpointController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $projectQuery = Project::orderBy('name');
        $checkpointQuery = Checkpoint::with('project')->orderBy('project_id')->orderBy('title');

        if ($user->isProjectAdmin() && $user->active_project_id) {
            $checkpointQuery->where('project_id', $user->active_project_id);
            $projectQuery->where('id', $user->active_project_id);
        } elseif ($request->filled('project_id')) {
            $checkpointQuery->where('project_id', $request->integer('project_id'));
        }

        $checkpoints = $checkpointQuery->paginate(30)->withQueryString();
        $projects = $projectQuery->get();

        return view('admin.patrol.checkpoints.index', compact('checkpoints', 'projects'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $projectQuery = Project::orderBy('name');

        if ($user->isProjectAdmin() && $user->active_project_id) {
            $projectQuery->where('id', $user->active_project_id);
        }

        $projects = $projectQuery->get();

        return view('admin.patrol.checkpoints.create', compact('projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'post_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($user->isProjectAdmin() && $user->active_project_id && $data['project_id'] !== $user->active_project_id) {
            abort(403);
        }

        $checkpoint = new Checkpoint($data);
        $checkpoint->code = $this->generateCode($data['project_id']);
        $checkpoint->save();

        return redirect()->route('admin.patrol.checkpoints.index')->with('status', 'Checkpoint berhasil dibuat.');
    }

    public function edit(Request $request, Checkpoint $checkpoint): View
    {
        $user = $request->user();

        if ($user->isProjectAdmin() && $user->active_project_id && $checkpoint->project_id !== $user->active_project_id) {
            abort(403);
        }

        $projectQuery = Project::orderBy('name');
        if ($user->isProjectAdmin() && $user->active_project_id) {
            $projectQuery->where('id', $user->active_project_id);
        }

        $projects = $projectQuery->get();

        return view('admin.patrol.checkpoints.edit', compact('checkpoint', 'projects'));
    }

    public function update(Request $request, Checkpoint $checkpoint): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'post_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($user->isProjectAdmin() && $user->active_project_id && $checkpoint->project_id !== $user->active_project_id) {
            abort(403);
        }

        $checkpoint->update($data);

        return redirect()->route('admin.patrol.checkpoints.index')->with('status', 'Checkpoint berhasil diperbarui.');
    }

    public function destroy(Request $request, Checkpoint $checkpoint): RedirectResponse
    {
        $user = $request->user();

        if ($user->isProjectAdmin() && $user->active_project_id && $checkpoint->project_id !== $user->active_project_id) {
            abort(403);
        }

        $checkpoint->delete();

        return redirect()->route('admin.patrol.checkpoints.index')->with('status', 'Checkpoint berhasil dihapus.');
    }

    public function print(Request $request, Checkpoint $checkpoint)
    {
        $user = $request->user();

        if ($user->isProjectAdmin() && $user->active_project_id && $checkpoint->project_id !== $user->active_project_id) {
            abort(403);
        }

        return view('admin.patrol.checkpoints.print', compact('checkpoint'));
    }

    public function printAll(Request $request)
    {
        $user = $request->user();

        $checkpointQuery = Checkpoint::with('project')->orderBy('project_id')->orderBy('title');

        if ($user->isProjectAdmin() && $user->active_project_id) {
            $checkpointQuery->where('project_id', $user->active_project_id);
        } elseif ($request->filled('project_id')) {
            $checkpointQuery->where('project_id', $request->integer('project_id'));
        }

        $checkpoints = $checkpointQuery->get();

        if ($checkpoints->isEmpty()) {
            return redirect()->route('admin.patrol.checkpoints.index')->with('status', 'Tidak ada checkpoint untuk dicetak.');
        }

        return view('admin.patrol.checkpoints.print-all', compact('checkpoints'));
    }

    private function generateCode(int $projectId): string
    {
        do {
            $code = 'CP-'.$projectId.'-'.strtoupper(Str::random(6));
        } while (Checkpoint::where('code', $code)->exists());

        return $code;
    }
}
