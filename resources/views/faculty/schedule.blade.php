@extends('layouts.app')

@section('title', 'My Schedule')

@section('content')
<div class="p-6 w-full min-h-screen bg-cover bg-center" 
     style="background-image: url('{{ asset('images/background.jpg') }}');">

    <!-- Overlay (optional for darkening the image) -->
    <div class="bg-black/40 p-6 rounded-lg">
        <h1 class="text-2xl font-bold mb-4 text-white">My Schedule</h1>
        <p class="text-white">Your teaching schedule.</p>
    </div>
</div>
@endsection