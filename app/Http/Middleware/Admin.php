<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the authenticated user's 'admin' property is true.
        if (auth()->user() && auth()->user()->admin) {
            // If they are an admin, allow the request to proceed.
            return $next($request);
        }

        // If not an admin, stop the request and show a 403 Forbidden error page.
        return abort(403, 'Only administrators can access this page');
    }
}
