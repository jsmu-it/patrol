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

        // Karyawan yang sudah terdaftar, supaya admin tidak perlu mengetik
        // ulang data orang yang datanya sudah ada. Peran ADMIN ikut ditampilkan
        // agar admin lama bisa dipindah otoritasnya.
        $karyawan = User::whereIn('role', [User::ROLE_GUARD, User::ROLE_ADMIN])
            ->with('profile:id,user_id,nip,position')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email', 'role', 'active_project_id'])
            ->map(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'username'   => $u->username,
                'email'      => $u->email,
                'role'       => $u->role,
                'project_id' => $u->active_project_id,
                'nip'        => $u->profile->nip ?? null,
                'position'   => $u->profile->position ?? null,
            ])
            ->values();

        return view('admin.permissions.create', [
            'projects' => $projects,
            'roles'    => $this->daftarPeran(),
            'karyawan' => $karyawan,
        ]);
    }

    /**
     * Peran yang bisa dipilih.
     *
     * PROJECT_ADMIN dihapus dari pilihan karena perilakunya identik dengan
     * ADMIN: keduanya dibatasi oleh project yang di-assign lewat
     * user_project_access, dan di seluruh middleware rute selalu disebut
     * berpasangan. Konstantanya sengaja dipertahankan agar pengecekan lama
     * di kode tidak pecah.
     */
    private function daftarPeran(): array
    {
        return [
            User::ROLE_SUPERADMIN => 'Superadmin',
            User::ROLE_ADMIN      => 'Admin',
            User::ROLE_HRD        => 'HRD',
            User::ROLE_PAYROLL    => 'Payroll',
            User::ROLE_CMS        => 'CMS',
        ];
    }

    public function store(Request $request)
    {
        // Dua cara: pilih karyawan yang sudah terdaftar, atau buat akun baru.
        $mode = $request->input('mode', 'baru');

        $aturanUmum = [
            'role'          => ['required', Rule::in(array_keys($this->daftarPeran()))],
            'project_ids'   => 'nullable|array',
            'project_ids.*' => 'exists:projects,id',
        ];

        if ($mode === 'karyawan') {
            $validated = $request->validate($aturanUmum + [
                'user_id' => ['required', 'exists:users,id'],
            ]);

            $user = User::findOrFail($validated['user_id']);

            if ($user->role === User::ROLE_SUPERADMIN) {
                return back()->withErrors(['user_id' => 'Superadmin tidak bisa diubah dari halaman ini.'])->withInput();
            }

            $user->update(['role' => $validated['role']]);
            $user->accessibleProjects()->sync($validated['project_ids'] ?? []);

            return redirect()->route('admin.permissions.index')
                ->with('success', $user->name . ' sekarang punya akses admin dengan otoritas project yang dipilih.');
        }

        $validated = $request->validate($aturanUmum + [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'role'     => $validated['role'],
        ]);

        $user->accessibleProjects()->sync($validated['project_ids'] ?? []);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Admin berhasil ditambahkan dengan otoritas project yang dipilih.');
    }

    public function edit(User $permission)
    {
        $projects = Project::orderBy('name')->get();
        $roles = $this->daftarPeran();

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
