@extends('layouts.app')

@section('title', 'Admin Profile')

@section('content')
<div class="max-w-xl mx-auto p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Admin Profile</h2>

    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-green-50 text-green-800 text-sm dark:bg-green-900/30 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm dark:bg-red-900/30 dark:text-red-300">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('faculty.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Profile Photo --}}
        <div class="flex items-center gap-4">
            <div>
                @if ($user->profile_photo)
                    <img src="{{ asset('storage/'.$user->profile_photo) }}" class="w-20 h-20 rounded-full object-cover border">
                @else
                    <img src="{{ asset('images/avatar.png') }}" class="w-20 h-20 rounded-full object-cover border">
                @endif
            </div>
            <div>
                <label for="profile_photo" class="block mb-1 text-sm font-medium text-gray-600 dark:text-gray-300">Change Photo</label>
                <input type="file" name="profile_photo" id="profile_photo" accept="image/*" class="block w-full text-sm text-gray-600">
                <p class="text-xs text-gray-500 mt-1">JPG/PNG, up to 10MB</p>
            </div>
        </div>

        {{-- Floating input fields --}}
        <div class="relative z-0 w-full mb-5 group">
            <input type="text" name="firstname" id="firstname"
                   value="{{ old('firstname', $user->firstname) }}"
                   class="peer block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300
                          appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500
                          focus:outline-none focus:ring-0 focus:border-blue-600"
                   placeholder=" " required />
            <label for="firstname"
                   class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0]
                          peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                          peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600 peer-focus:dark:text-blue-500">
                First Name
            </label>
        </div>

        <div class="relative z-0 w-full mb-5 group">
            <input type="text" name="middlename" id="middlename"
                   value="{{ old('middlename', $user->middlename) }}"
                   class="peer block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300
                          appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500
                          focus:outline-none focus:ring-0 focus:border-blue-600"
                   placeholder=" " />
            <label for="middlename" class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0]
                          peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                          peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600 peer-focus:dark:text-blue-500">
                Middle Name
            </label>
        </div>

        <div class="relative z-0 w-full mb-5 group">
            <input type="text" name="lastname" id="lastname"
                   value="{{ old('lastname', $user->lastname) }}"
                   class="peer block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300
                          appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500
                          focus:outline-none focus:ring-0 focus:border-blue-600"
                   placeholder=" " required />
            <label for="lastname" class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0]
                          peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                          peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600 peer-focus:dark:text-blue-500">
                Last Name
            </label>
        </div>

        <div class="relative z-0 w-full mb-5 group">
            <input type="email" name="email" id="email"
                   value="{{ old('email', $user->email) }}"
                   class="peer block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300
                          appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500
                          focus:outline-none focus:ring-0 focus:border-blue-600"
                   placeholder=" " required />
            <label for="email" class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0]
                          peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0
                          peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600 peer-focus:dark:text-blue-500">
                Email
            </label>
        </div>

            {{-- Change password trigger --}}
 <div class="mt-6">
    <a href="#"
       data-modal-target="changePasswordModal"
       data-modal-toggle="changePasswordModal"
       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
       Change Password
    </a>
</div>

        {{-- Save buttons --}}
        <div class="flex justify-end gap-2 mt-6">
            <a href="{{ url()->previous() }}" class="px-4 py-2 rounded border">Cancel</a>
            <button class="px-4 py-2 rounded text-white bg-blue-600 hover:bg-blue-700">Save changes</button>
        </div>
    </form>

</div>

{{-- Password Modal --}}
<div id="changePasswordModal" tabindex="-1" aria-hidden="true"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</h3>

        <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
            @csrf
            {{-- Current password --}}
            <div>
                <label class="block text-sm mb-1">Current Password</label>
                <input type="password" name="current_password" required
                       class="w-full border rounded p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                @error('current_password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- New password --}}
            <div>
                <label class="block text-sm mb-1">New Password</label>
                <input type="password" name="password" required
                       class="w-full border rounded p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            {{-- Confirm password --}}
            <div>
                <label class="block text-sm mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border rounded p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button"
                        data-modal-hide="changePasswordModal"
                        class="px-4 py-2 rounded border">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection