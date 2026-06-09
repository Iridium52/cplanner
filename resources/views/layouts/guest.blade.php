<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? '' }} — C Planner</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md px-4">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-600 mb-4">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">C Planner</h1>
                <p class="text-gray-500 text-sm mt-1">Project management for your projects</p>
            </div>

            {{ $slot }}
        </div>

        @livewireScripts
    </body>
</html>
