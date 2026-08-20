<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Force Password Change
        |--------------------------------------------------------------------------
        |
        | When an administrator creates or resets a temporary password,
        | must_change_password is set to true.
        |
        | Until the user changes that password, they must not be allowed
        | to access any other authenticated ERP route.
        |
        */

        if (
            $user->must_change_password
            && ! $request->routeIs([
                'password.change-required',
                'password.change-required.update',
                'logout',
            ])
        ) {
            return redirect()
                ->route('password.change-required');
        }

        return $next($request);
    }
}