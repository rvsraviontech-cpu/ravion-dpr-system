<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserAccountIsActive
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

        if ($user->account_status !== 'Active') {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = match ($user->account_status) {
                'Suspended' => 'Your Ravion ERP account has been suspended.',
                'Inactive' => 'Your Ravion ERP account is inactive.',
                'Exited' => 'Your Ravion ERP account is no longer active.',
                default => 'Your Ravion ERP account is not permitted to continue.',
            };

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $message . ' Please contact the administrator.',
                ]);
        }

        return $next($request);
    }
}