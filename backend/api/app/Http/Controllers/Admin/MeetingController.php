<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Services\LiveKitTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $query = Meeting::with('host')->orderByDesc('created_at');

        if ($request->filled('status') && in_array($request->status, ['scheduled', 'active', 'ended'])) {
            $query->where('status', $request->status);
        }

        $meetings = $query->paginate(20)->withQueryString();

        return view('admin.meetings.index', compact('meetings'));
    }

    public function create()
    {
        return view('admin.meetings.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'password' => 'nullable|string|max:50',
            'max_participants' => 'required|integer|min:2|max:500',
            'scheduled_at' => 'nullable|date',
            'settings.mute_on_join' => 'nullable|boolean',
            'settings.disable_camera_on_join' => 'nullable|boolean',
            'settings.enable_lobby' => 'nullable|boolean',
            'settings.enable_recording' => 'nullable|boolean',
        ]);

        $settings = [
            'mute_on_join' => $request->boolean('settings.mute_on_join'),
            'disable_camera_on_join' => $request->boolean('settings.disable_camera_on_join'),
            'enable_lobby' => $request->boolean('settings.enable_lobby'),
            'enable_recording' => $request->boolean('settings.enable_recording'),
        ];

        Meeting::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'slug' => Meeting::generateSlug($data['title']),
            'room_name' => Meeting::generateRoomName($data['title']),
            'password' => $data['password'] ?? null,
            'host_id' => auth()->id(),
            'status' => 'scheduled',
            'max_participants' => $data['max_participants'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'settings' => $settings,
        ]);

        return redirect()->route('admin.meetings.index')
            ->with('status', 'Meeting berhasil dibuat.');
    }

    public function edit(Meeting $meeting)
    {
        return view('admin.meetings.edit', compact('meeting'));
    }

    public function update(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'password' => 'nullable|string|max:50',
            'max_participants' => 'required|integer|min:2|max:500',
            'scheduled_at' => 'nullable|date',
            'settings.mute_on_join' => 'nullable|boolean',
            'settings.disable_camera_on_join' => 'nullable|boolean',
            'settings.enable_lobby' => 'nullable|boolean',
            'settings.enable_recording' => 'nullable|boolean',
        ]);

        $settings = [
            'mute_on_join' => $request->boolean('settings.mute_on_join'),
            'disable_camera_on_join' => $request->boolean('settings.disable_camera_on_join'),
            'enable_lobby' => $request->boolean('settings.enable_lobby'),
            'enable_recording' => $request->boolean('settings.enable_recording'),
        ];

        $meeting->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'password' => $data['password'] ?? null,
            'max_participants' => $data['max_participants'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'settings' => $settings,
        ]);

        return redirect()->route('admin.meetings.index')
            ->with('status', 'Meeting berhasil diperbarui.');
    }

    public function destroy(Meeting $meeting)
    {
        $meeting->delete();

        return redirect()->route('admin.meetings.index')
            ->with('status', 'Meeting berhasil dihapus.');
    }

    public function toggleStatus(Meeting $meeting)
    {
        if ($meeting->status === 'scheduled' || $meeting->status === 'ended') {
            $meeting->update([
                'status' => 'active',
                'started_at' => now(),
                'ended_at' => null,
            ]);
            $message = 'Meeting dimulai.';
        } else {
            $meeting->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);
            $message = 'Meeting diakhiri.';
        }

        return redirect()->route('admin.meetings.index')
            ->with('status', $message);
    }

    public function room(Meeting $meeting)
    {
        // Auto-start meeting if scheduled
        if ($meeting->status === 'scheduled') {
            $meeting->update([
                'status' => 'active',
                'started_at' => now(),
            ]);
        }

        $userName = auth()->user()->name;
        $tokenService = new LiveKitTokenService();
        $token = $tokenService->generateToken(
            roomName: $meeting->room_name,
            participantName: $userName,
            isAdmin: true
        );
        $livekitUrl = config('services.livekit.url');

        return view('admin.meetings.room', compact('meeting', 'userName', 'token', 'livekitUrl'));
    }

    /**
     * Generate a LiveKit token for a public participant (AJAX endpoint).
     */
    public function generateToken(Request $request, Meeting $meeting)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'password' => $meeting->password ? 'required|string' : 'nullable',
        ]);

        // Verify password
        if ($meeting->password && $request->password !== $meeting->password) {
            return response()->json(['error' => 'Password salah.'], 403);
        }

        // Check meeting status
        if ($meeting->status === 'ended') {
            return response()->json(['error' => 'Meeting sudah berakhir.'], 410);
        }

        $tokenService = new LiveKitTokenService();
        $token = $tokenService->generateToken(
            roomName: $meeting->room_name,
            participantName: $request->name,
            isAdmin: false
        );

        return response()->json([
            'token' => $token,
            'url' => config('services.livekit.url'),
            'room_name' => $meeting->room_name,
        ]);
    }

    /**
     * End a meeting (host only) — marks as ended in DB.
     */
    public function endMeeting(Meeting $meeting)
    {
        $meeting->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Meeting telah diakhiri.']);
    }
}
