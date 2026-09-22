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

    /**
     * Shift dikelola langsung di halaman Edit Project. Tidak ada lagi halaman
     * terpisah maupun daftar shift global — setiap project memiliki shift
     * sendiri, sehingga shift project lain tidak pernah ikut tampil.
     */
    private function pastikanBisaAkses(Request $request, Project $project): void
    {
        $user = $request->user();
        if (! $user->isSuperAdmin() && ! in_array($project->id, $user->getAccessibleProjectIds())) {
            abort(403);
        }
    }

    private function aturanShift(Project $project, ?Shift $shift = null): array
    {
        return [
            'name'              => ['required', 'string', 'max:100'],
            'start_time'        => ['required', 'date_format:H:i'],
            'end_time'          => ['required', 'date_format:H:i'],
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:180'],
        ];
    }

    public function storeShift(Request $request, Project $project): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $project);
        $data = $request->validate($this->aturanShift($project));

        $project->shifts()->create([
            'name'              => $data['name'],
            'code'              => $this->buatKode($project, $data['name']),
            'start_time'        => $data['start_time'],
            'end_time'          => $data['end_time'],
            'tolerance_minutes' => $data['tolerance_minutes'],
        ]);

        return back()->with('status', 'Shift "' . $data['name'] . '" ditambahkan.');
    }

    public function updateShift(Request $request, Project $project, Shift $shift): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $project);
        abort_unless($shift->project_id === $project->id, 404);

        $data = $request->validate($this->aturanShift($project, $shift));
        $shift->update($data);

        return back()->with('status', 'Shift "' . $shift->name . '" diperbarui.');
    }

    public function destroyShift(Request $request, Project $project, Shift $shift): RedirectResponse
    {
        $this->pastikanBisaAkses($request, $project);
        abort_unless($shift->project_id === $project->id, 404);

        // Riwayat absensi tidak boleh hilang karena shift dihapus.
        $terpakai = \App\Models\AttendanceLog::where('shift_id', $shift->id)->count();
        if ($terpakai > 0) {
            return back()->withErrors([
                'shift' => 'Shift "' . $shift->name . '" tidak bisa dihapus karena sudah dipakai '
                         . $terpakai . ' catatan absensi. Ubah jamnya bila perlu penyesuaian.',
            ]);
        }

        $nama = $shift->name;
        $shift->delete();

        return back()->with('status', 'Shift "' . $nama . '" dihapus.');
    }

    /** Kode unik per project, dipakai internal dan tidak ditampilkan ke admin. */
    private function buatKode(Project $project, string $nama): string
    {
        $dasar = 'SHIFT_' . strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $nama));
        $kode = $dasar;
        $n = 2;
        while (Shift::where('project_id', $project->id)->where('code', $kode)->exists()) {
            $kode = $dasar . '_' . $n++;
        }

        return $kode;
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
