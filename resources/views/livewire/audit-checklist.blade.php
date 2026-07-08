<div class="space-y-6">

    @if(session('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg">
            {{ session('toast') }}
        </div>
    @endif

    @if(!$this->audit)
        <div class="border border-dashed rounded-xl p-12 text-center">
            <p class="text-sm text-gray-500">Audit not found.</p>
            <a href="{{ route('audits.index') }}" class="text-xs text-[#FC54AA] hover:underline mt-2 inline-block">Back to Audits</a>
        </div>
    @else

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('audits.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
                <h1 class="text-xl font-semibold text-[#003470] truncate">{{ $this->audit->product_name }}</h1>
                @if($this->audit->status === 'completed')
                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium flex-shrink-0">Completed</span>
                @endif
            </div>
            @if($this->audit->product_url)
                <p class="text-xs text-gray-400 ml-6">{{ $this->audit->product_url }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            @if($this->audit->status === 'completed')
                <a href="{{ route('audits.report', $this->audit->id) }}"
                    class="flex items-center gap-1.5 px-3 py-2 text-sm bg-[#003470] text-white rounded-lg hover:bg-[#002555] transition-colors font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    View Report
                </a>
            @endif

            @if($this->audit->product_url)
                <button wire:click="runAiAudit" :disabled="$wire.aiRunning"
                    class="flex items-center gap-1.5 px-3 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors font-medium disabled:opacity-60">
                    <span wire:loading.remove wire:target="runAiAudit" class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
                        Run AI Audit
                    </span>
                    <span wire:loading wire:target="runAiAudit" class="flex items-center gap-1.5 leading-none">
                        <svg class="animate-spin shrink-0" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        Analysing…
                    </span>
                </button>
            @endif

            <button wire:click="completeAudit"
                wire:confirm="Mark this audit as complete? You can still view the report and edit responses later."
                class="flex items-center gap-1.5 px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors font-medium text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Complete
            </button>
        </div>
    </div>

    @if($this->aiError)
        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
            {{ $this->aiError }}
        </div>
    @endif

    {{-- AI Mode: show status screen only, no checklist --}}
    @if($this->audit->audit_mode === 'ai_assisted')

        @if($this->aiRunning)
            {{-- Running state --}}
            <div class="border border-pink-100 rounded-xl bg-white p-10 text-center">
                <div class="w-16 h-16 bg-pink-50 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="animate-spin text-[#FC54AA]" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900 mb-2">AI Audit in Progress</h2>
                <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Crawling the site and scoring all 98 criteria across UX and content. This takes about 30–60 seconds.</p>
                <div class="w-full max-w-xs mx-auto bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full bg-[#FC54AA] animate-pulse" style="width: 70%"></div>
                </div>
            </div>

        @elseif($this->audit->status === 'completed')
            {{-- Completed state --}}
            <div class="border border-green-100 rounded-xl bg-white p-10 text-center">
                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900 mb-2">AI Audit Complete</h2>
                <p class="text-sm text-gray-500 mb-6">All criteria have been scored. View the full report below.</p>
                <div class="flex items-center justify-center gap-3">
                    <a href="{{ route('audits.report', $this->audit->id) }}"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm bg-[#003470] text-white rounded-lg hover:bg-[#002555] transition-colors font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        View Report
                    </a>
                    @if($this->audit->product_url)
                        <button wire:click="runAiAudit"
                            class="flex items-center gap-2 px-4 py-2.5 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                            Re-run AI Audit
                        </button>
                    @endif
                </div>
                @if($this->audit->overall_score !== null)
                    <p class="text-xs text-gray-400 mt-5">Overall score: <strong class="text-gray-700">{{ $this->audit->overall_score }}%</strong> · UX: <strong class="text-gray-700">{{ $this->audit->ux_score }}%</strong> · Content: <strong class="text-gray-700">{{ $this->audit->content_score }}%</strong></p>
                @endif
            </div>

        @else
            {{-- Ready to run / running state (combined so no flash) --}}
            <div class="border border-gray-100 rounded-xl bg-white p-10 text-center"
                 x-data
                 x-init="$wire.runAiAudit()">
                <div class="w-16 h-16 bg-pink-50 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="animate-spin text-[#FC54AA]" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900 mb-2">Analysing…</h2>
                <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Crawling <strong>{{ $this->audit->product_url }}</strong> and scoring all 98 UX and content criteria. This takes about 30–60 seconds.</p>
                <div class="w-full max-w-xs mx-auto bg-gray-100 rounded-full h-2 overflow-hidden"
                     x-data="{ pct: 2 }"
                     x-init="
                         let tick = setInterval(() => {
                             if (pct < 88) pct = Math.min(pct + (88 - pct) * 0.03 + 0.3, 88);
                             else clearInterval(tick);
                         }, 500);
                     ">
                    <div class="h-2 rounded-full bg-[#FC54AA] transition-all duration-500"
                         :style="'width: ' + pct + '%'"></div>
                </div>
            </div>
        @endif

    @else
    {{-- Manual mode: show full checklist --}}

    {{-- Progress bar --}}
    @php
        $pct = $progress['total'] > 0 ? round(($progress['scored'] / $progress['total']) * 100) : 0;
    @endphp
    <div class="border border-gray-100 rounded-xl p-4 bg-white">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-gray-600">Progress</span>
            <span class="text-xs text-gray-500">{{ $progress['scored'] }} of {{ $progress['total'] }} items scored</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full transition-all duration-300
                {{ $pct >= 80 ? 'bg-green-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-[#FC54AA]') }}"
                style="width: {{ $pct }}%"></div>
        </div>
        <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
            @if($this->audit->ux_score !== null)
                <span>UX: <span class="font-medium text-gray-700">{{ $this->audit->ux_score }}%</span></span>
            @endif
            @if($this->audit->content_score !== null)
                <span>Content: <span class="font-medium text-gray-700">{{ $this->audit->content_score }}%</span></span>
            @endif
            @if($this->audit->overall_score !== null)
                <span>Overall: <span class="font-semibold
                    {{ $this->audit->overall_score >= 80 ? 'text-green-600' : ($this->audit->overall_score >= 60 ? 'text-amber-600' : 'text-red-600') }}">
                    {{ $this->audit->overall_score }}%
                </span></span>
            @endif
        </div>
    </div>

    {{-- Section Tabs --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit">
        <button wire:click="setActiveSection('ux')"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                {{ $activeSection === 'ux' ? 'bg-white text-[#003470] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            UX Audit
            @if($this->audit->ux_score !== null)
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full {{ $this->audit->ux_score >= 80 ? 'bg-green-100 text-green-700' : ($this->audit->ux_score >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $this->audit->ux_score }}%</span>
            @endif
        </button>
        <button wire:click="setActiveSection('content')"
            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                {{ $activeSection === 'content' ? 'bg-white text-[#003470] shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Content Audit
            @if($this->audit->content_score !== null)
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full {{ $this->audit->content_score >= 80 ? 'bg-green-100 text-green-700' : ($this->audit->content_score >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $this->audit->content_score }}%</span>
            @endif
        </button>
    </div>

    {{-- Checklist Items --}}
    @php
        $sectionItems = $activeSection === 'ux' ? $uxItems : $contentItems;
    @endphp

    <div class="space-y-6">
        @foreach($sectionItems as $categoryName => $items)
            @php
                // Calculate category score
                $catYes = 0; $catTotal = 0;
                foreach ($items as $item) {
                    $r = $responses[$activeSection . '.' . $item['key']] ?? null;
                    if (in_array($r, ['yes', 'no', 'fail'])) {
                        $catTotal++;
                        if ($r === 'yes') $catYes++;
                    }
                }
                $catScore = $catTotal > 0 ? round(($catYes / $catTotal) * 100) : null;
            @endphp
            <div class="border border-gray-100 rounded-xl bg-white overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800">{{ $categoryName }}</h3>
                    @if($catScore !== null)
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $catScore >= 80 ? 'bg-green-100 text-green-700' : ($catScore >= 60 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                            {{ $catScore }}%
                        </span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 font-medium">Not scored</span>
                    @endif
                </div>

                <div class="divide-y divide-gray-50">
                    @foreach($items as $item)
                        @php
                            $responseKey = $activeSection . '.' . $item['key'];
                            $current = $responses[$responseKey] ?? null;
                        @endphp
                        <div class="flex items-center justify-between gap-4 px-5 py-3 hover:bg-gray-50/50 transition-colors">
                            <p class="text-sm text-gray-700 flex-1">{{ $item['text'] }}</p>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                {{-- Yes --}}
                                <button wire:click="setResponse('{{ $activeSection }}', '{{ $item['key'] }}', 'yes', '{{ addslashes($categoryName) }}')"
                                    class="px-2.5 py-1 text-xs font-medium rounded-full border transition-colors
                                        {{ $current === 'yes'
                                            ? 'bg-green-500 border-green-500 text-white'
                                            : 'border-green-300 text-green-700 hover:bg-green-50' }}">
                                    Yes
                                </button>
                                {{-- No --}}
                                <button wire:click="setResponse('{{ $activeSection }}', '{{ $item['key'] }}', 'no', '{{ addslashes($categoryName) }}')"
                                    class="px-2.5 py-1 text-xs font-medium rounded-full border transition-colors
                                        {{ $current === 'no'
                                            ? 'bg-orange-500 border-orange-500 text-white'
                                            : 'border-orange-300 text-orange-700 hover:bg-orange-50' }}">
                                    No
                                </button>
                                {{-- N/A --}}
                                <button wire:click="setResponse('{{ $activeSection }}', '{{ $item['key'] }}', 'na', '{{ addslashes($categoryName) }}')"
                                    class="px-2.5 py-1 text-xs font-medium rounded-full border transition-colors
                                        {{ $current === 'na'
                                            ? 'bg-gray-400 border-gray-400 text-white'
                                            : 'border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                                    N/A
                                </button>
                                {{-- Fail --}}
                                <button wire:click="setResponse('{{ $activeSection }}', '{{ $item['key'] }}', 'fail', '{{ addslashes($categoryName) }}')"
                                    class="px-2.5 py-1 text-xs font-medium rounded-full border transition-colors
                                        {{ $current === 'fail'
                                            ? 'bg-red-500 border-red-500 text-white'
                                            : 'border-red-300 text-red-700 hover:bg-red-50' }}">
                                    Fail
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Sticky bottom bar --}}
    <div class="sticky bottom-0 -mx-6 px-6 py-3 bg-white/90 backdrop-blur border-t border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span>{{ $progress['scored'] }}/{{ $progress['total'] }} scored</span>
            <div class="h-1 w-24 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-1 bg-[#FC54AA] rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <span>{{ $pct }}%</span>
        </div>
        <button wire:click="completeAudit"
            wire:confirm="Mark this audit as complete?"
            class="px-4 py-2 text-sm bg-[#FC54AA] hover:bg-[#E0429A] text-white rounded-lg transition-colors font-medium">
            Complete &amp; Generate Report
        </button>
    </div>

    @endif {{-- end manual mode --}}
    @endif {{-- end audit exists --}}
</div>
