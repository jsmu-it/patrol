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

        $leave = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'reason' => $data['reason'],
            'status' => LeaveRequest::STATUS_PENDING,
            'doctor_note' => $data['doctor_note'] ?? null,
        ]);

        $typeLabel = match ($leave->type) {
            'Sakit', 'sakit' => 'Sakit',
            'Izin', 'izin' => 'Izin',
            'Cuti', 'cuti' => 'Cuti',
            default => $leave->type,
        };

        $this->notifications->notifyAdmins(
            'Pengajuan '.$typeLabel,
            sprintf('%s mengajukan %s.', $user->name, $typeLabel),
            [
                'type' => 'leave_request',
                'leave_request_id' => $leave->id,
            ],
        );

        $this->notifications->notifyUser(
            $user,
            'Pengajuan '.$typeLabel,
            'Pengajuan Anda telah tercatat dan menunggu persetujuan.',
            [
                'type' => 'leave_request',
                'leave_request_id' => $leave->id,
            ],
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
