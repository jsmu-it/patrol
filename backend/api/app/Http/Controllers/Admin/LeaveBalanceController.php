<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $leaveTypeId = $request->get('leave_type_id');
        $projectId = $request->get('project_id');
        $role = $request->get('role'); // Add role filter

        $query = User::query()
            ->with(['leaveBalances' => function ($query) use ($year) {
                $query->where('year', $year)->with('leaveType');
            }]);

        // Filter by role if specified, otherwise show all users
        if ($role) {
            $query->where('role', $role);
        }

        // Filter by project if specified
        if ($projectId) {
            $query->where('active_project_id', $projectId);
        }

        $users = $query->orderBy('name')->paginate(20);

        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $projects = \App\Models\Project::orderBy('name')->get();

        // Get all available roles for filter
        $roles = [
            User::ROLE_GUARD => 'Guard',
            User::ROLE_ADMIN => 'Admin',
            User::ROLE_PROJECT_ADMIN => 'Project Admin',
            User::ROLE_SUPERADMIN => 'Superadmin',
            User::ROLE_HRD => 'HRD',
            User::ROLE_PAYROLL => 'Payroll',
            User::ROLE_CMS => 'CMS',
        ];

        return view('admin.leave-balance.index', compact('users', 'leaveTypes', 'projects', 'year', 'projectId', 'roles', 'role'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'quota' => 'required|integer|min:0',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $year = $validated['year'] ?? now()->year;

        LeaveBalance::updateOrCreate(
            [
                'user_id' => $user->id,
                'leave_type_id' => $validated['leave_type_id'],
                'year' => $year,
            ],
            [
                'quota' => $validated['quota'],
            ]
        );

        return redirect()->route('admin.leave-balance.index', ['year' => $year])
            ->with('success', 'Saldo cuti berhasil diperbarui.');
    }

    /**
     * Reset all balances - delete all leave balances for the year
     */
    public function resetAll(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = $validated['year'];
        
        // Delete all leave balances for this year
        $deleted = LeaveBalance::where('year', $year)->delete();

        return redirect()->route('admin.leave-balance.index', ['year' => $year])
            ->with('success', 'Semua saldo cuti tahun ' . $year . ' berhasil dihapus (' . $deleted . ' record).');
    }

    /**
     * Show detail for a specific user
     */
    public function show(User $user, Request $request)
    {
        $year = $request->get('year', now()->year);
        
        $leaveTypes = LeaveType::active()->orderBy('name')->get();

        $berhak = \App\Services\SaldoCuti::berhakCuti($user);
        $saldo  = \App\Services\SaldoCuti::untuk($user, (int) $year, $leaveTypes)->keyBy('leave_type_id');

        return view('admin.leave-balance.show', compact('user', 'saldo', 'leaveTypes', 'year', 'berhak'));
    }

    /**
     * Bulk update leave balances for selected users
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|string',
            'leave_type_id' => 'required|exists:leave_types,id',
            'quota' => 'required|integer|min:0',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $userIds = explode(',', $validated['user_ids']);
        $year = $validated['year'];
        $leaveTypeId = $validated['leave_type_id'];
        $quota = $validated['quota'];

        $updated = 0;
        foreach ($userIds as $userId) {
            LeaveBalance::updateOrCreate(
                [
                    'user_id' => $userId,
                    'leave_type_id' => $leaveTypeId,
                    'year' => $year,
                ],
                [
                    'quota' => $quota,
                ]
            );
            $updated++;
        }

        return redirect()->route('admin.leave-balance.index', ['year' => $year])
            ->with('success', "Berhasil update saldo untuk {$updated} karyawan.");
    }

    /**
     * Bulk reset leave balances for selected users
     */
    public function bulkReset(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|string',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $userIds = explode(',', $validated['user_ids']);
        $year = $validated['year'];

        $deleted = LeaveBalance::whereIn('user_id', $userIds)
            ->where('year', $year)
            ->delete();

        return redirect()->route('admin.leave-balance.index', ['year' => $year])
            ->with('success', "Berhasil reset saldo untuk " . count($userIds) . " karyawan ({$deleted} record dihapus).");
    }

    /**
     * Bulk assign leave types to selected users
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|string',
            'leave_type_ids' => 'required|array',
            'leave_type_ids.*' => 'exists:leave_types,id',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $userIds = explode(',', $validated['user_ids']);
        $year = $validated['year'];
        $leaveTypeIds = $validated['leave_type_ids'];

        $created = 0;
        foreach ($userIds as $userId) {
            foreach ($leaveTypeIds as $leaveTypeId) {
                $exists = LeaveBalance::where('user_id', $userId)
                    ->where('leave_type_id', $leaveTypeId)
                    ->where('year', $year)
                    ->exists();

                if (!$exists) {
                    $leaveType = LeaveType::find($leaveTypeId);
                    LeaveBalance::create([
                        'user_id' => $userId,
                        'leave_type_id' => $leaveTypeId,
                        'quota' => $leaveType->default_quota ?? 0,
                        'used' => 0,
                        'year' => $year,
                    ]);
                    $created++;
                }
            }
        }

        return redirect()->route('admin.leave-balance.index', ['year' => $year])
            ->with('success', "Berhasil assign tipe cuti ke " . count($userIds) . " karyawan ({$created} saldo baru dibuat).");
    }
}
