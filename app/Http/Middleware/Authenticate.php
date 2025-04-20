<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if ($request->ajax()) {
            if (!Auth::check()) {
                return response()->json(['authenticated' => false], 401);
            }
        }

        // Verificar si la sesión está activa
        if (!Auth::check()) {
            Auth::logout();
            if ($request->ajax()) {
                return response()->json(['authenticated' => false], 401);
            }
            return redirect()->route('login');
        }

        // Forzar que no se cachee la respuesta
        $response = $next($request);
        
        if (!$request->ajax()) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
