<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="bg-gray-50 dark:bg-gray-900">
<section class="flex flex-col items-center justify-center min-h-screen px-6 py-8">

  <div class="w-full bg-white rounded-lg shadow sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border dark:border-gray-700">
    <div class="p-6 space-y-4 sm:p-8">
      <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
        Reset Password
      </h1>

      @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg p-3 text-sm">
          <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
          <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">New Password</label>
          <input
            id="password"
            type="password"
            name="password"
            required
            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
        </div>

        <div>
          <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Confirm Password</label>
          <input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            required
            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
        </div>

        <button type="submit"
          class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
          Reset Password
        </button>
      </form>
    </div>
  </div>

</section>
</body>
</html>
