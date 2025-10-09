<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'], // or: ['required','current_password']
            'password'         => ['required','string','min:6','confirmed'],
            'logout_others'    => ['sometimes','boolean'],
        ]);

        $user = $request->user();

        // Manually verify current password (works even if you don't enable the current_password rule)
        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        // Update password + rotate remember token
        $user->forceFill([
            'password'       => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Optionally log out other devices/sessions
        if ($request->boolean('logout_others')) {
            // Requires the new plain password to rehash and kill other sessions
            Auth::logoutOtherDevices($request->password);
        }

        // Refresh current session
        $request->session()->regenerate();

        return back()->with('success', 'Password updated successfully.');
    }
}
