<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function attendance(Request $request): View
    {
        $user = $request->user();

        $query = AttendanceLog::with('user', 'project', 'shift')
            ->where('mode', AttendanceLog::MODE_DINAS)
            ->where('status_dinas', AttendanceLog::STATUS_DINAS_PENDING)
            ->orderBy('occurred_at', 'desc');

        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $query->whereIn('project_id', $accessibleProjectIds);
        }

        $logs = $query->paginate(30);

        return view('admin.approvals.attendance', compact('logs'));
    }

    public function approveAttendance(AttendanceLog $attendanceLog): RedirectResponse
    {
        $attendanceLog->update(['status_dinas' => AttendanceLog::STATUS_DINAS_APPROVED]);

        return back()->with('status', 'Absensi dinas disetujui.');
    }

    public function rejectAttendance(AttendanceLog $attendanceLog): RedirectResponse
    {
        $attendanceLog->update(['status_dinas' => AttendanceLog::STATUS_DINAS_REJECTED]);

        return back()->with('status', 'Absensi dinas ditolak.');
    }

    public function leave(Request $request): View
    {
        $user = $request->user();
        $status = $request->get('status', 'pending'); // Default to pending
        
        $query = LeaveRequest::with(['user', 'approvedBy', 'leaveType'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by accessible projects
        $accessibleProjectIds = $user->getAccessibleProjectIds();
        if (!$user->isSuperAdmin() && !empty($accessibleProjectIds)) {
            $query->whereHas('user', function ($q) use ($accessibleProjectIds) {
                $q->whereIn('active_project_id', $accessibleProjectIds);
            });
        }

        $requests = $query->paginate(30)->appends(['status' => $status]);

        return view('admin.approvals.leave', compact('requests', 'status'));
    }

    public function approveLeave(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update(['status' => LeaveRequest::STATUS_APPROVED]);

        return back()->with('status', 'Pengajuan cuti disetujui.');
    }

    public function rejectLeave(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update(['status' => LeaveRequest::STATUS_REJECTED]);

        return back()->with('status', 'Pengajuan cuti ditolak.');
    }
}
