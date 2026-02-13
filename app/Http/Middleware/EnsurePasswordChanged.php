<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     * Redirect users with must_change_password to the password change page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }
        if ($user->must_change_password) {
            if (!$request->routeIs('password.change*') && !$request->routeIs('logout')) {
                return redirect()->route('password.change');
            }
        }
        return $next($request);
    }
}
