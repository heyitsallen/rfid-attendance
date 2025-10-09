<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('student')) {
            abort(403, 'Unauthorized');
        }

        return view('profile.student-edit', [
            'user'    => $user,
            'profile' => $user->studentProfile, // contains student_no
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('student')) {
            abort(403, 'Unauthorized');
        }

        $profile = $user->studentProfile; // may be null on first setup
        $profileId = $profile?->id;

        $data = $request->validate([
            'firstname'      => ['required','string','max:255'],
            'middlename'     => ['nullable','string','max:255'],
            'lastname'       => ['required','string','max:255'],
            'email'          => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'student_no'     => [
                'nullable','string','max:255',
                Rule::unique('student_profiles','student_no')->ignore($profileId),
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

        // Upsert student profile if student_no provided
        if (array_key_exists('student_no', $data)) {
            if ($profile) {
                $profile->update(['student_no' => $data['student_no']]);
            } else {
                if (!empty($data['student_no'])) {
                    $user->studentProfile()->create(['student_no' => $data['student_no']]);
                }
            }
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
