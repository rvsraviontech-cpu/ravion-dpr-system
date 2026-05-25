<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    )
    {
        if(
            !in_array(
                auth()->user()->role->name,
                $roles
            )
        )
        {
            abort(403, 'Unauthorized Access');
        }

        return $next($request);
    }
}