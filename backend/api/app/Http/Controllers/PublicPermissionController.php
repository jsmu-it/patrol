<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;


class PublicPermissionController extends Controller
{
    public function create()
    {
        // Show all projects that have been input
        $projects = Project::orderBy('name')->get();

        return view('public.permissions.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'position' => 'required|string|max:255', // Jabatan as text field
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'exists:projects,id',
        ]);

        // Create user with ADMIN role by default for public form
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => User::ROLE_ADMIN, // Default role for public submission
        ]);

        // Sync project access
        if (!empty($validated['project_ids'])) {
            $user->accessibleProjects()->sync($validated['project_ids']);
        }

        return redirect()->route('public.permissions.create')
            ->with('success', 'Permintaan akses berhasil dikirim. Tim kami akan segera memproses.');
    }
}
