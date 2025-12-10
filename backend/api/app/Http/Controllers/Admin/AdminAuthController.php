<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && in_array(Auth::user()->role, User::adminRoles())) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! in_array($user->role, User::adminRoles())) {
            Auth::logout();

            return back()->withErrors([
                'username' => 'Akses dashboard hanya untuk admin.',
            ])->onlyInput('username');
        }

        return redirect()->intended($this->getDefaultRoute($user));
    }

    protected function getDefaultRoute($user): string
    {
        if ($user->isHrd()) {
            return route('admin.hrd.applications');
        }
        if ($user->isPayroll()) {
            return route('admin.payroll.index');
        }
        if ($user->isCms()) {
            return route('admin.cms-hero-slides.index');
        }
        return route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
