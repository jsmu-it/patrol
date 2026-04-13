<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Project::orderBy('name');

        // Filter projects based on user's accessible project IDs
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $query->whereIn('id', $accessibleProjectIds);
        } elseif (!$user->isSuperAdmin() && empty($accessibleProjectIds)) {
            $query->whereRaw('1 = 0'); // No access
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
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !in_array($project->id, $accessibleProjectIds)) {
            abort(403);
        }

        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !in_array($project->id, $accessibleProjectIds)) {
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
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !in_array($project->id, $accessibleProjectIds)) {
            abort(403);
        }

        $shifts = Shift::orderBy('start_time')->get();
        $activeShiftIds = $project->shifts()->pluck('shifts.id')->all();

        return view('admin.projects.shifts', compact('project', 'shifts', 'activeShiftIds'));
    }

    public function updateShifts(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !in_array($project->id, $accessibleProjectIds)) {
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
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !in_array($project->id, $accessibleProjectIds)) {
            abort(403);
        }

        return view('admin.projects.pkwt', compact('project'));
    }

    public function updatePkwt(Request $request, Project $project): RedirectResponse
    {
        $user = $request->user();
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !in_array($project->id, $accessibleProjectIds)) {
            abort(403);
        }

        $data = $request->validate([
            'pkwt_title' => ['nullable', 'string', 'max:255'],
            'pkwt_template' => ['nullable', 'string'],
            'import_file' => ['nullable', 'file', 'mimes:doc,docx,html,txt', 'max:5120'],
        ]);

        // Handle DOCX/DOC file import
        if ($request->hasFile('import_file')) {
            $file = $request->file('import_file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (in_array($extension, ['docx', 'doc'])) {
                // Convert DOCX to HTML using simple approach
                $content = $this->convertDocxToHtml($file->getRealPath());
                if ($content) {
                    $data['pkwt_template'] = $content;
                }
            } else {
                // For HTML/TXT, just read the content
                $data['pkwt_template'] = file_get_contents($file->getRealPath());
            }
        }

        // Only update if not import_only request or has template
        if (!$request->has('import_only') || !empty($data['pkwt_template'])) {
            $project->update([
                'pkwt_title' => $data['pkwt_title'] ?? $project->pkwt_title,
                'pkwt_template' => $data['pkwt_template'] ?? $project->pkwt_template,
            ]);
        }

        return redirect()->route('admin.projects.pkwt.edit', $project)->with('status', 'Template PKWT berhasil diperbarui.');
    }

    /**
     * Convert DOCX file to HTML
     */
    private function convertDocxToHtml(string $filePath): ?string
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) !== true) {
                return null;
            }

            // Read the main document content
            $content = $zip->getFromName('word/document.xml');
            $zip->close();

            if (!$content) {
                return null;
            }

            // Parse XML
            $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOERROR);
            if (!$xml) {
                return null;
            }

            // Register namespaces
            $namespaces = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('w', $namespaces['w'] ?? 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            // Convert to HTML
            $html = '';
            $paragraphs = $xml->xpath('//w:p');

            foreach ($paragraphs as $p) {
                $text = '';
                $runs = $p->xpath('.//w:r');
                
                foreach ($runs as $run) {
                    $run->registerXPathNamespace('w', $namespaces['w'] ?? 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                    $tNodes = $run->xpath('.//w:t');
                    
                    foreach ($tNodes as $t) {
                        $text .= (string)$t;
                    }
                }

                if (!empty(trim($text))) {
                    $html .= '<p>' . htmlspecialchars($text) . '</p>' . "\n";
                }
            }

            return $html ?: null;
        } catch (\Exception $e) {
            \Log::warning('DOCX conversion failed: ' . $e->getMessage());
            return null;
        }
    }
}
