<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;

class LeaveTypeController extends Controller
{
    /**
     * Get all active leave types for mobile app
     */
    public function index(): JsonResponse
    {
        $leaveTypes = LeaveType::active()
            ->select(['id', 'name', 'description', 'default_quota'])
            ->orderBy('name')
            ->get();

        return response()->json($leaveTypes);
    }
}
