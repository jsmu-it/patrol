<?php

namespace App\Http\Controllers;

use App\Http\Resources\CheckpointResource;
use App\Models\Checkpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckpointController extends Controller
{
    public function index(): JsonResponse
    {
        $checkpoints = Checkpoint::query()->latest()->paginate(50);

        return response()->json(CheckpointResource::collection($checkpoints));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'code' => ['required', 'string', 'max:100', 'unique:checkpoints,code'],
            'title' => ['required', 'string', 'max:255'],
            'post_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $checkpoint = Checkpoint::create($data);

        return response()->json(new CheckpointResource($checkpoint), 201);
    }

    public function show(Checkpoint $checkpoint): JsonResponse
    {
        return response()->json(new CheckpointResource($checkpoint));
    }

    public function update(Request $request, Checkpoint $checkpoint): JsonResponse
    {
        $data = $request->validate([
            'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
            'code' => ['sometimes', 'string', 'max:100', 'unique:checkpoints,code,'.$checkpoint->id],
            'title' => ['sometimes', 'string', 'max:255'],
            'post_name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $checkpoint->update($data);

        return response()->json(new CheckpointResource($checkpoint));
    }

    public function destroy(Checkpoint $checkpoint): JsonResponse
    {
        $checkpoint->delete();

        return response()->json([], 204);
    }
}
