<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name') }} — C Planner</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen">
        <div class="flex h-screen overflow-hidden">
            {{-- Sidebar --}}
            <aside class="w-60 flex-shrink-0 bg-gray-900 border-r border-gray-800 flex flex-col">
                {{-- Logo --}}
                <div class="flex items-center gap-2 px-4 h-14 border-b border-gray-800">
                    <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <span class="font-semibold text-white text-sm tracking-tight">C Planner</span>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 px-2 py-3 space-y-0.5 overflow-y-auto">
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('flagged-tasks') }}" wire:navigate
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('flagged-tasks') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H12.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                        Flagged Tasks
                    </a>

                    @if(auth()->user()->isAdmin())
                    <div class="pt-3 pb-1 px-3">
                        <span class="text-xs font-medium text-gray-600 uppercase tracking-wider">Admin</span>
                    </div>
                    <a href="{{ route('admin.users') }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.users') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Users
                    </a>
                    <a href="{{ route('admin.project-types') }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors {{ request()->routeIs('admin.project-types') ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-gray-100 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Project Types
                    </a>
                    @endif
                </nav>

                {{-- User section --}}
                <div class="border-t border-gray-800 p-3">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="w-full flex items-center gap-2.5 px-2 py-2 rounded-lg hover:bg-gray-800 transition-colors text-left">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold text-white flex-shrink-0"
                                 style="background-color: {{ auth()->user()->avatar_color }}">
                                {{ auth()->user()->initials() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-100 truncate">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ ucfirst(auth()->user()->role) }}</div>
                            </div>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             class="absolute bottom-full left-0 w-full mb-1 bg-gray-800 border border-gray-700 rounded-lg shadow-xl py-1 z-50">
                            <a href="{{ route('profile') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile & 2FA
                            </a>
                            <div class="border-t border-gray-700 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-300 hover:text-red-400 hover:bg-gray-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Main content --}}
            <div class="flex-1 flex flex-col overflow-hidden">
                {{-- Top bar --}}
                @if(isset($header))
                <header class="flex-shrink-0 h-14 bg-gray-900 border-b border-gray-800 flex items-center px-6 gap-4">
                    {{ $header }}
                </header>
                @endif

                {{-- Content --}}
                <main class="flex-1 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
