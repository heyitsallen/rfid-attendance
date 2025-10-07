<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forgot Password</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="p-6 w-full min-h-screen bg-cover bg-center" 
     style="background-image: url('{{ asset('images/background.jpg') }}');">
<section class="flex flex-col items-center justify-center min-h-screen px-6 py-8">

  <div class="w-full bg-white rounded-lg shadow sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border dark:border-gray-700">
    <div class="p-6 space-y-4 sm:p-8">
      <h1 class="text-xl font-bold text-gray-900 md:text-2xl dark:text-white">
        Forgot your password?
      </h1>
      <p class="text-sm text-gray-600 dark:text-gray-400">
        No problem. Enter your email and we’ll send you a password reset link.
      </p>

      @if (session('status'))
        <div class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg p-3 text-sm">
          {{ session('status') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg p-3 text-sm">
          <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
          <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-200">Email</label>
          <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
        </div>

        <button type="submit"
          class="w-full text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
          Send Reset Link
        </button>
      </form>

      <p class="text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('login') }}" class="text-primary-600 hover:underline dark:text-primary-400">Back to login</a>
      </p>
    </div>
  </div>

</section>
</body>
</html>
