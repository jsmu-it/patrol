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
        $shifts = $project->shifts()->get();

        return response()->json(ShiftResource::collection($shifts));
    }

    public function syncShifts(Request $request, Project $project): JsonResponse
    {
        // Shift sekarang milik project. Endpoint ini memindahkan kepemilikan
        // shift yang dipilih ke project ini; shift lain yang sebelumnya milik
        // project ini dilepas hanya bila belum dipakai absensi.
        $validated = $request->validate([
            'shift_ids' => ['required', 'array'],
            'shift_ids.*' => ['integer', 'exists:shifts,id'],
        ]);

        \App\Models\Shift::whereIn('id', $validated['shift_ids'])
            ->update(['project_id' => $project->id]);

        $project->refresh();

        return response()->json(ShiftResource::collection($project->shifts));
    }
}
