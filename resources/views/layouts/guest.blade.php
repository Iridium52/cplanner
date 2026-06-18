<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? '' }} — C Planner</title>
        <link rel="icon" type="image/png" href="/images/logo.png">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md px-4">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <img src="/images/logo.png" alt="C Planner" class="w-12 h-12 rounded-2xl object-cover mb-4 mx-auto">
                <h1 class="text-2xl font-bold text-white">C Planner</h1>
                <p class="text-gray-500 text-sm mt-1">Project management for your projects</p>
            </div>

            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
