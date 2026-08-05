<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the authenticated user's 'active' property is true.
        if (auth()->user() && auth()->user()->active) {
            // If they are active, let the request proceed.
            return $next($request);
        }

        // If the user is not active, first log them out.
        auth('web')->logout();

        // Then, abort the request with a 403 error and a helpful message.
        return abort(403, 'Your account is not active. Please contact the administrator.');
    }
}
