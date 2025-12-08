<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceClockInRequest;
use App\Http\Requests\AttendanceClockOutRequest;
use App\Http\Requests\AttendanceHistoryRequest;
use App\Http\Resources\AttendanceLogResource;
use App\Models\AttendanceLog;
use App\Models\Project;
use App\Models\Shift;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function clockIn(AttendanceClockInRequest $request): JsonResponse
    {
        $user = $request->user();

        $project = $user?->activeProject;
        if (! $project) {
            return response()->json([
                'message' => 'User does not have an active project.',
            ], 422);
        }

        $shift = Shift::findOrFail($request->integer('shift_id'));

        $today = CarbonImmutable::now('UTC')->toDateString();

        // Check for the last log of the user for this project
        $lastLog = AttendanceLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('occurred_at')
            ->first();

        if ($lastLog && $lastLog->type === AttendanceLog::TYPE_CLOCK_IN) {
            // If the last log was a clock-in, they cannot clock-in again unless they clocked out
            // OR unless it's been a very long time (e.g. > 24 hours), but requirements say strict check
            return response()->json([
                'message' => 'You are currently clocked in. Please clock out first before starting a new shift.',
            ], 422);
        }

        $data = $request->validated();

        if ($data['mode'] === AttendanceLog::MODE_NORMAL) {
            if (! $this->isWithinGeofence($project, $data['latitude'], $data['longitude'])) {
                return response()->json([
                    'message' => 'Clock in must be within project geofence for normal mode.',
                ], 422);
            }
        }

        $photoPath = $request->file('selfie')
            ? $request->file('selfie')->store('attendance/selfies', 'public')
            : null;

        // Parse custom format: d-m-Y H:i
        $occurredAt = isset($data['occurred_at'])
            ? CarbonImmutable::createFromFormat('d-m-Y H:i', $data['occurred_at'], 'UTC')
            : CarbonImmutable::now('UTC');

        $log = AttendanceLog::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'shift_id' => $shift->id,
            'type' => AttendanceLog::TYPE_CLOCK_IN,
            'occurred_at' => $occurredAt,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'selfie_photo_path' => $photoPath,
            'note' => $data['note'] ?? null,
            'mode' => $data['mode'],
            'status_dinas' => $data['mode'] === AttendanceLog::MODE_DINAS
                ? AttendanceLog::STATUS_DINAS_PENDING
                : null,
        ]);

        return response()->json(new AttendanceLogResource($log), 201);
    }

    public function clockOut(AttendanceClockOutRequest $request): JsonResponse
    {
        $user = $request->user();
        $project = $user?->activeProject;
        if (! $project) {
            return response()->json([
                'message' => 'User does not have an active project.',
            ], 422);
        }

        $shift = Shift::findOrFail($request->integer('shift_id'));

        $data = $request->validated();

        $photoPath = $request->file('selfie')
            ? $request->file('selfie')->store('attendance/selfies', 'public')
            : null;

        // Parse custom format: d-m-Y H:i
        $occurredAt = isset($data['occurred_at'])
            ? CarbonImmutable::createFromFormat('d-m-Y H:i', $data['occurred_at'], 'UTC')
            : CarbonImmutable::now('UTC');

        $log = AttendanceLog::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'shift_id' => $shift->id,
            'type' => AttendanceLog::TYPE_CLOCK_OUT,
            'occurred_at' => $occurredAt,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'selfie_photo_path' => $photoPath,
            'note' => $data['note'] ?? null,
            'mode' => AttendanceLog::MODE_NORMAL,
        ]);

        return response()->json(new AttendanceLogResource($log), 201);
    }

    public function history(AttendanceHistoryRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validated();

        $from = CarbonImmutable::parse($data['from'])->startOfDay();
        $to = CarbonImmutable::parse($data['to'])->endOfDay();

        if ($from->diffInMonths($to) > 12) {
            return response()->json([
                'message' => 'Date range cannot exceed 12 months.',
            ], 422);
        }

        $query = AttendanceLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('occurred_at', [$from, $to])
            ->orderByDesc('occurred_at');

        if (! empty($data['project_id'])) {
            $query->where('project_id', $data['project_id']);
        }

        $logs = $query->paginate(50);

        return response()->json(AttendanceLogResource::collection($logs));
    }

    private function isWithinGeofence(Project $project, float $latitude, float $longitude): bool
    {
        if ($project->latitude === null || $project->longitude === null || $project->geofence_radius_meters === null) {
            return false;
        }

        $distanceMeters = $this->haversineDistance(
            (float) $project->latitude,
            (float) $project->longitude,
            $latitude,
            $longitude,
        );

        return $distanceMeters <= (float) $project->geofence_radius_meters;
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
}
