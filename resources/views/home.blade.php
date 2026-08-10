@extends('layouts.app')

@section('content')

<div class="flex min-h-screen items-center justify-center">

    <div class="rounded-2xl bg-white p-8 shadow-lg">

        <h1 class="text-3xl font-bold text-gray-800">
            e-SKM
        </h1>

        <p class="mt-2 text-gray-600">
            Sistem Survei Kepuasan Masyarakat
        </p>

        <button
            x-data
            @click="alert('Alpine.js berhasil!')"
            class="mt-6 rounded-lg bg-blue-600 px-5 py-2 text-white hover:bg-blue-700"
        >
            Test Alpine.js
        </button>

    </div>

</div>

@endsection