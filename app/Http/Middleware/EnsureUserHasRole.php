<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Allow bypassing role checks via environment/config while transitioning.
        if (env('DISABLE_ROLE_CHECK', false) || config('app.disable_role_check', false)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if ($roles === [] || $user->hasAnyRole($roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}