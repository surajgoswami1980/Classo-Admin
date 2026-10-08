<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\OtpService;
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

        return $this->completeLogin($request, $user);
    }

    /**
     * Shared gatekeeping + session finalize for both password and OTP login.
     */
    private function completeLogin(Request $request, User $user)
    {
        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Your account has been deactivated. Contact your administrator.'),
            ]);
        }

        if (!$user->hasAnyRole(['super-admin', 'school-admin', 'sub-admin', 'incharge'])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('You do not have permission to access the admin panel.'),
            ]);
        }

        if (!$user->hasRole('super-admin') && $user->school_id) {
            $school = $user->school;
            if (!$school || !$school->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => __('Your school account has been deactivated.'),
                ]);
            }
        }

        $user->recordLogin($request->ip());
        $request->session()->regenerate();

        if ($user->hasRole('super-admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }
        return redirect()->intended(route('user.dashboard'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // OTP Login (email / mobile)
    // ─────────────────────────────────────────────────────────────────────

    private function isEmail(string $value): bool
    {
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Find an admin-panel user by email or phone across all schools.
     * (The admin panel login isn't school-code scoped like the web portal.)
     */
    private function findUserByIdentifier(string $identifier): ?User
    {
        return User::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->where('email', $identifier)->orWhere('phone', $identifier))
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'school-admin', 'sub-admin', 'incharge']))
            ->first();
    }

    /**
     * Whether OTP login is allowed for this user. Super-admins: always.
     * School users: only if their school enables it in settings.
     *
     * @return array{allowed:bool, channels:array<int,string>}
     */
    private function otpPolicyForUser(User $user): array
    {
        if ($user->hasRole('super-admin')) {
            return ['allowed' => true, 'channels' => ['email', 'mobile']];
        }

        $settings = $user->school?->settings ?? [];
        $allowed = ($settings['otp_login_enabled'] ?? false) === true;
        $channels = $settings['otp_channels'] ?? ['email', 'mobile'];
        $channels = array_values(array_intersect($channels, ['email', 'mobile'])) ?: ['email', 'mobile'];

        return ['allowed' => $allowed, 'channels' => $channels];
    }

    private function mask(string $value): string
    {
        if ($this->isEmail($value)) {
            [$local, $domain] = explode('@', $value, 2);
            return substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 1)) . '@' . $domain;
        }
        $digits = preg_replace('/\D/', '', $value);
        return strlen($digits) <= 4 ? str_repeat('*', strlen($digits)) : str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
    }

    /**
     * Step 1: request an OTP (AJAX). Returns JSON.
     */
    public function requestOtp(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'identifier' => 'required|string|max:255',
            'channel' => 'nullable|in:email,mobile',
        ]);

        $identifier = trim($data['identifier']);
        $user = $this->findUserByIdentifier($identifier);

        // Generic response — don't reveal whether the account exists
        $channel = $data['channel'] ?? ($this->isEmail($identifier) ? 'email' : 'mobile');
        $generic = response()->json([
            'success' => true,
            'message' => 'If an account matches, an OTP has been sent.',
            'channel' => $channel,
            'masked' => $this->mask($identifier),
        ]);

        if (!$user) {
            return $generic;
        }

        $policy = $this->otpPolicyForUser($user);
        if (!$policy['allowed']) {
            return response()->json(['success' => false, 'message' => 'OTP login is not enabled for your school.'], 403);
        }

        $channel = $data['channel'] ?? ($this->isEmail($identifier) ? 'email' : 'mobile');
        if (!in_array($channel, $policy['channels'], true)) {
            return response()->json(['success' => false, 'message' => ucfirst($channel) . ' OTP is not enabled for your school.'], 422);
        }

        $destination = $channel === 'email' ? $user->email : $user->phone;
        if (!$destination) {
            return response()->json(['success' => false, 'message' => "No {$channel} on file for this account. Try the other method."], 422);
        }

        try {
            $result = $otp->request($channel, $destination, $user->name);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 429);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent.',
            'channel' => $channel,
            'masked' => $this->mask($destination),
            'cooldown' => $result['cooldown'] ?? 30,
            'dev_otp' => $result['dev_otp'] ?? null,
        ]);
    }

    /**
     * Step 2: verify an OTP and log the user in. Returns JSON with redirect.
     */
    public function verifyOtp(Request $request, OtpService $otp)
    {
        $data = $request->validate([
            'identifier' => 'required|string|max:255',
            'otp' => 'required|string|max:8',
        ]);

        $identifier = trim($data['identifier']);
        $user = $this->findUserByIdentifier($identifier);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials or OTP.'], 422);
        }

        $policy = $this->otpPolicyForUser($user);
        if (!$policy['allowed']) {
            return response()->json(['success' => false, 'message' => 'OTP login is not enabled for your school.'], 403);
        }

        $channel = $this->isEmail($identifier) ? 'email' : 'mobile';
        $destination = $channel === 'email' ? $user->email : $user->phone;
        if (!$destination) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials or OTP.'], 422);
        }

        try {
            $otp->verify($channel, $destination, trim($data['otp']));
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        // Run the same gatekeeping as password login
        if (!$user->is_active
            || !$user->hasAnyRole(['super-admin', 'school-admin', 'sub-admin', 'incharge'])
            || (!$user->hasRole('super-admin') && $user->school_id && !($user->school?->is_active))) {
            return response()->json(['success' => false, 'message' => 'Your account cannot access the admin panel.'], 403);
        }

        Auth::login($user, true);
        $user->recordLogin($request->ip());
        $request->session()->regenerate();

        $redirect = $user->hasRole('super-admin') ? route('admin.dashboard') : route('user.dashboard');

        return response()->json(['success' => true, 'redirect' => $redirect]);
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
