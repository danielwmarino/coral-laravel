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
            <button wire:click="generate" wire:loading.attr="disabled"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors disabled:opacity-60 whitespace-nowrap">
                <span class="relative inline-flex w-4 h-4 shrink-0">
                    <svg wire:loading.remove wire:target="generate" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute inset-0"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    <svg wire:loading wire:target="generate" class="animate-spin absolute inset-0" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </span>
                <span wire:loading.remove wire:target="generate">{{ $insights->isEmpty() ? 'Generate' : 'Refresh' }}</span>
                <span wire:loading wire:target="generate">Generating…</span>
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
            $priorityOrder = ['high', 'medium', 'low'];
            $priorityGroups = ['high' => [], 'medium' => [], 'low' => []];
            foreach ($insights as $insight) {
                $p = $insight->priority ?? 'medium';
                if (isset($priorityGroups[$p])) $priorityGroups[$p][] = $insight;
            }
            $priorityConfig = [
                'high'   => ['label' => 'High',   'arrow' => '↑', 'color' => 'text-red-600'],
                'medium' => ['label' => 'Medium',  'arrow' => '→', 'color' => 'text-yellow-600'],
                'low'    => ['label' => 'Low',     'arrow' => '↓', 'color' => 'text-gray-400'],
            ];
            $categoryColors = [
                'SEO' => 'bg-green-50 text-green-700', 'Paid' => 'bg-blue-50 text-blue-700',
                'Content' => 'bg-purple-50 text-purple-700', 'Social' => 'bg-pink-50 text-pink-700',
                'Email' => 'bg-yellow-50 text-yellow-700', 'Analytics' => 'bg-[#FCE4F1] text-[#a61040]',
                'Industry' => 'bg-gray-100 text-gray-700',
            ];
        @endphp

        <div class="space-y-3">
            @foreach($priorityOrder as $priority)
                @foreach($priorityGroups[$priority] as $insight)
                    @php
                        $pc = $priorityConfig[$priority];
                        $body = $insight->content['body'] ?? '';
                        $why = $insight->content['why'] ?? '';
                        $sources = $insight->content['sources'] ?? [];
                        $effort = $insight->content['effort'] ?? null;
                        $impact = $insight->content['impact'] ?? null;
                    @endphp
                    <div class="bg-white border border-gray-100 rounded-xl p-6">
                        <div class="flex items-start gap-5">
                            {{-- Priority column --}}
                            <div class="flex flex-col items-center gap-0.5 shrink-0 w-12 pt-0.5">
                                <span class="text-base font-bold {{ $pc['color'] }}">{{ $pc['arrow'] }}</span>
                                <span class="text-xs font-semibold {{ $pc['color'] }}">{{ $pc['label'] }}</span>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0 space-y-2.5">
                                {{-- Title row --}}
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-2 flex-wrap flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900 leading-snug">{{ $insight->title }}</h3>
                                        @if($insight->category)
                                            <span class="text-xs px-2 py-0.5 rounded-md {{ $categoryColors[$insight->category] ?? 'bg-gray-100 text-gray-600' }} shrink-0 font-medium">{{ $insight->category }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0 text-xs">
                                        <span class="text-gray-400">{{ $insight->created_at->format('n/j/Y') }}</span>
                                        @if($insight->saved)
                                            <button wire:click="unsaveInsight('{{ $insight->id }}')" class="flex items-center gap-1 text-[#FC54AA] hover:text-gray-400 transition-colors font-medium">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                                Save
                                            </button>
                                        @else
                                            <button wire:click="saveInsight('{{ $insight->id }}')" class="flex items-center gap-1 text-gray-400 hover:text-[#FC54AA] transition-colors font-medium">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                                Save
                                            </button>
                                        @endif
                                        <button wire:click="dismissInsight('{{ $insight->id }}')" class="flex items-center gap-1 text-gray-400 hover:text-gray-600 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            Dismiss
                                        </button>
                                    </div>
                                </div>

                                @if($body)
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $body }}</p>
                                @endif

                                @if($why)
                                    <p class="text-xs text-gray-500 leading-relaxed"><span class="font-semibold text-gray-600">Why: </span>{{ $why }}</p>
                                @endif

                                @if(!empty($sources))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($sources as $source)
                                            @if(!empty($source['url']) && !empty($source['label']))
                                                <a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1 text-xs text-[#003470] hover:text-[#FC54AA] transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                                    {{ $source['label'] }}
                                                </a>
                                                @if(!$loop->last)
                                                    <span class="text-gray-200">·</span>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                @if($effort || $impact)
                                    <div class="flex gap-4 text-xs text-gray-400 pt-2 border-t border-gray-50">
                                        @if($effort)<span>Effort: <span class="capitalize font-medium text-gray-600">{{ $effort }}</span></span>@endif
                                        @if($impact)<span>Impact: <span class="capitalize font-medium text-gray-600">{{ $impact }}</span></span>@endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif
</div>
