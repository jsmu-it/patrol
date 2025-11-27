<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    public function index(): JsonResponse
    {
        $shifts = Shift::query()->orderBy('start_time')->get();

        return response()->json(ShiftResource::collection($shifts));
    }

    public function store(ShiftRequest $request): JsonResponse
    {
        $data = $request->validated();
        $shift = Shift::create($data);

        return response()->json(new ShiftResource($shift), 201);
    }

    public function show(Shift $shift): JsonResponse
    {
        return response()->json(new ShiftResource($shift));
    }

    public function update(ShiftRequest $request, Shift $shift): JsonResponse
    {
        $shift->update($request->validated());

        return response()->json(new ShiftResource($shift));
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $shift->delete();

        return response()->json([], 204);
    }

    public function availableForCurrentUser(): JsonResponse
    {
        $user = auth()->user();

        $project = $user?->activeProject;
        if (! $project) {
            return response()->json([
                'message' => 'User does not have an active project.',
            ], 422);
        }

        $shifts = $project->shifts()->wherePivot('is_active', true)->get();

        return response()->json(ShiftResource::collection($shifts));
    }
}
