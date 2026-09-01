<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     * Supports remember me token for persistent sessions.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials do not match our records.'),
            ]);
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Your account has been deactivated. Contact your administrator.'),
            ]);
        }

        // Check if user has admin-level role
        if (!$user->hasAnyRole(['super-admin', 'school-admin', 'sub-admin', 'incharge'])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('You do not have permission to access the admin panel.'),
            ]);
        }

        // For school-scoped users, verify school is active
        if (!$user->hasRole('super-admin') && $user->school_id) {
            $school = $user->school;
            if (!$school || !$school->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => __('Your school account has been deactivated.'),
                ]);
            }
        }

        // Record login
        $user->recordLogin($request->ip());

        // Regenerate session
        $request->session()->regenerate();

        // Redirect based on role
        if ($user->hasRole('super-admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }
        return redirect()->intended(route('user.dashboard'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
