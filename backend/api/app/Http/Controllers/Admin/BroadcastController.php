<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BroadcastNotification;
use App\Models\Project;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BroadcastController extends Controller
{
    public function __construct(
        private PushNotificationService $pushService
    ) {}

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'target' => 'required|in:all,project,role',
            'target_project_id' => 'required_if:target,project|nullable|exists:projects,id',
            'target_role' => 'required_if:target,role|nullable|in:GUARD,ADMIN,PROJECT_ADMIN',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('broadcast-images', 'public');
        }

        // Get target users
        $usersQuery = User::whereNotNull('fcm_token')->where('fcm_token', '!=', '');

        if ($data['target'] === 'project' && $data['target_project_id']) {
            $usersQuery->where('active_project_id', $data['target_project_id']);
        } elseif ($data['target'] === 'role' && $data['target_role']) {
            $usersQuery->where('role', $data['target_role']);
        }

        $users = $usersQuery->get();
        $tokens = $users->pluck('fcm_token')->filter()->unique()->values()->all();

        // Create broadcast record
        $broadcast = BroadcastNotification::create([
            'sent_by' => auth()->id(),
            'title' => $data['title'],
            'message' => $data['message'],
            'image' => $imagePath,
            'target' => $data['target'],
            'target_project_id' => $data['target_project_id'] ?? null,
            'target_role' => $data['target_role'] ?? null,
            'recipients_count' => count($tokens),
            'success_count' => 0,
            'failed_count' => 0,
            'sent_at' => now(),
        ]);

        // Get full image URL for FCM
        $imageUrl = $broadcast->image_url;

        // Prepare FCM data payload
        $fcmData = [
            'type' => 'broadcast',
            'broadcast_id' => (string) $broadcast->id,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        if ($imageUrl) {
            $fcmData['image'] = $imageUrl;
        }

        // Send FCM notifications using PushNotificationService
        $successCount = 0;
        $failedCount = 0;

        foreach ($tokens as $token) {
            try {
                $this->pushService->sendToTokens(
                    [$token],
                    $data['title'],
                    $data['message'],
                    $fcmData
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Broadcast notification failed for token', [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $broadcast->update([
            'success_count' => $successCount,
            'failed_count' => $failedCount,
        ]);

        return redirect()->route('admin.broadcast.index')
            ->with('status', "Notifikasi berhasil dikirim ke {$successCount} dari " . count($tokens) . " penerima.");
    }

    public function show(BroadcastNotification $broadcast)
    {
        $broadcast->load(['sender', 'targetProject']);

        return view('admin.broadcast.show', compact('broadcast'));
    }

    public function destroy(BroadcastNotification $broadcast)
    {
        // Delete image if exists
        if ($broadcast->image) {
            Storage::disk('public')->delete($broadcast->image);
        }

        $broadcast->delete();

        return redirect()->route('admin.broadcast.index')
            ->with('status', 'Notifikasi berhasil dihapus.');
    }
}


