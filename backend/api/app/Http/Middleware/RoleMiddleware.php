<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): JsonResponse|SymfonyResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('admin.login');
        }

        $allowedRoles = (array) $roles;
        $isSuperAdminRequired = in_array('SUPERADMIN', $allowedRoles, true);

        if (! in_array($user->role, $allowedRoles, true)) {
            // Special case: if SUPERADMIN is required, HRD is also allowed
            if (! ($isSuperAdminRequired && $user->isSuperAdmin())) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Forbidden.'], 403);
                }
                abort(403);
            }
        }

        return $next($request);
    }
}
