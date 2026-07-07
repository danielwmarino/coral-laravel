<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Coral') }} — {{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-20 lg:hidden"
        ></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 w-60 bg-[#003470] flex flex-col transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto lg:z-auto"
        >
            {{-- Logo --}}
            <div class="flex items-center h-16 px-6 border-b border-white/10 flex-shrink-0">
                <span class="text-white text-xl font-bold tracking-wide">Coral</span>
            </div>

            {{-- Client Switcher --}}
            @livewire('client-switcher')

            {{-- Navigation --}}
            <nav class="flex-1 py-4 overflow-y-auto">
                <ul class="space-y-0.5 px-3">

                    <x-nav-item route="dashboard" icon="layout-dashboard" label="Dashboard" />
                    <x-nav-item route="strategy" icon="file-text" label="Strategy" />
                    <x-nav-item route="goals.index" icon="target" label="Goals" />

                    @if(auth()->user()->isAgency())
                        <x-nav-item route="agent" icon="bot" label="Agent" />
                    @endif

                    <x-nav-item route="recommendations" icon="lightbulb" label="Recommendations" />
                    <x-nav-item route="insights" icon="trending-up" label="Insights" />
                    <x-nav-item route="dataset" icon="database" label="Data Set" />

                    @if(auth()->user()->isSuperAdmin())
                        <x-nav-item route="admin.index" icon="shield" label="Admin" />
                    @endif

                </ul>
            </nav>

            {{-- User footer --}}
            <div class="border-t border-white/10 p-4 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#FC54AA] flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->profile?->full_name ?? auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">
                            {{ auth()->user()->profile?->full_name ?? auth()->user()->name }}
                        </p>
                        <p class="text-white/50 text-xs truncate capitalize">
                            {{ str_replace('_', ' ', auth()->user()->profile?->role ?? '') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Log out" class="text-white/40 hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Mobile header bar --}}
            <header class="lg:hidden bg-white border-b border-gray-200 h-14 flex items-center px-4 flex-shrink-0">
                <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <span class="ml-3 font-semibold text-[#003470]">Coral</span>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto">
                <div class="max-w-[1200px] mx-auto px-6 py-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
