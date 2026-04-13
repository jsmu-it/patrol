<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KraepelinQuestion;
use App\Models\PapikostikQuestion;
use App\Models\PsikotestSession;
use Illuminate\View\View;

class PsikotestController extends Controller
{
    public function index(): View
    {
        $stats = [
            'kraepelin_questions' => KraepelinQuestion::where('is_active', true)->count(),
            'papikostik_questions' => PapikostikQuestion::where('is_active', true)->count(),
            'total_sessions' => PsikotestSession::count(),
            'completed_sessions' => PsikotestSession::where('status', 'completed')->count(),
            'pending_sessions' => PsikotestSession::where('status', 'pending')->count(),
        ];

        return view('admin.psikotest.index', compact('stats'));
    }
}
