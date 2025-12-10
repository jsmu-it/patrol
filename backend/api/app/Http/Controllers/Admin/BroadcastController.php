<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastNotification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    public function index()
    {
        $notifications = BroadcastNotification::with(['sender', 'targetProject'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.broadcast.index', compact('notifications'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();

        return view('admin.broadcast.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'target' => 'required|in:all,project,role',
            'target_project_id' => 'required_if:target,project|nullable|exists:projects,id',
            'target_role' => 'required_if:target,role|nullable|in:GUARD,ADMIN,PROJECT_ADMIN',
        ]);

        // Get target users
        $usersQuery = User::whereNotNull('fcm_token')->where('fcm_token', '!=', '');

        if ($data['target'] === 'project' && $data['target_project_id']) {
            $usersQuery->where('active_project_id', $data['target_project_id']);
        } elseif ($data['target'] === 'role' && $data['target_role']) {
            $usersQuery->where('role', $data['target_role']);
        }

        $users = $usersQuery->get();

        // Create broadcast record
        $broadcast = BroadcastNotification::create([
            'sent_by' => auth()->id(),
            'title' => $data['title'],
            'message' => $data['message'],
            'target' => $data['target'],
            'target_project_id' => $data['target_project_id'] ?? null,
            'target_role' => $data['target_role'] ?? null,
            'recipients_count' => $users->count(),
            'success_count' => 0,
            'failed_count' => 0,
            'sent_at' => now(),
        ]);

        // Send FCM notifications
        $successCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            if ($this->sendFcmNotification($user->fcm_token, $data['title'], $data['message'])) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        $broadcast->update([
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);

        return redirect()->route('admin.broadcast.index')
            ->with('status', "Notifikasi berhasil dikirim ke {$successCount} dari {$users->count()} penerima.");
    }

    public function show(BroadcastNotification $broadcast)
    {
        $broadcast->load(['sender', 'targetProject']);

        return view('admin.broadcast.show', compact('broadcast'));
    }

    private function sendFcmNotification(string $token, string $title, string $body): bool
    {
        try {
            // Get FCM server key from config/services.php or .env
            $serverKey = config('services.fcm.server_key');

            if (empty($serverKey)) {
                Log::warning('FCM server key not configured');
                return false;
            }

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM notification failed: ' . $e->getMessage());
            return false;
        }
    }
}
