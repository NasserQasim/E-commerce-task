<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // For this demo, admin access is open.
        // In production, this would check authentication and role.
        // Example: if (!auth()->user()?->is_admin) abort(403);

        return $next($request);
    }
}
