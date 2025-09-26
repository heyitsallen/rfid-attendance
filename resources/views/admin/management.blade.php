@extends('layouts.app')

@section('title', 'Management')

@section('content')
<div class="p-6 w-full min-h-screen bg-cover bg-center" 
     style="background-image: url('{{ asset('images/background.jpg') }}');">

    <!-- Overlay (optional for darkening the image) -->
    <div class="bg-black/40 p-6 rounded-lg">
        <h1 class="text-2xl font-bold mb-4 text-white">Management</h1>
        <p class="text-white">Here you can manage students, faculty, sections, and devices.</p>
    </div>
</div>
@endsection