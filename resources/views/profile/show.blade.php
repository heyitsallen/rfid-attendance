@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
@php
    /** @var \App\Models\User $user */
    // Prefer accessor + active roles (many-to-many), with safe fallbacks
    $primaryRole = $user->role_name; // from getRoleNameAttribute
    $activeRoles = method_exists($user, 'activeRoles')
        ? ($user->relationLoaded('roles') ? $user->activeRoles : $user->activeRoles())->pluck('name')->map(fn($n)=>strtolower($n))->unique()->values()
        : collect([$primaryRole])->filter();

    // IDs from profile tables, with legacy fallbacks
    $studentNo = optional($user->studentProfile)->student_no ?? ($user->student_id ?? null);
    $employeeNo = optional($user->facultyProfile)->employee_no ?? ($user->employee_no ?? null);

    // Decide which edit route to show (priority: admin > faculty > student), fallback to profile.show
    if ($user->hasRole('admin') || $primaryRole === 'admin') {
        $editRoute = route('admin.profile.edit');
    } elseif ($user->hasRole('faculty') || $primaryRole === 'faculty') {
        $editRoute = route('faculty.profile.edit');
    } elseif ($user->hasRole('student') || $primaryRole === 'student') {
        $editRoute = route('student.profile.edit');
    } else {
        $editRoute = route('profile.show');
    }
@endphp

<div class="w-full max-w-2xl mx-auto p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-4">My Account</h2>

    <div class="flex items-center space-x-4 mb-6">
        {{-- Profile photo --}}
        @php $photo = $user->profile_photo ? asset('storage/'.$user->profile_photo) : asset('images/avatar.png'); @endphp
        <img src="{{ $photo }}"
             class="w-20 h-20 rounded-full border object-cover"
             alt="Profile Photo">

        <div>
            <p class="text-lg font-semibold">{{ $user->full_name }}</p>
            <p class="text-sm text-gray-600">{{ $user->email }}</p>
            <span class="text-xs px-2 py-1 rounded
                {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ ucfirst($user->status) }}
            </span>
        </div>
    </div>

    <div class="space-y-2 text-sm">
        <p class="flex items-center gap-2">
            <strong>Role:</strong>
            @if($activeRoles->isNotEmpty())
                <span class="flex flex-wrap gap-1">
                    @foreach ($activeRoles as $r)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 capitalize">{{ $r }}</span>
                    @endforeach
                </span>
            @else
                <span class="text-gray-600 capitalize">{{ $primaryRole ?? 'user' }}</span>
            @endif
        </p>

        @if (($primaryRole === 'student' || $user->hasRole('student')) && $studentNo)
            <p><strong>Student ID:</strong> {{ $studentNo }}</p>
        @elseif (($primaryRole === 'faculty' || $user->hasRole('faculty')) && $employeeNo)
            <p><strong>Employee No:</strong> {{ $employeeNo }}</p>
        @endif
    </div>

    <div class="mt-6 flex space-x-3">
        {{-- Back button --}}
        <a href="{{ url()->previous() }}"
           class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">
           Back
        </a>

        {{-- Edit profile (based on resolved role) --}}
        <a href="{{ $editRoute }}"
           class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">
           Edit Profile
        </a>
    </div>
</div>
@endsection
