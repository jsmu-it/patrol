<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveRequestStoreRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;

class LeaveRequestController extends Controller
{
    public function __construct(private readonly PushNotificationService $notifications)
    {
    }
    public function index(): JsonResponse
    {
        $user = auth()->user();

        $requests = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json(LeaveRequestResource::collection($requests));
    }

    public function store(LeaveRequestStoreRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Handle permit photo upload if provided
        $permitPhotoPath = null;
        if ($request->hasFile('permit_photo')) {
            $permitPhotoPath = $request->file('permit_photo')->store('permit_photos', 'public');
        }

        $leave = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'leave_type_id' => $data['leave_type_id'] ?? null,
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'time_from' => $data['time_from'] ?? null,
            'time_to' => $data['time_to'] ?? null,
            'reason' => $data['reason'],
            'status' => LeaveRequest::STATUS_PENDING,
            'doctor_note' => $data['doctor_note'] ?? null,
            'permit_photo' => $permitPhotoPath,
        ]);

        $typeLabel = match ($leave->type) {
            'Sakit', 'sakit' => 'Sakit',
            'Izin', 'izin' => 'Izin',
            'Cuti', 'cuti' => 'Cuti',
            default => $leave->type,
        };

        // Send notification to supervisor if exists, otherwise fallback to admins
        $supervisor = $user->supervisor;

        if ($supervisor && $supervisor->fcm_token) {
            // Has supervisor with FCM token - send to supervisor only
            $this->notifications->notifyUser(
                $supervisor,
                'Pengajuan '.$typeLabel.' dari Bawahan',
                sprintf('%s mengajukan %s.', $user->name, $typeLabel),
                [
                    'type' => 'leave_request',
                    'leave_request_id' => $leave->id,
                ]
            );
        } else {
            // Fallback: No supervisor or supervisor has no FCM token
            // Send to admins with project filtering
            $this->notifications->notifyAdmins(
                'Pengajuan '.$typeLabel,
                sprintf('%s mengajukan %s.', $user->name, $typeLabel),
                [
                    'type' => 'leave_request',
                    'leave_request_id' => $leave->id,
                ],
                $user->active_project_id  // Filter admins by user's active project
            );
        }


        // Kotak masuk portal: jalur yang tidak bergantung pada Firebase, jadi
        // pengajuan tetap terlihat walau notifikasi HP sedang tidak aktif.
        $penerima = collect([$supervisor])
            ->merge(\App\Models\User::whereIn('role', [\App\Models\User::ROLE_SUPERADMIN, \App\Models\User::ROLE_HRD])->get())
            ->filter()
            ->unique('id')
            ->reject(fn ($p) => $p->id === $user->id);

        if ($penerima->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send(
                $penerima,
                new \App\Notifications\CutiDiajukan($leave->loadMissing('user', 'leaveType'), 'persetujuan'),
            );
        }

        $user->notify(new \App\Notifications\CutiDiajukan($leave, 'tanda_terima'));

        $this->notifications->notifyUser(
            $user,
            'Pengajuan '.$typeLabel,
            'Pengajuan Anda telah tercatat dan menunggu persetujuan.',
            [
                'type' => 'leave_request',
                'leave_request_id' => $leave->id,
            ]
        );

        return response()->json(new LeaveRequestResource($leave), 201);
    }

    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $user = auth()->user();

        if ($leaveRequest->user_id !== $user->id && ! $user->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return response()->json(new LeaveRequestResource($leaveRequest));
    }
}
