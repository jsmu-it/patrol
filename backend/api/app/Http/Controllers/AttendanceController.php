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
            ->where('project_id', $project->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->first();

        if ($lastLog && $lastLog->type === AttendanceLog::TYPE_CLOCK_IN) {
            // Check if the last clock-in was from a different day
            $lastClockInDate = CarbonImmutable::parse($lastLog->occurred_at, 'Asia/Jakarta')->toDateString();
            $todayDate = CarbonImmutable::now('Asia/Jakarta')->toDateString();
            
            if ($lastClockInDate === $todayDate) {
                // Same day - cannot clock-in again without clock-out
                return response()->json([
                    'message' => 'You are currently clocked in. Please clock out first before starting a new shift.',
                ], 422);
            }
            
            // Different day - check if it's an overnight shift
            // For overnight shifts, prevent clock-in if still within 24-hour window
            $lastShift = Shift::find($lastLog->shift_id);
            if ($lastShift && $this->isOvernightShift($lastShift)) {
                $hoursSinceClockIn = CarbonImmutable::now('Asia/Jakarta')->diffInHours(
                    CarbonImmutable::parse($lastLog->occurred_at, 'Asia/Jakarta')
                );
                
                // Within 24 hours for overnight shift - still considered clocked in
                if ($hoursSinceClockIn < 24) {
                    return response()->json([
                        'message' => 'You are still in an overnight shift. Please clock out first before starting a new shift.',
                    ], 422);
                }
            }
            
            // Different day - auto mark previous as incomplete and allow new clock-in
            // Previous attendance without clock-out is treated as incomplete
        }


        $data = $request->validated();

        $akurasi = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
        $geofenceCheck = $this->checkGeofence($project, $data['latitude'], $data['longitude'], $akurasi);

        if ($data['mode'] === AttendanceLog::MODE_NORMAL) {
            if (! $geofenceCheck['within']) {
                return response()->json([
                    'message' => $this->pesanDiLuarRadius($geofenceCheck, $akurasi),
                    'debug' => [
                        'your_location' => ['lat' => $data['latitude'], 'lng' => $data['longitude']],
                        'project_location' => ['lat' => $project->latitude, 'lng' => $project->longitude],
                        'distance_meters' => $geofenceCheck['distance'],
                        'allowed_radius' => $geofenceCheck['radius'],
                    ],
                ], 422);
            }
        }

        $photoPath = $request->file('selfie')
            ? $request->file('selfie')->store('attendance/selfies', 'public')
            : null;

        // Parse custom format: d-m-Y H:i with fallback
        $occurredAt = CarbonImmutable::now('Asia/Jakarta');
        if (!empty($data['occurred_at'])) {
            try {
                $occurredAt = CarbonImmutable::createFromFormat('d-m-Y H:i', $data['occurred_at'], 'Asia/Jakarta');
                if (!$occurredAt) {
                    $occurredAt = CarbonImmutable::parse($data['occurred_at'], 'Asia/Jakarta');
                }
            } catch (\Exception $e) {
                \Log::warning('ClockIn: Failed to parse occurred_at: ' . $data['occurred_at'] . ' - ' . $e->getMessage());
                $occurredAt = CarbonImmutable::now('Asia/Jakarta');
            }
        }

        $log = AttendanceLog::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'shift_id' => $shift->id,
            'type' => AttendanceLog::TYPE_CLOCK_IN,
            'occurred_at' => $occurredAt,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'gps_accuracy_meters' => $akurasi !== null ? min(65535, (int) round($akurasi)) : null,
            'distance_meters' => (int) round($geofenceCheck['distance']),
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

        // Pulang tidak dibatasi geofence, tetapi mutu sinyal dan jaraknya tetap
        // dicatat supaya riwayatnya bisa ditelusuri sama seperti absen masuk.
        $akurasi = isset($data['accuracy']) ? (float) $data['accuracy'] : null;
        $geofenceCheck = $this->checkGeofence($project, $data['latitude'], $data['longitude'], $akurasi);

        $photoPath = $request->file('selfie')
            ? $request->file('selfie')->store('attendance/selfies', 'public')
            : null;

        // Parse custom format: d-m-Y H:i with fallback
        $occurredAt = CarbonImmutable::now('Asia/Jakarta');
        if (!empty($data['occurred_at'])) {
            try {
                $occurredAt = CarbonImmutable::createFromFormat('d-m-Y H:i', $data['occurred_at'], 'Asia/Jakarta');
                if (!$occurredAt) {
                    $occurredAt = CarbonImmutable::parse($data['occurred_at'], 'Asia/Jakarta');
                }
            } catch (\Exception $e) {
                \Log::warning('ClockOut: Failed to parse occurred_at: ' . $data['occurred_at'] . ' - ' . $e->getMessage());
                $occurredAt = CarbonImmutable::now('Asia/Jakarta');
            }
        }

        $log = AttendanceLog::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'shift_id' => $shift->id,
            'type' => AttendanceLog::TYPE_CLOCK_OUT,
            'occurred_at' => $occurredAt,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'gps_accuracy_meters' => $akurasi !== null ? min(65535, (int) round($akurasi)) : null,
            'distance_meters' => (int) round($geofenceCheck['distance']),
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
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        // Default to active project if not specified
        $projectId = $data['project_id'] ?? $user->activeProject?->id;
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $logs = $query->paginate(50);

        return response()->json(AttendanceLogResource::collection($logs));
    }

    /**
     * Check if a shift is an overnight shift (crosses midnight).
     * Overnight shift: end time is earlier than or equal to start time.
     * e.g., 22:00 - 06:00 = overnight
     * e.g., 08:00 - 17:00 = NOT overnight
     */
    private function isOvernightShift(?Shift $shift): bool
    {
        if (!$shift || !$shift->start_time || !$shift->end_time) {
            return false;
        }
        
        // Parse time in format HH:mm
        $startParts = explode(':', $shift->start_time);
        $endParts = explode(':', $shift->end_time);
        
        if (count($startParts) < 2 || count($endParts) < 2) {
            return false;
        }
        
        $startInMinutes = (int)$startParts[0] * 60 + (int)$startParts[1];
        $endInMinutes = (int)$endParts[0] * 60 + (int)$endParts[1];
        
        // Overnight: end time comes before or equal to start time
        return $endInMinutes <= $startInMinutes;
    }

    private function isWithinGeofence(Project $project, float $latitude, float $longitude): bool

    {
        $check = $this->checkGeofence($project, $latitude, $longitude);
        return $check['within'];
    }

    /**
     * Toleransi maksimum yang boleh disumbangkan ketidakpastian GPS, dalam meter.
     *
     * Titik yang dilaporkan perangkat bukan satu koordinat pasti, melainkan
     * pusat lingkaran seluas `accuracy`. Petugas yang benar-benar berdiri di
     * dalam pagar bisa terbaca di luar radius ketika sinyalnya buruk — di dalam
     * gedung, di basement, atau saat GPS baru menyala. Karena itu jarak diberi
     * kelonggaran sebesar akurasi yang dilaporkan, tetapi dibatasi agar fix yang
     * benar-benar kacau (ratusan meter, biasanya dari menara seluler) tidak bisa
     * dipakai untuk absen dari rumah.
     */
    private const TOLERANSI_GPS_MAKS = 75.0;

    /**
     * Di atas angka ini titiknya tidak layak dipakai menilai apa pun: sebaran
     * ratusan meter berarti perangkat menebak dari menara seluler, bukan
     * mengunci satelit. Kelonggaran tidak diberikan sama sekali, dan petugas
     * diminta mengulang setelah sinyalnya membaik.
     */
    private const AKURASI_TIDAK_LAYAK = 150.0;

    private function checkGeofence(Project $project, float $latitude, float $longitude, ?float $accuracy = null): array
    {
        if ($project->latitude === null || $project->longitude === null || $project->geofence_radius_meters === null) {
            return ['within' => false, 'distance' => 0, 'radius' => 0, 'toleransi' => 0];
        }

        $distanceMeters = $this->haversineDistance(
            (float) $project->latitude,
            (float) $project->longitude,
            $latitude,
            $longitude,
        );

        $radius = (float) $project->geofence_radius_meters;

        // Kelonggaran hanya sebesar ketidakpastian yang benar-benar dilaporkan,
        // dan hanya bila pembacaannya masih masuk akal.
        $toleransi = $accuracy !== null && $accuracy > 0 && $accuracy <= self::AKURASI_TIDAK_LAYAK
            ? min($accuracy, self::TOLERANSI_GPS_MAKS)
            : 0.0;

        return [
            'within' => ($distanceMeters - $toleransi) <= $radius,
            'distance' => round($distanceMeters, 1),
            'radius' => $radius,
            'toleransi' => round($toleransi, 1),
        ];
    }

    /** Pesan penolakan yang menyebut sebabnya, bukan sekadar angka jarak. */
    private function pesanDiLuarRadius(array $cek, ?float $akurasi): string
    {
        $pesan = "Anda terbaca {$cek['distance']}m dari lokasi, batasnya {$cek['radius']}m.";

        if ($akurasi === null) {
            return $pesan . ' Pastikan GPS aktif dan Anda berada di area lokasi tugas.';
        }

        $akurasiBulat = (int) round($akurasi);

        // Sinyal buruk lebih sering jadi biang keladi daripada posisi yang salah.
        if ($akurasi > self::AKURASI_TIDAK_LAYAK) {
            return $pesan . " Sinyal GPS sedang lemah (±{$akurasiBulat}m)."
                . ' Coba keluar sebentar ke area terbuka, tunggu beberapa detik, lalu ulangi.';
        }

        return $pesan . " Akurasi GPS ±{$akurasiBulat}m sudah diperhitungkan.";
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
