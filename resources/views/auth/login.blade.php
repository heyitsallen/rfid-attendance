<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="bg-gray-50 dark:bg-gray-900">

  <section class="flex flex-col items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8 py-8">

    {{-- Logo + title --}}
    <div class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white">
      <img class="w-10 h-10 mr-2" src="{{ asset('images/logo.png') }}" alt="SPCC Logo">
      <span class="truncate">SPCC Attendance Tracker</span>
    </div>

    {{-- Login box --}}
    <div class="w-full max-w-md bg-white rounded-lg shadow dark:border dark:bg-gray-800 dark:border-gray-700">
      <div class="p-6 space-y-4 sm:p-8">
        <h1 class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white">
          Sign in to your account
        </h1>

        {{-- Validation & auth errors --}}
        @if ($errors->any())
          <div class="rounded-lg bg-red-50 dark:bg-red-900/30 p-3 text-sm text-red-700 dark:text-red-300">
            <ul class="list-disc ml-5 space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="space-y-4 md:space-y-6" method="POST" action="{{ route('login.post') }}">
          @csrf

          {{-- Email --}}
          <div>
            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Email</label>
            <input
              type="email"
              name="email"
              id="email"
              value="{{ old('email') }}"
              class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              placeholder="you@example.com"
              required
              autocomplete="email"
            >
          </div>

{{-- Password --}}
<div>
  <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">
    Password
  </label>
  <div class="relative">
    <input
      type="password"
      name="password"
      id="password"
      class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
      placeholder="••••••••"
      required
      autocomplete="current-password"
    >
    <button
      type="button"
      id="togglePassword"
      class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
    >
      <!-- Eye Icon (visible by default) -->
      <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none"
           viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
           class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 
                 12 4.5c4.64 0 8.577 3.01 9.964 7.183.07.207.07.431 
                 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178z" />
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>

      <!-- Eye Slash Icon (hidden by default) -->
      <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" fill="none"
           viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
           class="w-5 h-5 hidden">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M3.98 8.223A10.477 10.477 0 001.934 
                 12C3.226 16.338 7.244 19.5 12 
                 19.5c1.841 0 3.564-.431 
                 5.086-1.2M6.228 6.228A10.45 
                 10.45 0 0112 4.5c4.756 0 
                 8.773 3.162 10.065 
                 7.5a10.522 10.522 0 
                 01-4.293 5.774M6.228 
                 6.228L3 3m3.228 3.228L21 
                 21" />
      </svg>
    </button>
  </div>
</div>

          {{-- Remember + forgot --}}
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember"
                name="remember"
                type="checkbox"
                class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-primary-600 dark:ring-offset-gray-800"
              >
              <label for="remember" class="ml-2 text-sm text-gray-500 dark:text-gray-300">Remember me</label>
            </div>

            <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400">
              Forgot password?
            </a>
          </div>

          {{-- Submit --}}
          <button
            type="submit"
            class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
          >
            Sign in
          </button>
        </form>
      </div>
    </div>


<script>
  const passwordInput = document.getElementById("password");
  const togglePassword = document.getElementById("togglePassword");
  const eyeIcon = document.getElementById("eyeIcon");
  const eyeSlashIcon = document.getElementById("eyeSlashIcon");

  togglePassword.addEventListener("click", () => {
    const isPassword = passwordInput.getAttribute("type") === "password";
    passwordInput.setAttribute("type", isPassword ? "text" : "password");

    // Toggle icons
    eyeIcon.classList.toggle("hidden", !isPassword);
    eyeSlashIcon.classList.toggle("hidden", isPassword);
  });
</script>
  </section>
</body>
</html>
