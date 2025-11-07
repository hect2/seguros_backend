<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Fuerza el header Accept: application/json
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        // Si no es JSON, lo convertimos
        if (! $response->headers->has('Content-Type') ||
            $response->headers->get('Content-Type') !== 'application/json') {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
