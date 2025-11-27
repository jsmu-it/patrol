<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ShiftResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        $projects = Project::query()->latest()->paginate(20);

        return response()->json(ProjectResource::collection($projects));
    }

    public function store(ProjectRequest $request): JsonResponse
    {
        $data = $request->validated();
        $project = Project::create($data);

        return response()->json(new ProjectResource($project), 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json(new ProjectResource($project));
    }

    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->validated());

        return response()->json(new ProjectResource($project));
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json([], 204);
    }

    public function shifts(Project $project): JsonResponse
    {
        $shifts = $project->shifts()->wherePivot('is_active', true)->get();

        return response()->json(ShiftResource::collection($shifts));
    }

    public function syncShifts(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'shift_ids' => ['required', 'array'],
            'shift_ids.*' => ['integer', 'exists:shifts,id'],
        ]);

        $syncData = [];
        foreach ($validated['shift_ids'] as $shiftId) {
            $syncData[$shiftId] = ['is_active' => true];
        }

        $project->shifts()->sync($syncData);

        $project->refresh();

        return response()->json(ShiftResource::collection($project->shifts));
    }
}
