<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::orderBy('name')->get();
        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_quota' => 'required|integer|min:0',
        ]);

        LeaveType::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'default_quota' => $validated['default_quota'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Tipe cuti berhasil ditambahkan.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_quota' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $leaveType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'default_quota' => $validated['default_quota'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Tipe cuti berhasil diperbarui.');
    }

    public function destroy(LeaveType $leaveType)
    {
        // Check if any leave requests are using this type
        if ($leaveType->leaveRequests()->exists()) {
            return redirect()->route('admin.leave-types.index')
                ->with('error', 'Tidak dapat menghapus tipe cuti yang sudah digunakan.');
        }

        $leaveType->delete();

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Tipe cuti berhasil dihapus.');
    }

    /**
     * Show form to assign leave type to users
     */
    public function showAssign(Request $request, LeaveType $leaveType)
    {
        $query = User::query()
            ->with(['activeProject', 'profile']);

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('active_project_id', $request->project_id);
        }

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Sort by project if requested
        if ($request->boolean('sort_project')) {
            $query->orderBy('active_project_id');
        }
        
        $query->orderBy('name');
        $users = $query->get();

        // Get projects for filter dropdown
        $projects = \App\Models\Project::orderBy('name')->get();

        $currentYear = now()->year;
        $assignedUserIds = LeaveBalance::where('leave_type_id', $leaveType->id)
            ->where('year', $currentYear)
            ->pluck('user_id')
            ->toArray();

        return view('admin.leave-types.assign', compact('leaveType', 'users', 'assignedUserIds', 'currentYear', 'projects'));
    }

    /**
     * Assign leave type to selected users
     */
    public function assign(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'quota' => 'nullable|integer|min:0',
        ]);

        $quota = $validated['quota'] ?? $leaveType->default_quota;
        $year = now()->year;

        foreach ($validated['user_ids'] as $userId) {
            LeaveBalance::updateOrCreate(
                [
                    'user_id' => $userId,
                    'leave_type_id' => $leaveType->id,
                    'year' => $year,
                ],
                [
                    'quota' => $quota,
                ]
            );
        }

        return redirect()->route('admin.leave-types.index')
            ->with('success', 'Tipe cuti berhasil di-assign ke karyawan.');
    }
}
