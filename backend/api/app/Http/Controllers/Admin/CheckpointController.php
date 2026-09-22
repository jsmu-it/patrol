<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Checkpoint;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Imports\CheckpointImport;
use App\Exports\CheckpointTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckpointController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $projectQuery = Project::orderBy('name');
        $checkpointQuery = Checkpoint::with('project')->orderBy('project_id')->orderBy('title');

        // Get accessible project IDs for current user
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        // Filter if user has limited project access (not SUPERADMIN)
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $checkpointQuery->whereIn('project_id', $accessibleProjectIds);
            $projectQuery->whereIn('id', $accessibleProjectIds);
        }
        // Filter if Project Admin role with active project
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id) {
            $checkpointQuery->where('project_id', $user->active_project_id);
            $projectQuery->where('id', $user->active_project_id);
        }
        
        // Additional filter by selected project
        if ($request->filled('project_id')) {
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

        // Get accessible project IDs for current user
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        // Filter if user has limited project access (not SUPERADMIN)
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $projectQuery->whereIn('id', $accessibleProjectIds);
        }
        // Filter if Project Admin role with active project
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id) {
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

        // Nilai dari formulir selalu berupa teks ("6"), sedangkan daftar project
        // yang boleh diakses berisi bilangan (6). Dibandingkan secara ketat,
        // keduanya tidak pernah sama — admin project pun ikut tertolak. Jadi
        // disamakan tipenya dulu, bukan pembandingnya yang dilonggarkan.
        $data['project_id'] = (int) $data['project_id'];

        // Check if user has access to the selected project
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            if (!in_array($data['project_id'], $accessibleProjectIds, true)) {
                abort(403, 'You do not have access to this project.');
            }
        }
        // Check if Project Admin role - restrict to active project only
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id && $data['project_id'] !== (int) $user->active_project_id) {
            abort(403, 'You can only create checkpoints for your active project.');
        }

        $checkpoint = new Checkpoint($data);
        $checkpoint->code = $this->generateCode($data['project_id']);
        $checkpoint->save();

        return redirect()->route('admin.patrol.checkpoints.index')->with('status', 'Checkpoint berhasil dibuat.');
    }

    public function edit(Request $request, Checkpoint $checkpoint): View
    {
        $user = $request->user();

        // Check if user has access to the checkpoint's project
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            if (!in_array($checkpoint->project_id, $accessibleProjectIds, true)) {
                abort(403, 'You do not have access to this checkpoint.');
            }
        }
        // Check if Project Admin role - restrict to active project only
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id && $checkpoint->project_id !== $user->active_project_id) {
            abort(403, 'You can only edit checkpoints from your active project.');
        }

        $projectQuery = Project::orderBy('name');
        
        // Filter available projects
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $projectQuery->whereIn('id', $accessibleProjectIds);
        }
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id) {
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
            'radius_meters' => ['required', 'integer', 'min:1'],
        ]);

        // Check if user has access to the checkpoint's project
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            if (!in_array($checkpoint->project_id, $accessibleProjectIds, true) || !in_array((int) $data['project_id'], $accessibleProjectIds, true)) {
                abort(403, 'You do not have access to this checkpoint or target project.');
            }
        }
        // Check if Project Admin role - restrict to active project only  
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id && $checkpoint->project_id !== $user->active_project_id) {
            abort(403, 'You can only update checkpoints from your active project.');
        }

        $checkpoint->update($data);

        return redirect()->route('admin.patrol.checkpoints.index')->with('status', 'Checkpoint berhasil diperbarui.');
    }

    public function destroy(Request $request, Checkpoint $checkpoint): RedirectResponse
    {
        $user = $request->user();

        // Check if user has access to the checkpoint's project
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            if (!in_array($checkpoint->project_id, $accessibleProjectIds, true)) {
                abort(403, 'You do not have access to this checkpoint.');
            }
        }
        // Check if Project Admin role - restrict to active project only
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id && $checkpoint->project_id !== $user->active_project_id) {
            abort(403, 'You can only delete checkpoints from your active project.');
        }

        $checkpoint->delete();

        return redirect()->route('admin.patrol.checkpoints.index')->with('status', 'Checkpoint berhasil dihapus.');
    }

    public function print(Request $request, Checkpoint $checkpoint)
    {
        $user = $request->user();

        // Check if user has access to the checkpoint's project
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            if (!in_array($checkpoint->project_id, $accessibleProjectIds, true)) {
                abort(403, 'You do not have access to this checkpoint.');
            }
        }
        // Check if Project Admin role - restrict to active project only
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id && $checkpoint->project_id !== $user->active_project_id) {
            abort(403, 'You can only print checkpoints from your active project.');
        }

        return view('admin.patrol.checkpoints.print', compact('checkpoint'));
    }

    public function printAll(Request $request)
    {
        $user = $request->user();

        $checkpointQuery = Checkpoint::with('project')->orderBy('project_id')->orderBy('title');

        // Get accessible project IDs for current user
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        
        // Filter if user has limited project access (not SUPERADMIN)
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $checkpointQuery->whereIn('project_id', $accessibleProjectIds);
        }
        // Filter if Project Admin role with active project
        elseif ($user->role === User::ROLE_PROJECT_ADMIN && $user->active_project_id) {
            $checkpointQuery->where('project_id', $user->active_project_id);
        }
        
        // Additional filter by selected project
        if ($request->filled('project_id')) {
            $checkpointQuery->where('project_id', $request->integer('project_id'));
        }

        $checkpoints = $checkpointQuery->get();

        if ($checkpoints->isEmpty()) {
            return redirect()->route('admin.patrol.checkpoints.index')->with('status', 'Tidak ada checkpoint untuk dicetak.');
        }

        return view('admin.patrol.checkpoints.print-all', compact('checkpoints'));
    }

    public function showImportForm(Request $request): View
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $user->role !== User::ROLE_PROJECT_ADMIN) {
            abort(403);
        }

        return view('admin.patrol.checkpoints.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && !$user->isSuperAdmin() && $user->role !== User::ROLE_PROJECT_ADMIN) {
            abort(403);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new CheckpointImport(
            $user->isSuperAdmin() ? null : $user->getAccessibleProjectIds()
        );

        Excel::import($import, $request->file('file'));

        $pesan = 'Import lokasi patroli berhasil diproses.';
        if ($import->dilewatiTanpaHak > 0) {
            $pesan .= ' ' . $import->dilewatiTanpaHak . ' baris dilewati karena project-nya di luar hak akses Anda.';
        }

        return redirect()->route('admin.patrol.checkpoints.index')->with('status', $pesan);
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new CheckpointTemplateExport(), 'template_lokasi_patroli.xlsx');
    }

    private function generateCode(int $projectId): string
    {
        do {
            $code = 'CP-'.$projectId.'-'.strtoupper(Str::random(6));
        } while (Checkpoint::where('code', $code)->exists());

        return $code;
    }
}
