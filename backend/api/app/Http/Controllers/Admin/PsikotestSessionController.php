<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\KraepelinQuestion;
use App\Models\PapikostikQuestion;
use App\Models\PsikotestSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PsikotestSessionController extends Controller
{
    public function index(Request $request): View
    {
        $query = PsikotestSession::with(['user'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('test_type')) {
            $query->where('test_type', $request->input('test_type'));
        }

        $sessions = $query->paginate(15)->withQueryString();

        return view('admin.psikotest.sessions.index', compact('sessions'));
    }

    public function create(): View
    {
        $kraepelinSets = KraepelinQuestion::where('is_active', true)->get();
        $papikostikSets = PapikostikQuestion::where('is_active', true)->get();
        
        // Get applicants that haven't been rejected
        $applicants = JobApplication::whereIn('status', ['pending', 'reviewed', 'accepted'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'phone']);

        return view('admin.psikotest.sessions.create', compact('kraepelinSets', 'papikostikSets', 'applicants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'participant_name' => ['required', 'string', 'max:255'],
            'participant_email' => ['nullable', 'email', 'max:255'],
            'participant_phone' => ['nullable', 'string', 'max:20'],
            'test_type' => ['required', 'in:kraepelin,papikostik'],
            'question_set_id' => ['required', 'integer'],
            'expires_days' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        // Validate question set exists
        if ($data['test_type'] === 'kraepelin') {
            KraepelinQuestion::findOrFail($data['question_set_id']);
        } else {
            PapikostikQuestion::findOrFail($data['question_set_id']);
        }

        $session = PsikotestSession::create([
            'participant_name' => $data['participant_name'],
            'participant_email' => $data['participant_email'],
            'participant_phone' => $data['participant_phone'],
            'test_type' => $data['test_type'],
            'question_set_id' => $data['question_set_id'],
            'expires_at' => now()->addDays($data['expires_days']),
        ]);

        return redirect()->route('admin.psikotest.sessions.show', $session)
            ->with('status', 'Sesi test berhasil dibuat. Kirim link berikut ke peserta.');
    }

    public function show(PsikotestSession $session): View
    {
        $session->load(['result', 'user']);
        
        $testUrl = route('psikotest.take', $session->access_token);
        
        return view('admin.psikotest.sessions.show', compact('session', 'testUrl'));
    }

    public function destroy(PsikotestSession $session): RedirectResponse
    {
        $session->delete();

        return redirect()->route('admin.psikotest.sessions.index')
            ->with('status', 'Sesi test berhasil dihapus.');
    }
}
