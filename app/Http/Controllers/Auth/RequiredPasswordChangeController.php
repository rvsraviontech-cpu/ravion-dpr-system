<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RequiredPasswordChangeController extends Controller
{
    /**
     * Display the mandatory password change screen.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        /*
         * If the user is not required to change the password,
         * there is no reason to display this screen.
         */
        if (! $user->must_change_password) {
            return $this->redirectToDashboard($user);
        }

        return view('auth.change-required-password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->must_change_password) {
            return $this->redirectToDashboard($user);
        }

        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],
        ]);

        /*
         * Prevent the temporary/reset password from being
         * immediately reused as the new permanent password.
         */
        if (Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors([
                    'password' => 'Your new password must be different from your current temporary password.',
                ]);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'password_changed_at' => now(),
            'must_change_password' => false,

            /*
             * Invalidate persistent remember-me authentication
             * created before this password change.
             */
            'remember_token' => null,
        ])->save();

        return $this->redirectToDashboard($user)
            ->with(
                'success',
                'Your password has been changed successfully.'
            );
    }

    /**
     * Redirect the user to the correct ERP dashboard.
     */
    private function redirectToDashboard($user): RedirectResponse
    {
        return match ($user->role?->name) {
            'Admin' => redirect('/admin-dashboard'),

            'Engineer',
            'Site Engineer',
            'Site Supervisor' => redirect('/engineer-dashboard'),

            'PMO',
            'DGM' => redirect('/pmo-dashboard'),

            'CEO' => redirect('/ceo-dashboard'),

            default => redirect('/dashboard'),
        };
    }
}