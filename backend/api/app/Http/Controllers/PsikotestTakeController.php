<?php

namespace App\Http\Controllers;

use App\Models\KraepelinQuestion;
use App\Models\PapikostikQuestion;
use App\Models\PsikotestResult;
use App\Models\PsikotestSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PsikotestTakeController extends Controller
{
    /**
     * Show the test entry page (enter access token)
     */
    public function index(): View
    {
        return view('psikotest.index');
    }

    /**
     * Validate token and redirect to test
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $session = PsikotestSession::where('access_token', $request->token)->first();

        if (!$session) {
            return back()->withErrors(['token' => 'Token tidak valid.']);
        }

        if ($session->isExpired()) {
            return back()->withErrors(['token' => 'Token sudah kadaluarsa.']);
        }

        if ($session->isCompleted()) {
            return back()->withErrors(['token' => 'Test sudah selesai dikerjakan.']);
        }

        return redirect()->route('psikotest.take', $session->access_token);
    }

    /**
     * Show the test page
     */
    public function take(string $token): View
    {
        $session = PsikotestSession::where('access_token', $token)->firstOrFail();

        if ($session->isExpired()) {
            abort(403, 'Token sudah kadaluarsa.');
        }

        if ($session->isCompleted()) {
            return view('psikotest.completed', compact('session'));
        }

        // Mark as started if not already
        if ($session->status === 'pending') {
            $session->markAsStarted();
        }

        if ($session->test_type === 'kraepelin') {
            $questionSet = KraepelinQuestion::findOrFail($session->question_set_id);
            return view('psikotest.kraepelin', compact('session', 'questionSet'));
        } else {
            $questionSet = PapikostikQuestion::findOrFail($session->question_set_id);
            return view('psikotest.papikostik', compact('session', 'questionSet'));
        }
    }

    /**
     * Submit test answers
     */
    public function submit(Request $request, string $token)
    {
        $session = PsikotestSession::where('access_token', $token)->firstOrFail();

        if ($session->isCompleted()) {
            return redirect()->route('psikotest.take', $token);
        }

        $answers = $request->input('answers', []);

        if ($session->test_type === 'kraepelin') {
            $questionSet = KraepelinQuestion::findOrFail($session->question_set_id);
            $scores = PsikotestResult::calculateKraepelinScores($answers, $questionSet);
            
            PsikotestResult::create([
                'session_id' => $session->id,
                'raw_answers' => $answers,
                'scores' => $scores['column_scores'],
                'total_correct' => $scores['total_correct'],
                'total_wrong' => $scores['total_wrong'],
                'accuracy_percentage' => $scores['accuracy_percentage'],
                'speed_score' => $scores['speed_score'],
                'consistency_score' => $scores['consistency_score'],
                'endurance_score' => $scores['endurance_score'],
            ]);
        } else {
            $questionSet = PapikostikQuestion::findOrFail($session->question_set_id);
            $scores = PsikotestResult::calculatePapikostikScores($answers, $questionSet);
            
            PsikotestResult::create([
                'session_id' => $session->id,
                'raw_answers' => $answers,
                'scores' => $scores['dimension_scores'],
                'interpretation' => $scores['interpretation'],
            ]);
        }

        $session->markAsCompleted();

        return redirect()->route('psikotest.take', $token);
    }
}
