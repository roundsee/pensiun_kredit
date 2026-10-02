<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentikasi endpoint API pencairan platinum.
 *
 * Google Apps Script tidak punya konsep user/session, jadi cukup satu bearer
 * token statis yang disimpan di .env. Token dibandingkan dengan hash_equals()
 * supaya tidak bocor lewat timing.
 */
class VerifyPencairanApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = (string) config('pencairan_platinum.api_token');

        if ($configured === '') {
            return response()->json([
                'success' => false,
                'message' => 'API token belum dikonfigurasi di server (PENCAIRAN_PLATINUM_API_TOKEN).',
            ], 500);
        }

        $token = $request->bearerToken();

        if ($token === null || ! hash_equals($configured, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: token tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}