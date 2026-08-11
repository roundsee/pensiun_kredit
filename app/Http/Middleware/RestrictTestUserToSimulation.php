<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        $role = $user->roleSlug();

        if ($this->isAdminRole($role)) {
            return $next($request);
        }

        $currentRouteName = $request->route()?->getName();
        if ($currentRouteName === null) {
            return $next($request);
        }

        if ($this->canAccessRoute($role, $currentRouteName)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Akses dibatasi sesuai role user.',
            ], 403);
        }

        return redirect()->route($this->defaultRouteForRole($role));
    }

    private function canAccessRoute(string $role, string $routeName): bool
    {
        if (in_array($routeName, ['logout', 'dashboard'], true)) {
            return true;
        }

        if ($role === User::ROLE_MARKETING) {
            return in_array($routeName, [
                'simulasi',
                'calculatesimulasi',
                'storesimulasi',
                'downloadpdfsimulasi',
                'kb_simulasi.index',
                'kb_simulasi.calculate',
                'kb_simulasi.store',
                'kb_simulasi.download_pdf',
            ], true);
        }

        if ($this->isSimulationDataRole($role)) {
            if (str_starts_with($routeName, 'data_simulasi.')) {
                return true;
            }

            return in_array($routeName, [
                'kb_simulasi.index',
                'kb_simulasi.calculate',
                'kb_simulasi.store',
                'kb_simulasi.download_pdf',
            ], true);
        }

        return true;
    }

    private function defaultRouteForRole(string $role): string
    {
        if ($role === User::ROLE_MARKETING) {
            return 'kb_simulasi.index';
        }

        if ($this->isSimulationDataRole($role)) {
            return 'data_simulasi.trial.list';
        }

        return 'dashboard';
    }

    private function isSimulationDataRole(string $role): bool
    {
        return in_array($role, [
            User::ROLE_SUPPORT_BISNIS,
            User::ROLE_OPERATION,
        ], true);
    }

    private function isAdminRole(string $role): bool
    {
        return in_array($role, [
            User::ROLE_ADMIN,
            User::ROLE_SUPERVISOR,
        ], true);
    }
}
