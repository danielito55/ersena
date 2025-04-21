<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && (App::environment('production') || App::environment('local'))) {
            // Forzar HTTPS en producción y desarrollo local
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
