<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CvController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['profile', 'activeProject'])
            ->whereIn('role', ['GUARD', 'ADMIN', 'PROJECT_ADMIN'])
            ->whereHas('profile');

        if ($request->filled('project_id')) {
            $query->where('active_project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('profile', function ($q2) use ($search) {
                      $q2->where('nip', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->get('sort', 'name');
        $sortDir = $request->get('dir', 'asc');

        if ($sortBy === 'project') {
            $query->leftJoin('projects', 'users.active_project_id', '=', 'projects.id')
                  ->orderBy('projects.name', $sortDir)
                  ->select('users.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $users = $query->paginate(20)->withQueryString();
        $projects = Project::orderBy('name')->get();

        return view('admin.hrd.cv.index', compact('users', 'projects'));
    }

    public function show(User $user): View
    {
        $user->load(['profile', 'activeProject']);

        return view('admin.hrd.cv.show', compact('user'));
    }

    public function exportPdf(User $user)
    {
        $user->load(['profile', 'activeProject']);

        $pdf = Pdf::loadView('admin.hrd.cv.pdf', compact('user'));
        $pdf->setPaper('A4', 'portrait');

        $filename = 'CV_' . str_replace(' ', '_', $user->name) . '_' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
