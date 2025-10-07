@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="p-6 w-full min-h-screen bg-cover bg-center" 
     style="background-image: url('{{ asset('images/background.jpg') }}');">
<div class="w-full max-w-2xl mx-auto p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-4">My Account</h2>

    <div class="flex items-center space-x-4 mb-6">
        {{-- Profile photo --}}
        @if ($user->profile_photo)
            <img src="{{ asset('storage/' . $user->profile_photo) }}"
                 class="w-20 h-20 rounded-full border object-cover"
                 alt="Profile Photo">
        @else
            <img src="{{ asset('images/avatar.png') }}"
                 class="w-10 h-10 rounded-full border object-cover"
                 alt="Default Avatar">
        @endif

        <div>
            <p class="text-lg font-semibold">{{ $user->firstname }} {{ $user->lastname }}</p>
            <p class="text-sm text-gray-600">{{ $user->email }}</p>
            <span class="text-xs px-2 py-1 rounded 
                {{ $user->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ ucfirst($user->status) }}
            </span>
        </div>
    </div>

    <div class="space-y-2 text-sm">
        <p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
        @if ($user->role === 'student' && $user->student_id)
            <p><strong>Student ID:</strong> {{ $user->student_id }}</p>
        @elseif ($user->role === 'faculty' && $user->employee_no)
            <p><strong>Employee No:</strong> {{ $user->employee_no }}</p>
        @endif
    </div>

    <div class="mt-6 flex space-x-3">
        {{-- Back button --}}
        <a href="{{ url()->previous() }}"
           class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 rounded">
           Back
        </a>

        {{-- Edit profile button depending on role --}}
        @if ($user->role === 'student')
            <a href="{{ route('student.profile.edit') }}"
               class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">
               Edit Profile
            </a>
        @elseif ($user->role === 'faculty')
            <a href="{{ route('faculty.profile.edit') }}"
               class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">
               Edit Profile
            </a>
        @elseif ($user->role === 'admin')
            <a href="{{ route('admin.profile.edit') }}"
               class="px-4 py-2 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">
               Edit Profile
            </a>
        @endif
    </div>
</div>
</div>
@endsection
