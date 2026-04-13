<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestAdminController extends Controller
{
    public function __construct(private readonly PushNotificationService $notifications)
    {
    }

    /**
     * List all leave requests (admin only)
     * Can filter by status: pending, approved, rejected, cancelled
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = LeaveRequest::with('user');

        // If not superadmin, only show subordinates' requests
        if (!$user->isSuperAdmin()) {
            $subordinateIds = $user->subordinates()->pluck('id');
            
            // If no subordinates, return empty collection
            if ($subordinateIds->isEmpty()) {
                return response()->json([]);
            }
            
            $query->whereIn('user_id', $subordinateIds);
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json(LeaveRequestResource::collection($requests));
    }

    /**
     * Approve a leave request
     */
    public function approve(LeaveRequest $leaveRequest, Request $request): JsonResponse
    {
        $currentUser = $request->user();
        $requestUser = $leaveRequest->user;

        \Illuminate\Support\Facades\Log::info('Leave approval attempt', [
            'requester_id' => $leaveRequest->user_id,
            'approver_id' => $currentUser->id,
            'approver_role' => $currentUser->role,
            'is_super_admin' => $currentUser->isSuperAdmin(),
            'request_id' => $leaveRequest->id,
            'supervisor_id' => $requestUser?->supervisor_id,
        ]);

        // Check if current user can approve this request
        // SuperAdmin can approve any request
        // Otherwise, only the direct supervisor can approve
        if (!$currentUser->isSuperAdmin()) {
            if ($requestUser->supervisor_id !== $currentUser->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk menyetujui pengajuan ini. Hanya atasan langsung yang dapat menyetujui.',
                ], 403);
            }
        }

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return response()->json([
                'message' => 'Hanya pengajuan dengan status pending yang bisa disetujui.',
            ], 422);
        }

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_APPROVED,
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
        ]);

        // Deduct from leave balance if leave_type_id is set
        if ($leaveRequest->leave_type_id) {
            $days = $leaveRequest->date_from->diffInDays($leaveRequest->date_to) + 1;
            $balance = \App\Models\LeaveBalance::getOrCreate(
                $requestUser->id,
                $leaveRequest->leave_type_id,
                $leaveRequest->date_from->year
            );
            
            // Only deduct if leave type has quota (quota > 0)
            if ($balance->quota > 0) {
                $balance->deduct($days);
            }
        }

        // Notify user about approval
        if ($requestUser) {
            $typeLabel = match ($leaveRequest->type) {
                'Sakit', 'sakit' => 'Sakit',
                'Izin', 'izin' => 'Izin',
                'Cuti', 'cuti' => 'Cuti',
                default => $leaveRequest->type,
            };

            $this->notifications->notifyUser(
                $requestUser,
                'Pengajuan '.$typeLabel.' Disetujui',
                'Pengajuan '.$typeLabel.' Anda telah disetujui oleh '.$currentUser->name.'.',
                [
                    'type' => 'leave_request_approved',
                    'leave_request_id' => (string) $leaveRequest->id,
                ],
            );
        }

        return response()->json([
            'message' => 'Pengajuan berhasil disetujui.',
            'data' => new LeaveRequestResource($leaveRequest->fresh()),
        ]);
    }

    /**
     * Reject a leave request
     */
    public function reject(LeaveRequest $leaveRequest, Request $request): JsonResponse
    {
        $currentUser = $request->user();
        $requestUser = $leaveRequest->user;

        \Illuminate\Support\Facades\Log::info('Leave rejection attempt', [
            'requester_id' => $leaveRequest->user_id,
            'approver_id' => $currentUser->id,
            'approver_role' => $currentUser->role,
            'is_super_admin' => $currentUser->isSuperAdmin(),
            'request_id' => $leaveRequest->id,
            'supervisor_id' => $requestUser?->supervisor_id,
        ]);

        // Check if current user can reject this request
        if (!$currentUser->isSuperAdmin()) {
            if ($requestUser->supervisor_id !== $currentUser->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk menolak pengajuan ini. Hanya atasan langsung yang dapat menolak.',
                ], 403);
            }
        }

        if ($leaveRequest->status !== LeaveRequest::STATUS_PENDING) {
            return response()->json([
                'message' => 'Hanya pengajuan dengan status pending yang bisa ditolak.',
            ], 422);
        }

        $leaveRequest->update([
            'status' => LeaveRequest::STATUS_REJECTED,
            'approved_by' => $currentUser->id,
            'approved_at' => now(),
        ]);

        // Notify user about rejection
        if ($requestUser) {
            $typeLabel = match ($leaveRequest->type) {
                'Sakit', 'sakit' => 'Sakit',
                'Izin', 'izin' => 'Izin',
                'Cuti', 'cuti' => 'Cuti',
                default => $leaveRequest->type,
            };

            $this->notifications->notifyUser(
                $requestUser,
                'Pengajuan '.$typeLabel.' Ditolak',
                'Pengajuan '.$typeLabel.' Anda ditolak oleh '.$currentUser->name.'.',
                [
                    'type' => 'leave_request_rejected',
                    'leave_request_id' => (string) $leaveRequest->id,
                ],
            );
        }

        return response()->json([
            'message' => 'Pengajuan berhasil ditolak.',
            'data' => new LeaveRequestResource($leaveRequest->fresh()),
        ]);
    }

    /**
     * Set leave request back to pending status
     */
    public function setPending(LeaveRequest $leaveRequest): JsonResponse
    {
        if ($leaveRequest->status === LeaveRequest::STATUS_CANCELLED) {
            return response()->json([
                'message' => 'Pengajuan yang sudah dibatalkan tidak bisa diubah statusnya.',
            ], 422);
        }

        $leaveRequest->update(['status' => LeaveRequest::STATUS_PENDING]);

        // Notify user
        if ($leaveRequest->user) {
            $typeLabel = match ($leaveRequest->type) {
                'Sakit', 'sakit' => 'Sakit',
                'Izin', 'izin' => 'Izin',
                'Cuti', 'cuti' => 'Cuti',
                default => $leaveRequest->type,
            };

            $this->notifications->notifyUser(
                $leaveRequest->user,
                'Pengajuan '.$typeLabel.' Menunggu Review',
                'Status pengajuan '.$typeLabel.' Anda diubah menjadi menunggu review.',
                [
                    'type' => 'leave_request_pending',
                    'leave_request_id' => $leaveRequest->id,
                ],
            );
        }

        return response()->json([
            'message' => 'Status pengajuan berhasil diubah menjadi pending.',
            'data' => new LeaveRequestResource($leaveRequest->fresh()),
        ]);
    }
}
