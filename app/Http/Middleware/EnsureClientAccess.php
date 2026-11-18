<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = Auth::guard('client')->user();
        if (!$client) {
            return redirect()->route('client.login');
        }
        if ($client->status !== 'Active' || $client->deactivated_at !== null) {
            Auth::guard('client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Client account is not active.');
        }
        if ($client->must_change_password && !$request->routeIs('client.password.edit', 'client.password.update', 'client.logout')) {
            return redirect()->route('client.password.edit');
        }
        return $next($request);
    }
}
