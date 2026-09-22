<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // API routes should never redirect to login page
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // Portal karyawan punya pintu masuknya sendiri; login admin menolak
        // peran GUARD sehingga karyawan akan mentok bila diarahkan ke sana.
        if ($request->is('portal') || $request->is('portal/*')) {
            return route('portal.login');
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return route('admin.login');
        }

        return route('admin.login'); // Default fallback to admin login since we don't have public user login yet
    }
}
