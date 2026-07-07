<div>

    {{-- Flash message --}}
    @if (session()->has('message'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (!$client)
        {{-- No client assigned / selected --}}
        <div class="flex flex-col items-center justify-center min-h-[60vh] text-center">
            <div class="w-16 h-16 rounded-full bg-[#FCE4F1] flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FC54AA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-[#003470] mb-1">No client selected</h2>
            <p class="text-sm text-gray-400">Select a client from the sidebar to view their dashboard.</p>
        </div>
    @else

        {{-- Page header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-[#003470]">Dashboard</h1>
            <p class="text-sm text-gray-400 mt-0.5">{{ $client->name }}</p>
        </div>

        {{-- ── STAT CARDS ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Goals</p>
                <p class="text-3xl font-bold mt-2 text-[#003470]">{{ $stats['total'] }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">In Progress</p>
                <p class="text-3xl font-bold mt-2 text-blue-500">{{ $stats['in_progress'] }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Completed</p>
                <p class="text-3xl font-bold mt-2 text-emerald-500">{{ $stats['completed'] }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">At Risk</p>
                <p class="text-3xl font-bold mt-2 text-rose-500">{{ $stats['at_risk'] }}</p>
            </div>

        </div>

        {{-- ── EXECUTIVE SUMMARY + STRATEGIST MESSAGE ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

            {{-- Executive Summary --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-[#003470] uppercase tracking-wide">Executive Summary</h2>
                    @if($isAgency)
                        <button
                            wire:click="regenerateSummary"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 text-xs font-medium text-[#FC54AA] hover:text-[#E0429A] transition-colors disabled:opacity-50"
                        >
                            <svg wire:loading wire:target="regenerateSummary" class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            <svg wire:loading.remove wire:target="regenerateSummary" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.08-4.49"/></svg>
                            Regenerate
                        </button>
                    @endif
                </div>
                @if($client->executive_summary)
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $client->executive_summary }}</p>
                    @if($client->executive_summary_updated_at)
                        <p class="text-xs text-gray-300 mt-3">Updated {{ $client->executive_summary_updated_at->diffForHumans() }}</p>
                    @endif
                @else
                    <p class="text-sm text-gray-300 italic">
                        {{ $isAgency ? 'No summary yet. Click Regenerate to generate one with AI.' : 'No summary available yet.' }}
                    </p>
                @endif
            </div>

            {{-- Strategist Message --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-[#003470] uppercase tracking-wide">Message from your Strategist</h2>
                </div>
                @if($isAgency)
                    @livewire('strategist-message-editor', ['client' => $client], key('msg-'.$client->id))
                @else
                    @if($client->strategist_message)
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $client->strategist_message }}</p>
                    @else
                        <p class="text-sm text-gray-300 italic">No message from your strategist yet.</p>
                    @endif
                @endif
            </div>

        </div>

        {{-- ── GOAL CARDS GRID ── --}}
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-[#003470] uppercase tracking-wide">Active Goals</h2>
            @if($stats['total'] > 0)
                <a href="{{ route('goals.index') }}" class="text-xs text-[#FC54AA] hover:text-[#E0429A] font-medium transition-colors">
                    View all →
                </a>
            @endif
        </div>

        @if($goals->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                </div>
                <p class="text-sm text-gray-400">No active goals yet.</p>
                @if($isAgency)
                    <a href="{{ route('goals.index') }}" class="inline-block mt-3 text-xs font-medium text-[#FC54AA] hover:text-[#E0429A]">Add a goal →</a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($goals as $goal)
                    <x-goal-card :goal="$goal" :isAgency="$isAgency" />
                @endforeach
            </div>
        @endif

    @endif
</div>
