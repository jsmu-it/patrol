<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Login Portal JSMU.
 *
 * Memakai akun yang sama dengan yang dibuat lewat formulir PDP: username
 * adalah NIP karyawan. Berbeda dengan login admin yang menolak peran GUARD,
 * portal terbuka untuk semua karyawan yang punya akun.
 */
class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('portal.home');
        }

        return view('portal.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $kredensial = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($kredensial, $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'NIP atau kata sandi salah.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('portal.home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
