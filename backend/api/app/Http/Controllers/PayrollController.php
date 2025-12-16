<?php

namespace App\Http\Controllers;

use App\Http\Resources\PayrollSlipResource;
use App\Models\PayrollSlip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $slips = PayrollSlip::where('user_id', $user->id)
            ->with(['incomeItems', 'deductionItems'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(PayrollSlipResource::collection($slips));
    }

    public function show(Request $request, PayrollSlip $slip): JsonResponse
    {
        $user = $request->user();

        if ($slip->user_id !== $user->id) {
            return response()->json([
                'message' => 'Slip gaji tidak ditemukan.',
            ], 404);
        }

        $slip->load(['incomeItems', 'deductionItems']);

        return response()->json(new PayrollSlipResource($slip));
    }
}
