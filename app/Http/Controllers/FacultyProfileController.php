<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FacultyProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('faculty')) {
            abort(403, 'Unauthorized');
        }

        // Ensure we pass existing profile (may be null if not created yet)
        return view('profile.faculty-edit', [
            'user'    => $user,
            'profile' => $user->facultyProfile, // contains employee_no
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('faculty')) {
            abort(403, 'Unauthorized');
        }

        $profile = $user->facultyProfile; // may be null on first setup
        $profileId = $profile?->id;

        $data = $request->validate([
            'firstname'      => ['required','string','max:255'],
            'middlename'     => ['nullable','string','max:255'],
            'lastname'       => ['required','string','max:255'],
            'email'          => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'employee_no'    => [
                'nullable','string','max:255',
                Rule::unique('faculty_profiles','employee_no')->ignore($profileId),
            ],
            'profile_photo'  => ['nullable','image','mimes:jpg,jpeg,png','max:10240'],
            'remove_photo'   => ['sometimes','boolean'],
        ]);

        // Photo removal
        if ($request->boolean('remove_photo') && $user->profile_photo) {
            if (Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = null;
        }

        // New upload
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Update core user fields
        $user->update([
            'firstname'     => $data['firstname'],
            'middlename'    => $data['middlename'] ?? null,
            'lastname'      => $data['lastname'],
            'email'         => $data['email'],
            'profile_photo' => $data['profile_photo'] ?? $user->profile_photo,
        ]);

        // Upsert faculty profile if employee_no provided (or keep existing)
        if (array_key_exists('employee_no', $data)) {
            if ($profile) {
                $profile->update(['employee_no' => $data['employee_no']]);
            } else {
                if (!empty($data['employee_no'])) {
                    $user->facultyProfile()->create(['employee_no' => $data['employee_no']]);
                }
            }
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
