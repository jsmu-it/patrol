<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', User::adminRoles())
            ->orderBy('name')
            ->paginate(20);

        return view('admin.admin-users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.admin-users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(User::adminRoles())],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.admin-users.index')
            ->with('status', 'User admin berhasil ditambahkan.');
    }

    public function edit(User $admin_user)
    {
        return view('admin.admin-users.edit', ['user' => $admin_user]);
    }

    public function update(Request $request, User $admin_user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($admin_user->id)],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($admin_user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(User::adminRoles())],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $admin_user->update($validated);

        return redirect()->route('admin.admin-users.index')
            ->with('status', 'User admin berhasil diupdate.');
    }

    public function destroy(User $admin_user)
    {
        if ($admin_user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $admin_user->delete();

        return redirect()->route('admin.admin-users.index')
            ->with('status', 'User admin berhasil dihapus.');
    }
}
