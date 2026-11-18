<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ClientSessionController extends Controller
{
    public function loginForm()
    {
        return view('client-control.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $credentials['email'] = mb_strtolower(trim($credentials['email']));
        $client = \App\Models\ClientUser::where('email', $credentials['email'])->first();
        if (!$client || $client->status !== 'Active' || $client->deactivated_at !== null || !Hash::check($credentials['password'], $client->password)) {
            throw ValidationException::withMessages(['email' => 'The provided credentials are invalid or access is unavailable.']);
        }
        Auth::guard('client')->login($client); // No remember-me in first release.
        $request->session()->regenerate();
        $client->forceFill(['last_login_at' => now()])->save();
        return redirect()->intended(route('client.projects.index'));
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('client.login');
    }

    public function changePasswordForm()
    {
        return view('client-control.auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:client'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $client = Auth::guard('client')->user();
        $client->forceFill(['password' => $data['password'], 'must_change_password' => false])->save();
        $request->session()->regenerate();
        return redirect()->route('client.projects.index');
    }
}
