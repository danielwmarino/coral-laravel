<x-app-layout title="New Strategy">
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('strategy') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-[#003470]">New Strategy</h1>
                <p class="text-sm text-gray-500 mt-0.5">Answer a few questions and we'll generate a full marketing strategy</p>
            </div>
        </div>
    </div>
    @livewire('strategy-wizard')
</x-app-layout>
