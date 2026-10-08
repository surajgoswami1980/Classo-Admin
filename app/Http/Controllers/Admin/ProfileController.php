<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('admin.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'phone' => 'nullable|string|max:15',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'New password and confirmation do not match.',
            'new_password.min' => 'New password must be at least 8 characters.',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return back()->with('success', 'Password changed successfully');
    }

    public function settings()
    {
        $user = auth()->user();
        $school = $user->school;
        return view('admin.profile.settings', compact('user', 'school'));
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();

        // Only school-admin can update school settings
        if ($user->school_id && $user->hasRole('school-admin') && $user->school) {
            $validated = $request->validate([
                'school_name' => 'nullable|string|max:255',
                'school_email' => 'nullable|email|max:255',
                'school_phone' => 'nullable|string|max:15',
            ]);

            $user->school->update([
                'name' => $validated['school_name'] ?? $user->school->name,
                'email' => $validated['school_email'] ?? $user->school->email,
                'phone' => $validated['school_phone'] ?? $user->school->phone,
            ]);
        }

        return back()->with('success', 'Settings saved');
    }

    /**
     * Persist OTP-login preferences into schools.settings JSON.
     * Only a school-admin may change their own school's login policy.
     */
    public function updateOtpSettings(Request $request)
    {
        $user = auth()->user();

        if (!($user->school_id && $user->hasRole('school-admin') && $user->school)) {
            return back()->withErrors(['error' => 'Only a school admin can change login settings.']);
        }

        $validated = $request->validate([
            'otp_login_enabled' => 'nullable|boolean',
            'otp_channels' => 'nullable|array',
            'otp_channels.*' => 'in:email,mobile',
        ]);

        $enabled = $request->boolean('otp_login_enabled');
        $channels = array_values(array_intersect($validated['otp_channels'] ?? [], ['email', 'mobile']));
        if (empty($channels)) {
            $channels = ['email', 'mobile'];
        }

        $settings = $user->school->settings ?? [];
        $settings['otp_login_enabled'] = $enabled;
        $settings['otp_channels'] = $channels;
        $user->school->settings = $settings;
        $user->school->save();

        return back()->with('success', 'Login (OTP) settings updated.');
    }
}
