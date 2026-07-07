<div>
    @if($isAgency && $clients->count() > 0)
        <div x-data="{ open: false }" class="relative">
            <button
                @click="open = !open"
                class="w-full flex items-center justify-between gap-2 px-3 py-2 bg-white/10 hover:bg-white/15 rounded-lg transition-colors text-left"
            >
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-6 h-6 rounded bg-[#FC54AA] flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr($selectedClientName, 0, 1)) }}
                    </div>
                    <span class="text-white text-sm font-medium truncate">{{ $selectedClientName }}</span>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white/60 flex-shrink-0" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <div
                x-show="open"
                @click.outside="open = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-lg border border-gray-100 z-50 py-1 max-h-64 overflow-y-auto"
            >
                @foreach($clients as $client)
                    <button
                        wire:click="switchClient('{{ $client->id }}')"
                        @click="open = false"
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-gray-50 transition-colors text-left
                               {{ $client->id === $selectedClientId ? 'text-[#003470] font-semibold' : 'text-gray-700' }}"
                    >
                        <div class="w-5 h-5 rounded bg-[#003470] flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($client->name, 0, 1)) }}
                        </div>
                        <span class="truncate">{{ $client->name }}</span>
                        @if($client->id === $selectedClientId)
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="ml-auto text-[#FC54AA] flex-shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @elseif(!$isAgency && $selectedClientName !== 'Select Client')
        {{-- Client user: just show their client name, no dropdown --}}
        <div class="flex items-center gap-2 px-3 py-2">
            <div class="w-6 h-6 rounded bg-[#FC54AA] flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">
                {{ strtoupper(substr($selectedClientName, 0, 1)) }}
            </div>
            <span class="text-white text-sm font-medium truncate">{{ $selectedClientName }}</span>
        </div>
    @endif
</div>
