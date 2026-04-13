<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PsikotestResult;
use App\Models\PsikotestSession;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class PsikotestResultController extends Controller
{
    public function index(Request $request): View
    {
        $query = PsikotestSession::with(['result', 'user'])
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc');

        // Search by name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('participant_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by test type
        if ($request->filled('test_type')) {
            $query->where('test_type', $request->input('test_type'));
        }

        $sessions = $query->paginate(15)->withQueryString();

        return view('admin.psikotest.dashboard', compact('sessions'));
    }

    public function show(PsikotestSession $session): View
    {
        $session->load(['result', 'user']);
        
        if ($session->test_type === 'kraepelin') {
            $session->load('kraepelinQuestionSet');
        } else {
            $session->load('papikostikQuestionSet');
        }

        return view('admin.psikotest.result', compact('session'));
    }

    public function exportPdf(PsikotestSession $session)
    {
        $session->load(['result', 'user']);
        
        if ($session->test_type === 'kraepelin') {
            $session->load('kraepelinQuestionSet');
            $pdf = Pdf::loadView('admin.psikotest.pdf.kraepelin', compact('session'));
        } else {
            $session->load('papikostikQuestionSet');
            $pdf = Pdf::loadView('admin.psikotest.pdf.papikostik', compact('session'));
        }

        $filename = "hasil_{$session->test_type}_{$session->participant_name}_{$session->completed_at->format('Ymd')}.pdf";
        
        return $pdf->download($filename);
    }
}
