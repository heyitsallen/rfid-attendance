<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        // Ensure only admins can view/update this page
        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        return view('profile.admin-edit', ['user' => $user]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'firstname'      => 'required|string|max:255',
            'middlename'     => 'nullable|string|max:255',
            'lastname'       => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $user->id,
            'profile_photo'  => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'remove_photo'   => 'sometimes|boolean',
        ]);

        // Handle remove photo option
        if ($request->boolean('remove_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = null;
        }

        // Handle new upload
        if ($request->hasFile('profile_photo')) {
            // delete old if exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}
