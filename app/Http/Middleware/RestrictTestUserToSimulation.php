<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictTestUserToSimulation
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $email = strtolower((string) $user->email);
        if ($email !== 'test@example.com') {
            return $next($request);
        }

        $allowedRouteNames = [
            'simulasi',
            'calculatesimulasi',
            'storesimulasi',
            'downloadpdfsimulasi',
            'kb_simulasi.index',
            'kb_simulasi.calculate',
            'kb_simulasi.store',
            'kb_simulasi.download_pdf',
            'logout',
        ];

        $currentRouteName = $request->route()?->getName();

        if ($currentRouteName !== null && in_array($currentRouteName, $allowedRouteNames, true)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Akses dibatasi. User ini hanya dapat membuka menu Simulasi.',
            ], 403);
        }

        return redirect()->route('simulasi');
    }
}
