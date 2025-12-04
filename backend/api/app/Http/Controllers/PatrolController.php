<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatrolLogRequest;
use App\Http\Resources\PatrolLogResource;
use App\Models\Checkpoint;
use App\Models\PatrolLog;
use App\Services\PushNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;

class PatrolController extends Controller
{
    public function __construct(private readonly PushNotificationService $notifications)
    {
    }
    public function store(PatrolLogRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // DEBUG LOGGING (Raw Request)
        \Illuminate\Support\Facades\Log::info('Patrol Store RAW', [
            'user_id' => $user->id,
            'all_input' => $request->all(),
            'has_file' => $request->hasFile('photo'),
        ]);

        $data = $request->validated();

        // Clean Checkpoint Code (remove URL prefix if present)
        if (!empty($data['checkpoint_code'])) {
            $code = $data['checkpoint_code'];
            // Remove satpamapp://checkpoint?code=
            if (str_contains($code, '?code=')) {
                $parts = explode('?code=', $code);
                $code = end($parts);
            }
            // Remove clean URL if just path
            $code = str_replace(['satpamapp://checkpoint', 'https://satpamapp/checkpoint'], '', $code);
            $data['checkpoint_code'] = trim($code);
        }

        // DEBUG LOGGING (Validated & Cleaned)
        \Illuminate\Support\Facades\Log::info('Patrol Store Validated', [
            'project_id' => $data['project_id'],
            'type' => $data['type'] ?? 'null',
            'clean_code' => $data['checkpoint_code'] ?? 'NULL',
        ]);

        $type = $data['type'] ?? 'patrol';

        $checkpoint = null;
        if (! empty($data['checkpoint_code'])) {
            $checkpoint = Checkpoint::query()
                ->where('project_id', $data['project_id'])
                ->where('code', $data['checkpoint_code'])
                ->first();
        }

        if ($type === 'patrol') {
            if (! $checkpoint) {
                // Check if checkpoint exists globally to give better error
                $globalCheckpoint = Checkpoint::where('code', $data['checkpoint_code'] ?? '')->first();
                
                if ($globalCheckpoint) {
                    return response()->json([
                        'message' => "Checkpoint ini terdaftar di project lain: {$globalCheckpoint->project->name}.",
                    ], 422);
                }

                return response()->json([
                    'message' => 'Checkpoint not found for this project.',
                ], 422);
            }

            // Check Geofence (if checkpoint has coordinates)
            if ($checkpoint->latitude && $checkpoint->longitude) {
                $distance = $this->haversineDistance(
                    (float) $checkpoint->latitude,
                    (float) $checkpoint->longitude,
                    (float) $data['latitude'],
                    (float) $data['longitude']
                );

                // Use checkpoint specific radius if available, otherwise default to 150m
                $maxDistance = $checkpoint->radius_meters ?? 150;

                if ($distance > $maxDistance) {
                    return response()->json([
                        'message' => "Lokasi Anda terlalu jauh dari titik patroli ({$distance}m). Maksimal {$maxDistance}m.",
                    ], 422);
                }
            }
        }

        $photoPath = $request->file('photo')
            ? $request->file('photo')->store('patrol/photos', 'public')
            : null;

        $occurredAt = isset($data['occurred_at'])
            ? CarbonImmutable::parse($data['occurred_at'])
            : CarbonImmutable::now();

        $log = PatrolLog::create([
            'user_id' => $user->id,
            'project_id' => $data['project_id'],
            'checkpoint_id' => $checkpoint?->id,
            'type' => $type,
            'title' => $data['title'],
            'post_name' => $data['post_name'],
            'description' => $data['description'] ?? null,
            'photo_path' => $photoPath,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'occurred_at' => $occurredAt,
        ]);

        if (in_array($type, ['sos', 'incident'], true)) {
            $title = $type === 'sos' ? 'Laporan Darurat (SOS)' : 'Laporan Insiden';
            $body = sprintf(
                '%s oleh %s di project %s.',
                $title,
                $user->name,
                $log->project?->name ?? '-'
            );

            $this->notifications->notifyAdmins($title, $body, [
                'type' => $type,
                'patrol_log_id' => $log->id,
            ]);
        }

        return response()->json(new PatrolLogResource($log), 201);
    }

    public function history(): JsonResponse
    {
        $user = auth()->user();

        $logs = PatrolLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->paginate(50);

        return response()->json(PatrolLogResource::collection($logs));
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $earthRadius * $angle;
    }

    public function checkCheckpoint(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = $request->user();
        $code = $request->string('code');

        $checkpoint = Checkpoint::query()
            ->where('project_id', $user->active_project_id)
            ->where('code', $code)
            ->first();

        if (! $checkpoint) {
            // Check global
            $global = Checkpoint::where('code', $code)->first();
            if ($global) {
                return response()->json([
                    'message' => "Checkpoint terdaftar di project: {$global->project->name}",
                ], 404);
            }

            return response()->json([
                'message' => 'Checkpoint tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'id' => $checkpoint->id,
            'title' => $checkpoint->title,
            'post_name' => $checkpoint->post_name,
            'description' => $checkpoint->description,
        ]);
    }
}
