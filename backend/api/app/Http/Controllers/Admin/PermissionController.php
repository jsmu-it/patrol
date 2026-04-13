<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', [
            User::ROLE_SUPERADMIN,
            User::ROLE_ADMIN,
            User::ROLE_PROJECT_ADMIN,
            User::ROLE_HRD,
            User::ROLE_PAYROLL,
            User::ROLE_CMS,
        ])
        ->with('accessibleProjects')
        ->orderBy('name')
        ->paginate(20);

        return view('admin.permissions.index', compact('users'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();
        $roles = [
            User::ROLE_SUPERADMIN => 'Superadmin',
            User::ROLE_ADMIN => 'Admin',
            User::ROLE_PROJECT_ADMIN => 'Project Admin',
            User::ROLE_HRD => 'HRD',
            User::ROLE_PAYROLL => 'Payroll',
            User::ROLE_CMS => 'CMS',
        ];

        return view('admin.permissions.create', compact('projects', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in([
                User::ROLE_SUPERADMIN,
                User::ROLE_ADMIN,
                User::ROLE_PROJECT_ADMIN,
                User::ROLE_HRD,
                User::ROLE_PAYROLL,
                User::ROLE_CMS,
            ])],
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'exists:projects,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        // Sync project access
        if (!empty($validated['project_ids'])) {
            $user->accessibleProjects()->sync($validated['project_ids']);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Admin berhasil ditambahkan dengan otoritas project yang dipilih.');
    }

    public function edit(User $permission)
    {
        $projects = Project::orderBy('name')->get();
        $roles = [
            User::ROLE_SUPERADMIN => 'Superadmin',
            User::ROLE_ADMIN => 'Admin',
            User::ROLE_PROJECT_ADMIN => 'Project Admin',
            User::ROLE_HRD => 'HRD',
            User::ROLE_PAYROLL => 'Payroll',
            User::ROLE_CMS => 'CMS',
        ];

        $selectedProjects = $permission->accessibleProjects->pluck('id')->toArray();

        return view('admin.permissions.edit', compact('permission', 'projects', 'roles', 'selectedProjects'));
    }

    public function update(Request $request, User $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($permission->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($permission->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in([
                User::ROLE_SUPERADMIN,
                User::ROLE_ADMIN,
                User::ROLE_PROJECT_ADMIN,
                User::ROLE_HRD,
                User::ROLE_PAYROLL,
                User::ROLE_CMS,
            ])],
            'project_ids' => 'nullable|array',
            'project_ids.*' => 'exists:projects,id',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $permission->update($updateData);

        // Sync project access
        $permission->accessibleProjects()->sync($validated['project_ids'] ?? []);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Admin berhasil diupdate.');
    }

    public function destroy(User $permission)
    {
        if ($permission->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Admin berhasil dihapus.');
    }
}
