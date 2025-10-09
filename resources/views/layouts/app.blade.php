<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'RFID Attendance Tracker')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Tailwind via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Flowbite CSS --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 text-gray-900">

    {{-- Navbar --}}
    <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
        <div class="px-4 py-3 flex items-center justify-between">
            {{-- Left side (UNCHANGED POSITION) --}}
            <div class="flex items-center">
                <img class="w-10 h-10 mr-2" src="{{ asset('images/logo.png') }}" alt="SPCC Logo">
                <span class="ms-3 font-bold text-xl">SPCC RFID ATTENDANCE TRACKER</span>
            </div>

            {{-- Center nav links (UNCHANGED POSITION) --}}
            @php
                /** @var \App\Models\User|null $user */
                $user   = auth()->user();
                $active = $active ?? '';

                // Prefer accessor from model; fallback to hasRole checks
                $roleName = $user?->role_name;
                if (!$roleName && $user) {
                    $roleName = $user->hasRole('admin') ? 'admin'
                              : ($user->hasRole('faculty') ? 'faculty'
                              : ($user->hasRole('student') ? 'student' : null));
                }

                // helper for active class
                $navClass = function (string $key) use ($active) {
                    return $active === $key
                        ? 'pb-2 text-blue-700 border-b-2 border-blue-700 font-medium'
                        : 'pb-2 text-gray-700 hover:text-blue-600 font-medium';
                };
            @endphp

            @auth
                @if ($roleName === 'admin')
                    <a href="{{ route('admin.dashboard') }}"  class="{{ $navClass('dashboard')  }}">Dashboard</a>
                    <a href="{{ route('admin.management') }}" class="{{ $navClass('management') }}">Management</a>
                    <a href="{{ route('admin.attendance') }}" class="{{ $navClass('attendance') }}">Attendance Log</a>
                    <a href="{{ route('admin.reports') }}"   class="{{ $navClass('reports')    }}">Reports</a>

                @elseif ($roleName === 'faculty')
                    <a href="{{ route('faculty.attendance') }}" class="{{ $navClass('attendance') }}">Class attendances</a>
                    <a href="{{ route('faculty.schedule') }}"   class="{{ $navClass('schedule')   }}">Class Schedule</a>
                    <a href="{{ route('faculty.personal') }}"   class="{{ $navClass('personal')   }}">My attendance</a>

                @elseif ($roleName === 'student')
                    <a href="{{ route('student.attendance') }}" class="{{ $navClass('attendance') }}">My Attendance</a>
                    <a href="{{ route('student.schedule') }}"   class="{{ $navClass('schedule')   }}">My Class Schedule</a>

                @else
                    {{-- Fallback menu so you never see a blank state --}}
                    <a href="{{ route('profile.show') }}" class="{{ $navClass('profile') }}">Profile</a>
                @endif
            @endauth

            {{-- Right side: user dropdown (UNCHANGED POSITION) --}}
            <div class="relative">
                @if($user)
                    <button id="dropdownUserButton" data-dropdown-toggle="dropdownUser"
                            class="flex items-center space-x-2 focus:outline-none">
                        @if (!empty($user->profile_photo))
                            <img src="{{ asset('storage/'.$user->profile_photo) }}"
                                 class="w-8 h-8 rounded-full object-cover border" alt="Avatar">
                        @else
                            <img src="{{ asset('images/avatar.png') }}"
                                 class="w-8 h-8 rounded-full object-cover border" alt="Avatar">
                        @endif
                        <span class="hidden sm:block font-medium text-gray-700">
                            {{ $user->full_name }}
                        </span>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown -->
                    <div id="dropdownUser"
                        class="hidden z-50 my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow">
                        <div class="px-4 py-3">
                            <span class="block text-sm text-gray-900">{{ $user->email }}</span>
                            <span class="block text-sm text-gray-500 truncate capitalize">
                                {{ $roleName ?? 'user' }}
                            </span>
                        </div>
                        <ul class="py-2">
                            <li>
                                <a href="{{ route('profile.show') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Manage Account
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    {{-- Page wrapper (UNCHANGED POSITION) --}}
    <div class="flex pt-20">
        @yield('content')
    </div>

    {{-- Flowbite JS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
</body>
</html>
