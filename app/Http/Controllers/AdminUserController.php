<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\SchoolYear;

class AdminUserController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['student','faculty'])],
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'student_no' => 'nullable|required_if:role,student|unique:users,student_no',
            'employee_no' => 'nullable|required_if:role,faculty|unique:users,employee_no',
        ]);

        $payload = [
            'firstname' => $data['firstname'],
            'middlename' => $data['middlename'] ?? null,
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
            'status' => 'active',
        ];

        if ($data['role'] === 'student') $payload['student_no'] = $data['student_no'];
        if ($data['role'] === 'faculty') $payload['employee_no'] = $data['employee_no'];

        User::create($payload);

        return back()->with('success', ucfirst($data['role']).' added.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'firstname' => 'required|string|max:255',
            'middlename' => 'nullable|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'student_no' => ['nullable', Rule::unique('users','student_no')->ignore($user->id)],
            'employee_no' => ['nullable', Rule::unique('users','employee_no')->ignore($user->id)],
            'status' => ['required', Rule::in(['active','inactive'])],
        ]);

        $user->update($data);

        // Optional: linking a new card to current SY happens via AdminCardController@link
        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->update(['status' => 'inactive']);
        return back()->with('success', 'User set to inactive.');
    }
}
