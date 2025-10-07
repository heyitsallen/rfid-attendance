<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'RFID Attendance Tracker')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>[x-cloak]{display:none!important}</style>
    {{-- Tailwind via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Flowbite CSS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 text-gray-900">

    {{-- Navbar --}}
    <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
        <div class="px-4 py-3 flex items-center justify-between">
            {{-- Left side --}}
            <div class="flex items-center">
                <img class="w-10 h-10 mr-2" src="{{ asset('images/logo.png') }}" alt="SPCC Logo">
                <span class="ms-3 font-bold text-xl">SPCC RFID ATTENDANCE TRACKER</span>
            </div>

            {{-- Center nav links --}}
            @php
                $user = Auth::user();
                $active = $active ?? ''; // current active link (set in controllers)
            @endphp
@if ($user->role === 'admin')
<a href="{{ route('admin.dashboard') }}"
   class="pb-2 {{ $active === 'dashboard'  ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-600' }} font-medium"> 
    Dashboard
</a>

<a href="{{ route('admin.management') }}"
   class="pb-2 {{ $active === 'management' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-600' }} font-medium"> 
    Management
</a>

<a href="{{ route('admin.attendance') }}"
   class="pb-2 {{ $active === 'attendance' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-600' }} font-medium">
    Attendance Log
</a>

<a href="{{ route('admin.reports') }}"
   class="pb-2 {{ $active === 'reports'  ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-600' }} font-medium">
   Reports
</a>

@elseif ($user->role === 'faculty')
    <a href="{{ route('faculty.attendance') }}"
       class="pb-2 {{ $active === 'attendance'  ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-600' }} font-medium">
        Class attendances
    </a>
    <a href="{{ route('faculty.schedule') }}"
       class="pb-2 {{ $active === 'schedule'  ? 'text-blue-700 border-b-2 border-blue-700'  : 'text-gray-700 hover:text-blue-600' }} font-medium">
        Class Schedule
    </a>
    <a href="{{ route('faculty.personal') }}"
       class="pb-2 {{ $active === 'personal'  ? 'text-blue-700 border-b-2 border-blue-700'  : 'text-gray-700 hover:text-blue-600' }} font-medium">
        My attendance
    </a>
@elseif ($user->role === 'student')
    <a href="{{ route('student.attendance') }}"
       class="pb-2 {{ $active === 'attendance'  ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-700 hover:text-blue-600' }} font-medium">
        My Attendance
    </a>
    <a href="{{ route('student.schedule') }}"
       class="pb-2 {{ ($active ?? '') === 'schedule' 
           ? 'text-blue-700 border-b-2 border-blue-700' 
           : 'text-gray-700 hover:text-blue-600' }} font-medium">
        My Class Schedule
    </a>
@endif


            {{-- Right side: user dropdown --}}
            <div class="relative">
                <button id="dropdownUserButton" data-dropdown-toggle="dropdownUser"
                        class="flex items-center space-x-2 focus:outline-none">
                    @if ($user->profile_photo)
                        <img src="{{ asset('storage/'.$user->profile_photo) }}"
                             class="w-8 h-8 rounded-full object-cover border">
                    @else
                        <img src="{{ asset('images/avatar.png') }}"
                             class="w-8 h-8 rounded-full object-cover border">
                    @endif
                    <span class="hidden sm:block font-medium text-gray-700 dark:text-gray-200">
                        {{ $user->firstname }} {{ $user->lastname }}
                    </span>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown -->
                <div id="dropdownUser"
                     class="hidden z-50 my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600">
                    <div class="px-4 py-3">
                        <span class="block text-sm text-gray-900 dark:text-white">{{ $user->email }}</span>
                        <span class="block text-sm text-gray-500 truncate dark:text-gray-400 capitalize">{{ $user->role }}</span>
                    </div>
                    <ul class="py-2">
                        <li>
                            <a href="{{ route('profile.show') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                               Manage Account
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    {{-- Page wrapper --}}
    <div class="flex pt-16">
        @yield('content')
    </div>

    {{-- Flowbite JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
</body>
</html>
