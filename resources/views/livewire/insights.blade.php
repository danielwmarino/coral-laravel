<div class="space-y-8">

    @if(session('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg">
            {{ session('toast') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-[#003470]">Insights</h1>
            <p class="text-sm text-gray-500 mt-1">External market intelligence and trends</p>
        </div>
        @if($isAgency)
            <button wire:click="generate" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-3 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors disabled:opacity-60">
                <span wire:loading.remove wire:target="generate" class="flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    {{ $insights->isEmpty() ? 'Generate Insights' : 'Regenerate' }}
                </span>
                <span wire:loading wire:target="generate" class="flex items-center gap-1.5">
                    <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    Generating…
                </span>
            </button>
        @endif
    </div>

    @if($insights->isEmpty())
        <div class="border border-dashed rounded-xl p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            <p class="text-sm text-gray-500">No insights yet</p>
            <p class="text-xs text-gray-400 mt-1">Generate AI market insights for this client</p>
        </div>
    @else
        @php
            $priorityConfig = ['high' => 'bg-red-50 text-red-700', 'medium' => 'bg-yellow-50 text-yellow-700', 'low' => 'bg-gray-100 text-gray-600'];
            $categoryColors = [
                'SEO' => 'bg-green-50 text-green-700', 'Paid' => 'bg-blue-50 text-blue-700',
                'Content' => 'bg-purple-50 text-purple-700', 'Social' => 'bg-pink-50 text-pink-700',
                'Email' => 'bg-yellow-50 text-yellow-700', 'Analytics' => 'bg-[#FCE4F1] text-[#a61040]',
                'Industry' => 'bg-gray-100 text-gray-700',
            ];
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($insights as $insight)
                <div class="bg-white border border-gray-100 rounded-xl p-5 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            @if($insight->priority)
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $priorityConfig[$insight->priority] ?? 'bg-gray-100 text-gray-600' }} capitalize">{{ $insight->priority }}</span>
                            @endif
                            @if($insight->category)
                                <span class="text-xs px-1.5 py-0.5 rounded {{ $categoryColors[$insight->category] ?? 'bg-gray-100 text-gray-600' }}">{{ $insight->category }}</span>
                            @endif
                        </div>
                        <div class="flex gap-1 shrink-0">
                            @if(!$insight->saved)
                                <button wire:click="saveInsight('{{ $insight->id }}')" class="p-1 text-gray-400 hover:text-[#FC54AA] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                </button>
                            @else
                                <span class="p-1 text-[#FC54AA]"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></span>
                            @endif
                            <button wire:click="dismissInsight('{{ $insight->id }}')" class="p-1 text-gray-300 hover:text-gray-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ $insight->title }}</p>
                    @if($insight->content['body'] ?? null)
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $insight->content['body'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
