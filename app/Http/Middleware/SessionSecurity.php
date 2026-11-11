<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionSecurity
{
    public function handle(Request $request, Closure $next)
    {
        $session = $request->session();
        if (Auth::check() || $session->has('solicitante_id')) {
            $last = $session->get('last_activity', time());
            if (time() - $last >= 1800) {
                Auth::logout();
                $session->invalidate();
                $session->regenerateToken();
                $response = $request->expectsJson()
                    ? response()->json(['message' => 'Su sesión expiró por inactividad.', 'error' => 'Su sesión expiró por inactividad.'], 401)
                    : redirect()->route($request->is('solicitud*') ? 'solicitud.login' : 'login')
                        ->with('error', 'Su sesión expiró después de 30 minutos de inactividad.');
            } else {
                $session->put('last_activity', time());
            }
        }

        $response = $response ?? $next($request);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
}
