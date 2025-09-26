@if ($errors->any())
  <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
    <ul class="list-disc ml-5">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if (session('success'))
  <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800">
    {{ session('success') }}
  </div>
@endif
