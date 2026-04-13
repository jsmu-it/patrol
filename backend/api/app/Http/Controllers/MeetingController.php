<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    /**
     * Show the public meeting join page.
     */
    public function join(string $slug)
    {
        $meeting = Meeting::with('host')->where('slug', $slug)->firstOrFail();

        return view('meeting.join', compact('meeting'));
    }
}
