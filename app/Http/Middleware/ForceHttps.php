<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Only modify the request variable if running locally AND if needed.
        // Avoid forcing HTTPS=true if the local server isn't actually serving HTTPS.
        if (app()->environment('local')) {
            // $request->server->set('HTTPS', true); // Commented out: Avoid forcing HTTPS locally unless intended
        }

        // Note: If you *do* have a local HTTPS setup (e.g., Laragon SSL),
        // you might keep the line above or handle HTTPS redirection differently.
        // For standard local HTTP development, it's safer commented out.

        // If not local, or if you want to enforce HTTPS in production/staging,
        // you might add redirection logic here, e.g.:
        // if (!$request->secure() && app()->environment(['production', 'staging'])) {
        //     return redirect()->secure($request->getRequestUri());
        // }

        return $next($request);
    }
}
